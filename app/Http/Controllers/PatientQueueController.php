<?php

namespace App\Http\Controllers;

use App\Events\{PatientQueueUpdated, PatientQueueDisplay, CallOutQueue};
use App\Models\Patient; // Make sure Patient model exists and is correctly namespaced
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Import Request
use Illuminate\Support\Facades\Log; // For logging

class PatientQueueController extends Controller
{
    /**
     * Get the list of patients in the queue (waiting or null status).
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Fetch patients relevant to the queue (e.g., 'waiting' or 'in-progress')
        // Adjust the query based on your actual status values and logic

        $user = auth()->user();

        $date = \Carbon\Carbon::now()->toDateString();
        $query = Patient::whereIn('status', ['waiting', 'in-progress'])
                        ->whereDate('created_at', $date)
                        ->orderBy('priority_number'); // Example ordering

        // Get all departments the user has access to
        if (!empty($request->department_id)) {
            $query->where('next_department_id', $request->department_id);
        }

        $patients = $query->get();

        return response()->json($patients);
    }

    // Call out session
    public function callOutQueue(Patient $patient) {
        try {
            $user = auth()->user();
            $departmentIds = $user->getAllDepartmentIds();

            if (!in_array($patient->next_department_id, $departmentIds)) {
                return response()->json(['message' => 'You do not have permission to call out this patient.'], 403);
            }

            event(new CallOutQueue([
                'priority' => $patient->priority,
                'number' => $patient->priority_number,
                'department_name' => $patient->next_department->name
            ]));

            return response()->json(['message' => 'Queued called successfully.', 'patient' => $patient]);
        } catch (\Exception $e) {
            Log::error("Error calling out patient ID: {$patient->id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to call out.'], 500);
        }
    }

    /**
     * Start a session for a patient.
     *
     * @param Patient $patient The patient model instance (route model binding)
     * @return JsonResponse
     */
    public function startSession(Patient $patient): JsonResponse
    {
        try {
            // check if has permission to start session
            $user = auth()->user();
            $departmentIds = $user->getAllDepartmentIds();

            if (!in_array($patient->next_department_id, $departmentIds)) {
                return response()->json(['message' => 'You do not have permission to start this session.'], 403);
            }

            $patient->status = 'in-progress';
            $patient->session_started = \Carbon\Carbon::now()->toDateTimeString();
            $patient->save();

            Log::info("Session started for patient ID: {$patient->id}");
            // TODO: Broadcast event for real-time updates (e.g., using Laravel Echo)
            // event(new PatientStatusUpdated($patient));
            event(new PatientQueueDisplay("Reload PatientQueueDisplay"));

            event(new CallOutQueue([
                'priority' => $patient->priority,
                'number' => $patient->priority_number,
                'department_name' => $patient->next_department->name
            ]));

            return response()->json(['message' => 'Session started successfully.', 'patient' => $patient]);
        } catch (\Exception $e) {
            Log::error("Error starting session for patient ID: {$patient->id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to start session.'], 500);
        }
    }

    /**
     * End a session for a patient.
     *
     * @param Patient $patient The patient model instance
     * @return JsonResponse
     */
    public function endSession(Patient $patient): JsonResponse
    {
         try {
            // Check if patient was actually in progress
            if ($patient->status !== 'in-progress') {
                 return response()->json(['message' => 'Patient session was not in progress.'], 409);
            }

            // Mark as completed or remove, depending on workflow
            $patient->status = 'completed'; // Or perhaps delete/archive
            $patient->session_ended = \Carbon\Carbon::now()->toDateTimeString(); // Or perhaps delete/archive

            $current_next_department_id = $patient->next_department_id;
            $current_next_department_started = $patient->next_department_started;

            $prev_departments = $patient->prev_department_ids ?? []; // Initialize as empty array if null
            if ($current_next_department_id !== null) {
                $prev_departments[] = [
                    'department_id' => $current_next_department_id,
                    'timestamp' => $current_next_department_started, // Store as string
                ];
            }
            $patient->prev_department_ids = $prev_departments;

            $patient->next_department_id = null;
            $patient->next_department_started = null;

            $patient->save();

            Log::info("Session ended for patient ID: {$patient->id}");
            // TODO: Broadcast event
            // event(new PatientStatusUpdated($patient));
            event(new PatientQueueDisplay("Reload PatientQueueDisplay"));

            return response()->json(['message' => 'Session ended successfully.']);
        } catch (\Exception $e) {
            Log::error("Error ending session for patient ID: {$patient->id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to end session.'], 500);
        }
    }

    /**
     * Move a patient to the next step (e.g., transfer to another department/status).
     *
     * @param Patient $patient The patient model instance
     * @param Request $request
     * @return JsonResponse
     */
    public function nextStep(Patient $patient, Request $request): JsonResponse
    {
        // Validate the request if needed (e.g., ensure next_step_id is provided)
        // $validated = $request->validate([
        //     'next_step_id' => 'required|integer|exists:departments,id', // Example validation
        // ]);

        try {
            if ($patient->status !== 'in-progress') {
                return response()->json(['message' => 'Patient session was not in progress.'], 409);
            }

            // Logic to handle the next step:
            // Option 1: Mark as completed in this queue
            $patient->status = 'waiting'; // Or 'completed'
            // Option 2: Update status and potentially assign to a new department/queue
            // $patient->department_id = $validated['next_step_id'];
            // $patient->status = 'waiting'; // Waiting in the next queue

            // Get the current next_department_id before updating
            $current_next_department_id = $patient->next_department_id;
            $current_next_department_started = $patient->next_department_started;

            // Add the current next_department_id to the prev_department_ids array
            // Ensure prev_department_ids is treated as an array (handled by model casting)
            $prev_departments = $patient->prev_department_ids ?? []; // Initialize as empty array if null
            if ($current_next_department_id !== null) {
                $prev_departments[] = [
                    'department_id' => $current_next_department_id,
                    'timestamp' => $current_next_department_started, // Store as string
                ];
            }
            $patient->prev_department_ids = $prev_departments;

            // Update the next_department_id with the new value from the request
            $patient->next_department_id = $request->input('next_step_id');
            $patient->next_department_started = \Carbon\Carbon::now()->toDateTimeString();

            $patient->save();

            $nextStepId = $request->input('next_step_id', 'Unknown'); // Get next step if provided
            Log::info("Patient ID: {$patient->id} moved to next step (ID: {$nextStepId})");

            // Broadcast event to the specific department
            event(new PatientQueueUpdated($nextStepId));
            event(new PatientQueueDisplay("Reload PatientQueueDisplay"));

            return response()->json(['message' => 'Patient moved to next step successfully.']);
        } catch (\Exception $e) {
            Log::error("Error moving patient ID: {$patient->id} to next step: " . $e->getMessage());
            return response()->json(['message' => 'Failed to move patient to next step.'], 500);
        }
    }
}