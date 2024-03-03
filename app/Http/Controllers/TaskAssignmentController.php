<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminAsignTasks;
use App\Models\AdminAssignTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AdminAsignTasks as AdminAssignTasksResource;

class TaskAssignmentController extends Controller
{
    public function assignTask($taskId)
    {

        $user = Auth::user();

        if ($user === null) {
            return response()->json([
                'error' => 'Not Logged In',
                'link' => '/login'
            ]);
        }

        $verifyTask = AdminAssignTask::findOrFail($taskId);

        if ($user->id !== $verifyTask->user_id) {
            return response()->json([
                'error' => 'Not Authorized',
                'link' => '/'
            ]);
        }
        if ($verifyTask->deadline <= now()) {
            return response()->json([
                'error' => 'Task has already passed deadline',
                'link' => '/'
            ]);
        } else if ($verifyTask->status !== 'Pending') {
            return response()->json([
                'error' => $verifyTask->status === 'Accepted' ? 'Task Status Is Already Accepted' : 'Task Status Is Already Cancelled',
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

        if ($user === null) {
            return response()->json([
                'error' => 'Not Logged In',
                'link' => '/login'
            ]);
        }

        $status = $request->input('status');
        $task = AdminAssignTask::findOrFail($taskId);

        // if($user->id)

        if ($task->deadline <= now()) {
            return response()->json([
                'error' => 'Task has already passed deadline',
                'link' => '/'
            ]);
        } else if ($task->status !== 'Pending') {
            return response()->json([
                'error' => $task->status === 'accept' ? 'Task Status Is Already Accepted' : 'Task Status Is Already Cancelled',
                'link' => '/'
            ]);
        }

        if ($status == 'Decline') {
            $task->status = $status;
            $task->save();
            return response()->json(['message' => 'Task Assigned By Admin Is Declined successfully'], 200);
        } else {
            $attachTask = Task::create([
                'title' => $task->title,
                'description' => $task->description,
                'deadline' => $task->deadline,
                'is_completed' => 0,
                'progress' => 0,
                'priority' => $task->priority,
                'user_id' => $user->id,
                'admin_id' => $task->admin_id,
            ]);


            $task->status = $status;
            $task->task_id = $attachTask->id;
            $task->save();

            return response()->json(['message' => 'Congratulations!! . Task added successfully'], 200);
        }
    }

    public function allAssignTasks()
    {
        try {
            $authUser = Auth::user();
            if (!$authUser || !$authUser->is_admin) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $assignTasks = AdminAssignTask::with(['user', 'task'])
                ->where('admin_id', $authUser->id)
                ->orderBy('updated_at', 'desc')->get();
            return AdminAssignTasksResource::collection($assignTasks);
        } catch (\Exception $e) {
            return response()->json(['error' => 'something went wrong'], 500);
        }
    }
}
