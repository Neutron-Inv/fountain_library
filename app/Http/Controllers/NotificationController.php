<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Subject_Routing;
use App\Models\Activity;
use App\Models\User;
use App\Models\Student;
use App\Models\Admin;
use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();
        $userId = $user->id;
        $role = $user->role;
        
        // Initialize school_id
        $school_id = null;

        // Retrieve school_id based on user role
        if ($role == 'student') {
            $school_id = Student::where('user_id', $userId)->pluck('school_id')->first();
        } elseif ($role == 'teacher') {
            $school_id = Teacher::where('user_id', $userId)->pluck('school_id')->first();
        } elseif ($role == 'admin') {
            $school_id = Admin::where('user_id', $userId)->pluck('school_id')->first();
        }

        // Ensure school_id is found
        if (is_null($school_id)) {
            return response()->json(['error' => 'School ID not found for the user.'], 404);
        }
        
        $currentDateTime = Carbon::now();
        
        // Fetch notifications based on school_id and group criteria
        $notifications = Notification::where('school_id', $school_id)
                                     ->where(function ($query) use ($role) {
                                         $query->where('group', 'all')
                                               ->orWhere('group', $role);
                                     })
                                     ->orWhere('user_id', $userId)
                                     ->get();

        // Structure the JSON response
        $response = $notifications->map(function ($notification) {
            return [
                'notification_id' => $notification->id,
                'title' => $notification->title,
                'from' => $notification->from,
                'till' => $notification->till,
                'message' => $notification->message,
                'created_at' => $notification->created_at
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200);
    }
    
    
    public function store(Request $request)
    {
        // Validate the request input
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'group' => 'required|string|max:255',
            'from' => 'required|date',
            'till' => 'required|date',
            'message' => 'required|string'
        ]);

        // Get the authenticated user
        $user = $request->user();
        $userId = $user->id;
        $role = $user->role;

        // Initialize school_id
        $school_id = null;

        // Retrieve school_id based on user role
        if ($role == 'student') {
            $school_id = Student::where('user_id', $userId)->pluck('school_id')->first();
        } elseif ($role == 'teacher') {
            $school_id = Teacher::where('user_id', $userId)->pluck('school_id')->first();
        } elseif ($role == 'admin') {
            $school_id = Admin::where('user_id', $userId)->pluck('school_id')->first();
        }

        // Ensure school_id is found
        if (is_null($school_id)) {
            return response()->json(['error' => 'School ID not found for the user.'], 404);
        }

        // Create the notification
        $notification = Notification::create([
            'school_id' => $school_id,
            'user_id' => $userId,
            'title' => $request->input('title'),
            'group' => $request->input('group'),
            'from' => $request->input('from'),
            'till' => $request->input('till'),
            'message' => $request->input('message')
        ]);

        // Return a response
        return response()->json([
            'success' => true,
            'data' => $notification
        ], 201);
    }
}
