<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Admin;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            //'number' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string',
            'role' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'role' => $validatedData['role'],
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
        ]);
        
        $user = $user;
        
        if($validatedData['role'] == 'student'){
            
            $student = Student::create([
                'school_id' => $request->input('school_id'),
                'user_id' => $user->id,
                'grade_id' => $request->input('grade_id'),
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'dob' => $request->input('dob'),
                'email' => $request->input('email'),
                'gender' => $request->input('gender')
                ]);
            
            $user = $student;
        }elseif($validatedData['role'] == 'teacher'){
            
            $teacher = Teacher::create([
                'school_id' => $request->input('school_id'),
                'user_id' => $user->id,
                'grade_id' => $request->input('grade_id'),
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $request->input('email'),
                'number' => $request->input('phone'),
                'dob' => $request->input('dob'),
                'gender' => $request->input('gender')
                ]);
            
            $user = $teacher;
        }elseif($validatedData['role'] == 'admin'){
            
            $admin = Admin::create([
                'school_id' => $request->input('school_id'),
                'user_id' => $user->id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'gender' => $request->input('gender')
                ]);
            
            $user = $admin;
        }

        return response()->json(['message' => 'Registration successful', 'user' => $student], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user_details = User::where('email', $credentials['email'])->first();
            
            if($user_details->role =="teacher"){
                $user_details['teacher_id'] = Teacher::select('id')->where('user_id', $user_details->id)->value('id');
            }
            $token = Auth::user()->createToken('authToken')->plainTextToken;
            return response()->json(['token' => $token, 'user' => $user_details], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to your email'], 200)
            : response()->json(['error' => 'Unable to send reset link'], 400);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully'], 200)
            : response()->json(['error' => 'Unable to reset password'], 400);
    }

    public function verify(Request $request)
    {
        // Logic to verify email
    }

    public function activate(Request $request)
    {
        // Logic to activate account
    }
    
    public function verifyToken(Request $request)
    {
        if ($request->user()) {
            return response()->json(['message' => 'Token is valid'], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    
    public function studentindex($id)
    {  
        $students = Student::leftjoin('grades', 'students.grade_id', '=', 'grades.id')
        ->leftjoin('users', 'students.user_id','=','users.id')
        ->select(
            'students.id as student_id',
            'users.id as user_id',
            'students.first_name as first_name',
            'students.last_name as last_name',
            'students.dob as dob',
            'students.gender as gender',
            'grades.grade_name as grade',
            \DB::raw('"student" as role'),
            'users.email as email',
            'users.image as image'
            )
        ->where('students.school_id', $id)->get();
        
        return response()->json($students);
    }
    
    public function teacherindex($id)
    {  
         $teachers = Teacher::leftjoin('users', 'teachers.user_id','=','users.id')
         ->select(
            'teachers.id as teacher_id',
            'users.id as user_id',
            'teachers.first_name as first_name',
            'teachers.last_name as last_name',
            'teachers.dob as dob',
            'teachers.gender as gender',
            \DB::raw('"teacher" as role'),
            'users.email as email',
            'users.image as image'
        )
        ->where('school_id', $id)->get();
        
        return response()->json($teachers);
    }
    
    public function adminindex($id)
    {  
        $admin = Admin::leftjoin('users', 'admins.user_id','=','users.id')
         ->select(
            'admins.id as admin_id',
            'users.id as user_id',
            'admins.first_name as first_name',
            'admins.last_name as last_name',
            'admins.phone as phone',
            'admins.gender as gender',
            \DB::raw('"admin" as role'),
            'users.email as email',
            'users.image as image'
        )
        ->where('school_id', $id)->get();
        
        return response()->json($admin);
    }
    
    
    
    public function refreshToken(Request $request)
    {
        // Check if the token is about to expire
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

}
