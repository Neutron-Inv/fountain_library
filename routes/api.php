<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\{GradeController, SubjectController, SubjectRoutingController, SubjectTopicController, ArmController, TopicController, DashboardController,CommentController, NotificationController, TeacherController, StudentController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/api',function(){
    return "Test api Tester";
});

Route::prefix('test')->group(function () {
    Route::get('/test', [SubjectController::class, 'test']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('user')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/sendotp', [UserController::class, 'sendOtp']);
    Route::post('/verifyotp', [UserController::class, 'verifyOtp']);
    Route::post('/login', [UserController::class, 'login'])->name('login');
    Route::get('/refreshToken', [UserController::class, 'refreshToken'])->name('refresh');
});

Route::middleware('auth:sanctum')->get('/user/verify-token', [UserController::class, 'verifyToken']);

Route::get('/csrf-token', function() {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::prefix('school')->group(function () {
    Route::post('/store', [SubjectController::class, 'store']);
    Route::get('/discover', [SubjectController::class, 'index']);
    Route::get('/show/{studio}', [SubjectController::class, 'show']);
    Route::put('/update/{studio}', [SubjectController::class, 'update']);
    Route::delete('/delete/{studio}', [SubjectController::class, 'delete']);
});

Route::middleware('auth:sanctum')->group(function () {

Route::prefix('subject')->group(function () {
    Route::post('/store', [SubjectController::class, 'store']);
    Route::get('/create/{id}', [SubjectController::class, 'create']);
    Route::get('/fetch', [SubjectController::class, 'index']);
    Route::get('/show/{id}', [SubjectController::class, 'fetch']);
    Route::delete('/delete/{id}', [SubjectController::class, 'destroy']);
    Route::post('/update/{subjectId}', [SubjectController::class, 'update']);
    Route::get('/student/fetch', [SubjectController::class, 'studentsubject']);
    Route::get('/teacher/fetch', [SubjectController::class, 'teachersubject']);
    Route::post('/topic/create', [SubjectTopicController::class, 'store']);
    Route::get('/topic/fetch', [SubjectTopicController::class, 'index']);
}); 

Route::prefix('topic')->group(function () {
    Route::post('/store', [TopicController::class, 'store']);
    Route::get('/create/{id}', [TopicController::class, 'create']);
    Route::get('/fetch/{id}', [TopicController::class, 'index']);
    Route::get('/student/fetch', [TopicController::class, 'sindex']);
    Route::get('/show/{id}', [TopicController::class, 'show']);
    Route::delete('/delete/{id}', [TopicController::class, 'destroy']);
    Route::post('/update/{id}', [TopicController::class, 'update']);
}); 

Route::prefix('grade')->group(function () {
    Route::post('/store', [GradeController::class, 'store']);
    Route::get('/fetch', [GradeController::class, 'index']);
});

Route::prefix('dashboard')->group(function () {
    Route::get('/admin/{id}', [DashboardController::class, 'index']);
    Route::get('/student/{id}', [DashboardController::class, 'student']);
});

Route::prefix('arm')->group(function () {
    Route::post('/store', [ArmController::class, 'store']);
    Route::get('/fetch', [ArmController::class, 'index']);
});

Route::prefix('comments')->group(function () {
    Route::post('/store', [CommentController::class, 'store']);
    Route::get('/fetch/{id}', [CommentController::class, 'fetch']);
});

Route::prefix('notifications')->group(function () {
    Route::post('/store', [NotificationController::class, 'store']);
    Route::get('/fetch', [NotificationController::class, 'index']);
});

Route::prefix('teachers')->group(function () {
    Route::post('/create', [TeacherController::class, 'store']);
    Route::get('/profile/{id}', [TeacherController::class, 'edit']);
    Route::post('/update/{id}', [TeacherController::class, 'update']);
    Route::delete('/delete/{id}', [TeacherController::class, 'destroy']);
    Route::get('/fetch', [NotificationController::class, 'index']);
});

Route::prefix('students')->group(function () {
    Route::post('/create', [StudentController::class, 'store']);
    Route::get('/profile/{id}', [StudentController::class, 'edit']);
    Route::post('/update/{id}', [StudentController::class, 'update']);
    Route::delete('/delete/{id}', [StudentController::class, 'destroy']);
    Route::get('/fetch', [NotificationController::class, 'index']);
});


Route::prefix('users')->group(function () {
    Route::get('/students/{id}', [UserController::class, 'studentindex']);
    Route::get('/teachers/{id}', [UserController::class, 'teacherindex']);
    Route::get('/admins/{id}', [UserController::class, 'adminindex']);
});
});