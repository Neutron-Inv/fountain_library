<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\User;
use App\Models\Subject;
use App\Models\Activity;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Admin;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class TopicController extends Controller
{
    // Fetch all topics
    public function index($id)
    {
        $topics = Topic::where('subject_id', $id)->get()
        ->map(function ($item) {
            return collect($item->getAttributes())
                ->mapWithKeys(function ($value, $key) {
                    return [$key => (string) $value];
                });
        });
    
    return response()->json($topics);
    }
    
    public function sindex(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_id' => 'required|exists:grades,id',
            // 'term_id' => 'nullable|string',
        ]);
        
        $topics = Topic::select('id', 'week', 'title', 'introduction', 'file', 'video')->where('subject_id', $request->input('subject_id'))->where('grade_id', $request->input('grade_id'))->get();
        return response()->json($topics);
    }


    public function store(Request $request)
    {
        \Log::info('Store method called');

        $messages = [
            'video.max' => 'The video must not be greater than 2000 MB.',
            'file.max' => 'The file must not be greater than 100 MB.',
        ];

        \Log::info('Validation started');
        //\Log::info(['Request' => $request]);
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_id' => 'nullable|exists:grades,id',
            'grade_ids' => 'nullable|array',
            'term_id' => 'nullable|string',
            'week' => 'nullable|integer',
            'title' => 'nullable|string|max:255',
            'introduction' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,mkv|max:2024000', // Max 100MB
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:102400', // Max 10MB
        ], $messages);

        \Log::info('Validation passed', ['validated' => $validated]);

        if ($request->hasFile('video')) {
            \Log::info('Video file found');
            $videoFile = $request->file('video');
            $videoName = 'video_' . Carbon::now()->timestamp . '.' . $videoFile->getClientOriginalExtension();
            $path = $videoFile->storeAs('videos', $videoName, 'public');
            $validated['video'] = Storage::disk('public')->url($path);
            \Log::info('Video stored', ['path' => $path]);
        }

        if ($request->hasFile('file')) {
            \Log::info('Other file found');
            $file = $request->file('file');
            $fileName = 'file_' . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('files', $fileName, 'public');
            $validated['file'] = Storage::disk('public')->url($path);
            \Log::info('File stored', ['path' => $path]);
        }
        
        $validated['grade_id'] = $validated['grade_ids'][0];
        $topic = Topic::create($validated);

        \Log::info('Topic created', ['topic' => $topic]);

        $subject = Subject::where('id', $validated['subject_id'])->first();
        $user = $request->user(); // Get the authenticated user
        $userId = $user->id;
        $role = $user->role;
        $school_id = 0;

        \Log::info('User details fetched', ['user' => $user]);

        if($role == 'student'){
            $school_id = Student::where('user_id', $userId)->pluck('school_id')->first();
        } elseif($role == 'admin'){
            $school_id = Admin::where('user_id', $userId)->pluck('school_id')->first();
        } elseif($role == 'teacher'){
            $school_id = Teacher::where('user_id', $userId)->pluck('school_id')->first();
        }

        $user_name = User::where('id', $userId)->first();
        $activity_log = 'Created a Topic "'.$validated['title'].'" under '.$subject->subject_name;
        $activity = Activity::create([
            'user_id' => $userId,
            'school_id' => $school_id,
            'name' => 'Topic Creation',
            'description' => $activity_log,
            'type' => 'Topic',
        ]);

        \Log::info('Activity logged', ['activity' => $activity]);

        return response()->json($topic, 201);
    }

    // Show a specific topic
   public function show($id)
    {
        $topic = Topic::find($id);
        
        if (!$topic) {
            return response()->json(['message' => 'No topic found'], 404);
        }
        
        return response()->json($topic);
    }
    
    // Update a specific topic
    public function update(Request $request, $id)
    {
        $topic = Topic::find($id);
        
        if (!$topic) {
            return response()->json(['message' => 'No topic found'], 404);
        }
        
        
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'week' => 'required|integer',
            'title' => 'required|string|max:255',
            'introduction' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:204800', // Max 20MB
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:102400', // Max 10MB
        ]);

        if ($request->hasFile('video')) {
            if ($topic->video) {
                Storage::disk('public')->delete($topic->video);
            }
            $videoFile = $request->file('video');
            $videoName = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME) . '_' . Carbon::now()->timestamp . '.' . $videoFile->getClientOriginalExtension();
            $path = $videoFile->storeAs('videos', $videoName, 'public');
            $validated['video'] = Storage::disk('public')->url($path);
        }else{
            $validated['video'] = null;
        }

        if ($request->hasFile('file')) {
            if ($topic->file) {
                Storage::disk('public')->delete($topic->file);
            }
            $file = $request->file('file');
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('files', $fileName, 'public');
            $validated['file'] = Storage::disk('public')->url($path);
        }else{
            $validated['file'] = null;
        }

        $topic->update($validated);

        return response()->json($topic);
    }

    // Delete a specific topic
    public function destroybefore($id)
    {
        $topic = Topic::findOrFail($id);

        if ($topic->video) {
            Storage::disk('public')->delete($topic->video);
        }

        if ($topic->file) {
            Storage::disk('public')->delete($topic->file);
        }

        $topic->delete();

        return response()->json(['message' => 'Topic deleted successfully']);
    }
    
    public function destroy($id, Request $request)
    {
        try {
            \Log::info('Destroy method called', ['topic_id' => $id]);
    
            // Find the topic by ID or fail if not found
            $topic = Topic::findOrFail($id);
            
            // Store the title and subject name for logging after deletion
            $title = $topic->title;
            $subjectName = $topic->subject->subject_name ?? 'Unknown Subject';
    
            // Delete video and file if they exist
            if ($topic->video) {
                \Log::info('Deleting video file', ['path' => $topic->video]);
                Storage::disk('public')->delete(str_replace('/storage/', '', $topic->video));
            }
            if ($topic->file) {
                \Log::info('Deleting additional file', ['path' => $topic->file]);
                Storage::disk('public')->delete(str_replace('/storage/', '', $topic->file));
            }
    
            // Delete the topic
            $topic->delete();
    
            \Log::info('Topic deleted successfully', ['topic_id' => $id]);
    
            // Log the activity
            $user = $request->user();
            $userId = $user->id;
            $role = $user->role;
            $school_id = 0;
    
            // Determine school ID based on user role
            if($role == 'student'){
                $school_id = Student::where('user_id', $userId)->pluck('school_id')->first();
            } elseif($role == 'admin'){
                $school_id = Admin::where('user_id', $userId)->pluck('school_id')->first();
            } elseif($role == 'teacher'){
                $school_id = Teacher::where('user_id', $userId)->pluck('school_id')->first();
            }
    
            $activity_log = 'Deleted the Topic "'.$title.'" under '.$subjectName;
            $activity = Activity::create([
                'user_id' => $userId,
                'school_id' => $school_id,
                'name' => 'Topic Deletion',
                'description' => $activity_log,
                'type' => 'Topic',
            ]);
    
            \Log::info('Activity logged', ['activity' => $activity]);
    
            return response()->json(['message' => 'Topic deleted successfully'], 200);
    
        } catch (ModelNotFoundException $e) {
            \Log::error('Topic not found', ['topic_id' => $id]);
            return response()->json(['error' => 'Topic not found'], 404);
        } catch (Exception $e) {
            \Log::error('Error deleting topic', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while deleting the topic'], 500);
        }
    }

}