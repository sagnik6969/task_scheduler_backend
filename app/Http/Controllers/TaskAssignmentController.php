<?php

namespace App\Http\Controllers;

use App\Models\AdminAssignTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskAssignmentController extends Controller
{
    public function assignTask($taskId)
    {
        
        $user = Auth::user();

        $verifyTask = AdminAssignTask::findOrFail($taskId);
        
        if($user->id !== $verifyTask->user_id){
            return response()->json([
                'error' => 'Not Authorized',
                'link' => '/'
            ]);
        }
        if($verifyTask->deadline <= now()){
            return response()->json([
                'error' => 'Task has already passed deadline',
                'link' => '/'
            ]);
        }

        else if($verifyTask->status !== 'Pending'){
            return response()->json([
                'error' => $verifyTask->status==='Accepted'? 'Task Status Is Already Accepted' : 'Task Status Is Already Cancelled',
                'link' => '/'
            ]);
        }
        return response()->json($verifyTask);
        // if ($user->tasks()->where('id', $taskId)->exists()) {
        //     return response()->json([
        //         'message' => 'Task is already accepted.',
        //         'link' => '/'
        //     ]);
        // }


    }

    public function assignTaskUpdates(Request $request, $taskId)
    {
        $user = Auth::user();

        $status = $request->input('status');

        $task = AdminAssignTask::findOrFail($taskId);

        // if($user->id)

        if($task->deadline <= now()){
            return response()->json([ 
                'error' => 'Task has already passed deadline',
                'link' => '/'
            ]);
        }

        else if($task->status !== 'Pending'){
            return response()->json([
                'error' => $task->status==='Accepted'? 'Task Status Is Already Accepted' : 'Task Status Is Already Cancelled',
                'link' => '/'
            ]);
        }

        $task->status = $status;
        $task->save();

        $attachTask = Task::create([
            'title' => $task->title,
            'description' => $task->description,
            'deadline' => $task->deadline,
            'is_completed' => 0,
            'progress' => 0,
            'priority' => $task->priority,
            'user_id' => $user->id,
            // 'admin_id' => $task->admin_id,
        ]);

        $user->tasks()->attach($attachTask);

        return response()->json(['message' => 'Congratulations!! . Task added successfully'], 200);
    }
}
