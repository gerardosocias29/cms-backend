<?php

namespace App\Http\Controllers;

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
    public function index(): JsonResponse
    {
        // Fetch patients relevant to the queue (e.g., 'waiting' or 'in-progress')
        // Adjust the query based on your actual status values and logic
        $patients = Patient::whereIn('status', ['waiting', 'in-progress'])
                           ->orWhereNull('status') // Include null if that means waiting
                           ->orderBy('priority_number') // Example ordering
                           ->get();

        return response()->json($patients);
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
            // Check if patient is already being served or completed
            if ($patient->status !== 'waiting' && !is_null($patient->status)) {
                 return response()->json(['message' => 'Patient is not waiting.'], 409); // Conflict
            }

            $patient->status = 'in-progress';
            $patient->save();

            Log::info("Session started for patient ID: {$patient->id}");
            // TODO: Broadcast event for real-time updates (e.g., using Laravel Echo)
            // event(new PatientStatusUpdated($patient));

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
            $patient->save();

            Log::info("Session ended for patient ID: {$patient->id}");
            // TODO: Broadcast event
            // event(new PatientStatusUpdated($patient));

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
            $patient->status = 'transferred'; // Or 'completed'
            // Option 2: Update status and potentially assign to a new department/queue
            // $patient->department_id = $validated['next_step_id'];
            // $patient->status = 'waiting'; // Waiting in the next queue

            $patient->save();

            $nextStepId = $request->input('next_step_id', 'Unknown'); // Get next step if provided
            Log::info("Patient ID: {$patient->id} moved to next step (ID: {$nextStepId})");
            // TODO: Broadcast event
            // event(new PatientStatusUpdated($patient));

            return response()->json(['message' => 'Patient moved to next step successfully.']);
        } catch (\Exception $e) {
            Log::error("Error moving patient ID: {$patient->id} to next step: " . $e->getMessage());
            return response()->json(['message' => 'Failed to move patient to next step.'], 500);
        }
    }
}