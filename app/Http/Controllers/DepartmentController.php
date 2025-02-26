<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Department, DepartmentSpecialization};

class DepartmentController extends Controller
{
    public function get() {
        $departments = Department::with(['specializations'])->get();
        return response()->json($departments);
    }
}
