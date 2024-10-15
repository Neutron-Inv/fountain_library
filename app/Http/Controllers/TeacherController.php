<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Topic;
use App\Models\User;
use App\Models\Subject;
use App\Models\Activity;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class TeacherController extends Controller
{
    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|string|max:10',
            'password' => 'required|string|max:255'
        ]);

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

        $image = null;
            
            if ($request->hasFile('image')) {
            $imagefile = $request->file('image');
            $imageName = pathinfo($imagefile->getClientOriginalName(), PATHINFO_FILENAME) . '_' . Carbon::now()->timestamp . '.' . $imagefile->getClientOriginalExtension();
            $path = $imagefile->storeAs('users/image', $imageName, 'public');
            $image = Storage::disk('public')->url($path);
            }
        
        // Create a new user with role 'teacher'
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'username' => $validated['first_name'].' '.$validated['last_name'],
            'phone' => $request->phone,
            'role' => 'teacher',
            'image'=> $image,
            'password' => Hash::make($validated['password']), // Default password
            'email_verified_at' => now(),
            'verification_token' => Str::random(60),
            'status' => 'active',
            'remember_token' => Str::random(60),
        ]);
        
        // Create a corresponding teacher record
        $teacher = Teacher::create([
            'title' => $validated['title'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'school_id' => $school_id,
            'user_id' => $user->id,
            'dob' => $request->dob,
            'email' => $validated['email'],
            'number' => $request->phone,
            'gender' => $validated['gender'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teacher created successfully',
            'data' => [
                'user' => $user,
                'teacher' => $teacher,
            ],
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        // Validate request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|string|max:10',
        ]);
    
        // Find the user by ID
        $user = User::findOrFail($id);
        
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
    
        $image = $user->image;
    
        if ($request->hasFile('image')) {
            // Delete the old image
            if ($image) {
                Storage::disk('public')->delete(str_replace(Storage::disk('public')->url(''), '', $image));
            }
    
            $imagefile = $request->file('image');
            $imageName = pathinfo($imagefile->getClientOriginalName(), PATHINFO_FILENAME) . '_' . Carbon::now()->timestamp . '.' . $imagefile->getClientOriginalExtension();
            $path = $imagefile->storeAs('users/image', $imageName, 'public');
            $image = Storage::disk('public')->url($path);
        }
    
        // Update user
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'username' => $validated['first_name'].' '.$validated['last_name'],
            'phone' => $validated['phone'],
            'image' => $image,
        ]);
    
        // Find the corresponding teacher record
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
    
        // Update teacher
        $teacher->update([
            'title' => $validated['title'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'school_id' => $school_id,
            'dob' => $request->dob,
            'email' => $validated['email'],
            'number' => $validated['phone'],
            'gender' => $validated['gender'],
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Teacher updated successfully',
            'data' => [
                'user' => $user,
                'teacher' => $teacher,
            ],
        ], 200);
    }
    
    public function edit($id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);
    
        // Find the corresponding student record
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
    
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'teacher' => $teacher,
            ],
        ], 200);
    }
    
    public function destroy($id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);
    
        // Find the corresponding student record
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
    
        // Delete the image file if it exists
        if ($user->image) {
            Storage::disk('public')->delete(str_replace(Storage::disk('public')->url(''), '', $user->image));
        }
    
        // Delete the student record
        $teacher->delete();
    
        // Delete the user record
        $user->delete();
    
        return response()->json([
            'success' => true,
            'message' => 'Teacher and user deleted successfully',
        ], 200);
    }
}
