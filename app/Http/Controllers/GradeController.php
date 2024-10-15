<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Teacher;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $userId = $request->user();
        
        $role = $userId->role; // Adjust based on how role is stored/retrieved
        
        $school_id = 0;
        
        if($role == 'student'){
            $school_id = Student::where('user_id', $userId->id)->pluck('school_id')->first();
        } elseif($role == 'admin'){
            $school_id = Admin::where('user_id', $userId->id)->pluck('school_id')->first();
        } elseif($role == 'teacher'){
            $school_id = Teacher::where('user_id', $userId->id)->pluck('school_id')->first();
        }
        // Retrieve all grade_name values for the specified school_id
        $grades = Grade::where('school_id', $school_id)
        ->select('id','grade_name')
        ->get();

        // Respond with the list of grade_names
        return response()->json(['grades' => $grades]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'school_id' => 'required',
            'grade_name' => 'required',
            // Add any other validation rules as needed
        ]);

        // Create or update the record based on school_id
        Grade::create([
            'school_id' => $request->input('school_id'),
            'grade_name' => $request->input('grade_name'),
            // Add any other fields as needed
        ]);

        // Respond with a JSON message and HTTP status code
        return response()->json(['message' => 'Grade stored created successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function show(Grade $grade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function edit(Grade $grade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Grade $grade)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Grade  $grade
     * @return \Illuminate\Http\Response
     */
    public function destroy(Grade $grade)
    {
        //
    }
}
