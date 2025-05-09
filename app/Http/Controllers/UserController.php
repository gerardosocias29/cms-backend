<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function get(Request $request){
        $filter = json_decode($request->filter);
        $usersQuery = User::with(['department_specialization.department', 'department'])
            ->withTrashed()
            ->where('role_id', '!=', 1);

        $usersQuery = $this->applyFilters($usersQuery, $filter, User::class);
        $users = $usersQuery->paginate(($filter->rows), ['*'], 'page', ($filter->page + 1));

        // The accessors will automatically be included in the response

        return response($users);
    }

    public function saveUser(Request $request, $id = null)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . ($id ?? 'NULL') . ',id',
                'password' => $id ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
                'role_id' => 'required|integer|exists:roles,id|not_in:1',
                'department_id' => 'nullable|integer',
                'department_ids' => 'nullable|array',
                'department_ids.*' => 'integer|exists:departments,id',
                'specialization_id' => 'nullable|integer',
            ]);

            $user = $id ? User::find($id) : new User();

            if ($id && !$user) {
                return response()->json(['status' => false, 'message' => 'User not found'], 404);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->role_id = $request->role_id;
            $user->department_id = $request->department_id;
            $user->department_ids = $request->department_ids;
            $user->department_specialization_id = $request->specialization_id;

            // Update password only if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => $id ? 'User updated successfully' : 'User created successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['status' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cardTotals() {
        $admin = User::withTrashed()->where('role_id', 2)->count();
        $staff = User::withTrashed()->where('role_id', 3)->count();
        $active = User::where('role_id', '!=', 1)->count();
        $inactive = User::where('role_id', '!=', 1)->whereNotNull('deleted_at')->count();

        return response()->json([
            "admin" => $admin,
            "staff" => $staff,
            "active" => $active,
            "inactive" => $inactive,
        ]);
    }

    public function getStaff() {
        $staffs = User::where('role_id', 3)->get();
        return response()->json($staffs ?? []);
    }

    public function getUserById(Request $request, $id) {

        $user = User::with(['department_specialization.department', 'department', 'patients'])
            ->where('id', $id)
            ->first();

        // The accessors will automatically be included in the response

        return response($user);
    }
}
