<?php

namespace App\Http\Controllers;
use App\Models\Topic;
use App\Models\User;
use App\Models\Subject;
use App\Models\Activity;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Comment;
use App\Models\Subject_Routing;
use App\Models\Notification;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'comment' => 'required|string',
            'type' => 'required|string|max:255',
            'comment_id' => 'nullable|string'
        ]);
        
        $user = $request->user(); // Get the authenticated user
        $userId = $user->id;
        $userDetails = User::find($userId);
        $fullName = $userDetails->first_name . ' ' . $userDetails->last_name;
    
        // Assume $role is set somewhere in your code. You might retrieve it like:
        $role = $userDetails->role; // Adjust based on how role is stored/retrieved
        
        $school_id = 0;
        
        if($role == 'student'){
            $school_id = Student::where('user_id', $userId)->pluck('school_id')->first();
        } elseif($role == 'admin'){
            $school_id = Admin::where('user_id', $userId)->pluck('school_id')->first();
        } elseif($role == 'teacher'){
            $school_id = Teacher::where('user_id', $userId)->pluck('school_id')->first();
        }
        
        $subject_id = Topic::where('id', $request->input('topic_id'))
                        ->value('subject_id');
        $topic_title = Topic::where('id', $request->input('topic_id'))
                        ->value('title');
        $subject_title = Subject::where('id', $subject_id)
                        ->value('subject_name');
        $teacher_id = Subject_Routing::join('teachers','teachers.id','=','subject_routings.teacher_id')->where('subject_routings.subject_id', $subject_id)
                    ->value('teachers.user_id');
                        
        
        $comment = Comment::create([
            'school_id' => $school_id,
            'topic_id' => $request->input('topic_id'),
            'user_id' => $userId,
            'comment' => $request->input('comment'),
            'type' => $request->input('type'),
            'comment_id' => $request->input('comment_id')
        ]);
        
        $title = 'New Comment by '.$fullName.' on '.$topic_title.' under '.$subject_title;
        
        if($request->input('comment_id') && $request->input('type') == 'reply'){
            
        $main_id = Comment::where('id', $request->input('comment_id'))
                 ->where('type', 'main')
                 ->value('user_id');
        
            // Create the notification
            Notification::create([
                'school_id' => $school_id,
                'user_id' => $main_id,
                'title' => $request->input('comment'),
                'group' => 'Admin',
                'from' => date('Y-m-d'),
                'till' => date('Y-m-d'),
                'message' => $title
            ]);
            
            
        } 
        
        
        if ($comment) {
            $topic = $comment->topic; // Use the topic() relationship to get the related Topic model
                    
            
            Notification::create([
                'school_id' => $school_id,
                'user_id' => $teacher_id,
                'title' => $request->input('comment'),
                'group' => 'Direct',
                'from' => date('Y-m-d'),
                'till' => date('Y-m-d'),
                'message' => $title
            ]);
            
            $activity_log = 'New Comment under topic "' . $topic->name . '" by ' . $fullName;
            
            $activity = Activity::create([
                'user_id' => $userId,
                'school_id' => $school_id,
                'name' => 'New Comment',
                'description' => $activity_log,
                'type' => 'Comment',
            ]);
        }
        
        return response()->json($comment, 201);
    }
    
    public function fetch($topicId)
    {
        // Fetch comments under the given topic with their replies
        $comments = Comment::where('topic_id', $topicId)
                           ->whereNull('comment_id') // Only fetch top-level comments
                           ->with('replies')
                           ->orderBy('created_at', 'Desc')
                           ->get();

        // Structure the JSON response
        $response = $comments->map(function ($comment) {
            return [
                'comment_id' => $comment->id,
                'school_id' => $comment->school_id,
                'topic_id' => $comment->topic_id,
                'user_id' => $comment->user_id,
                'user_name' => $comment->user->first_name . ' ' . $comment->user->last_name,
                'user_role' => $comment->user->role,
                'comment' => $comment->comment,
                'type' => $comment->type,
                'created_at' =>$comment->created_at,
                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'comment_id' => $reply->id,
                        'school_id' => $reply->school_id,
                        'topic_id' => $reply->topic_id,
                        'user_id' => $reply->user_id,
                        'user_name' => $reply->user->first_name . ' ' . $reply->user->last_name,
                        'user_role' => $reply->user->role,
                        'comment' => $reply->comment,
                        'type' => $reply->type,
                        'created_at' => $reply->created_at,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $response
        ], 200);
    }


}
