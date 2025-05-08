<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Validation\ValidationException;
use App\Events\PatientQueueUpdated;

class PatientController extends Controller
{
    public function get(Request $request){
        $filter = json_decode($request->filter);
        $date = \Carbon\Carbon::now()->toDateString();
        $patientsQuery = Patient::with(['next_department', 'starting_department'])->whereDate('created_at', $date)->orderBy('created_at', 'desc');
        $patientsQuery = $this->applyFilters($patientsQuery, $filter, Patient::class);
        $patients = $patientsQuery->paginate(($filter->rows), ['*'], 'page', ($filter->page + 1));
        
        return response($patients);
    }

    public function savePatient(Request $request, $id = null) {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'birthday' => 'required|date',
                'priority' => 'required|string|max:50',
                'address' => 'required|string',
                'symptoms' => 'required|string',
                'bloodpressure' => 'required|string|max:20',
                'heartrate' => 'required|integer|min:1',
                'temperature' => 'required|numeric|min:30|max:45',
                'starting_department_id' => 'nullable|integer|exists:departments,id',
            ]);

            $validatedData['birthday'] = \Carbon\Carbon::parse($validatedData['birthday'])->toDateString();
            $date = \Carbon\Carbon::now()->toDateString();
            $priority = $validatedData['priority'];
            $lastPriority = Patient::whereDate('created_at', $date)
                ->where('priority', $priority)
                ->max('priority_number');

            $patient = $id ? Patient::findOrFail($id) : new Patient();
            $patient->fill($validatedData);

            // Assign priority number only when creating a new patient
            if (!$id) {
                $patient->priority_number = $lastPriority ? $lastPriority + 1 : 1;
                $patient->status = "waiting";
            }

            // Assign starting department based on assigned user's department specialization
            if (isset($validatedData['starting_department_id'])) {
                $patient->starting_department_id = $validatedData['starting_department_id'];
                $patient->next_department_id = $validatedData['starting_department_id'];
                $patient->next_department_started = \Carbon\Carbon::now()->toDateTimeString();

                event(new PatientQueueUpdated($validatedData['starting_department_id']));
            }

            $patient->save();

            return response()->json([
                'status' => true, 
                'message' => $id ? 'Patient updated successfully' : 'Patient created successfully',
                'priority_number' => $patient->priority_number,
                'priority_type' => $patient->priority
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cardTotals() {
        $date = \Carbon\Carbon::now()->toDateString();
        
        $urgent = Patient::where('priority', 'P')->whereDate('created_at', $date)->count();
        $waiting = Patient::where('status', 'waiting')->whereDate('created_at', $date)->count();
        $inprogress = Patient::where('status', 'in-progress')->whereDate('created_at', $date)->count();
        $completed = Patient::where('status', 'completed')->whereDate('created_at', $date)->count();

        return response()->json([
            "urgent" => $urgent,
            "waiting" => $waiting,
            "inprogress" => $inprogress,
            "completed" => $completed,
        ]);
    }

}
