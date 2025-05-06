<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\{Department, DepartmentSpecialization};

class DepartmentController extends Controller
{
    /**
     * Get all departments
     */
    public function get(Request $request) {
        $with = ['specializations'];
        if($request->has('has_patient')){
            $with[] = "patient";
        }

        $departments = Department::with($with)->get();

        return response()->json($departments);
    }

    /**
     * Create or update a department
     */
    public function store(Request $request, $id = null) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'staffCount' => 'nullable|integer|min:0',
            'totalBeds' => 'nullable|integer|min:0',
            'status' => 'nullable|in:available,busy,full',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $department = $id ? Department::findOrFail($id) : new Department();

            // Create data array from request
            $data = $request->all();

            // Ensure description has a default value if not provided
            if (!isset($data['description']) || $data['description'] === null) {
                $data['description'] = 'No description provided';
            }

            $department->fill($data);
            $department->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $id ? 'Department updated successfully' : 'Department created successfully',
                'data' => $department->load('specializations')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to ' . ($id ? 'update' : 'create') . ' department: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a department
     */
    public function destroy($id) {
        try {
            $department = Department::findOrFail($id);
            $department->delete();

            return response()->json([
                'status' => true,
                'message' => 'Department deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete department: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update specializations for a department
     */
    public function updateSpecializations(Request $request, $departmentId) {
        $validator = Validator::make($request->all(), [
            'specializations' => 'required|array',
            'specializations.*.id' => 'nullable|integer',
            'specializations.*.name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $department = Department::findOrFail($departmentId);

            // Get current specialization IDs
            $currentSpecIds = $department->specializations->pluck('id')->toArray();

            // Get IDs from request
            $requestSpecIds = collect($request->specializations)
                ->filter(function ($spec) {
                    return !empty($spec['id']);
                })
                ->pluck('id')
                ->toArray();

            // IDs to delete (in current but not in request)
            $idsToDelete = array_diff($currentSpecIds, $requestSpecIds);

            // Delete removed specializations
            if (!empty($idsToDelete)) {
                DepartmentSpecialization::whereIn('id', $idsToDelete)->delete();
            }

            // Update or create specializations
            foreach ($request->specializations as $spec) {
                if (!empty($spec['id'])) {
                    // Update existing
                    DepartmentSpecialization::where('id', $spec['id'])
                        ->update(['name' => $spec['name']]);
                } else {
                    // Create new
                    DepartmentSpecialization::create([
                        'department_id' => $departmentId,
                        'name' => $spec['name']
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Specializations updated successfully',
                'data' => $department->fresh()->load('specializations')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update specializations: ' . $e->getMessage()
            ], 500);
        }
    }
}
