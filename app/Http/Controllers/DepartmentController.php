<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Department, DepartmentSpecialization};

class DepartmentController extends Controller
{
    public function get(Request $request) {

        $with = ['specializations'];
        if($request->has('has_patient')){
            $with[] = "patient";
        }

        $departments = Department::with($with)->get();

        return response()->json($departments);
    }
}
