<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
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

            $patient = $id ? Patient::findOrFail($id) : new Patient();
            $patient->fill($validatedData);
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

}
