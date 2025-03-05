<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function get(Request $request){
        $filter = json_decode($request->filter);
        $patientsQuery = Patient::with(['assigned_to']);

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
                'assigned_user_id' => 'nullable|integer|exists:users,id',
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
            }

            $patient->save();

            return response()->json([
                'status' => true,
                'message' => $id ? 'Patient updated successfully' : 'Patient created successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cardTotals() {
        $urgent = Patient::where('priority', 'P')->count();
        $waiting = Patient::where('status', 'waiting')->orWhereNull('status')->count();
        $inprogress = Patient::where('status', 'in-progress')->count();
        $completed = Patient::where('status', 'competed')->count();

        return response()->json([
            "urgent" => $urgent,
            "waiting" => $waiting,
            "inprogress" => $inprogress,
            "completed" => $completed,
        ]);
    }

}
