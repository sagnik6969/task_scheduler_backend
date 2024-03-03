<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminAsignTasks;
use App\Models\AdminAssignTask;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssginmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AdminAsignTasks as AdminAssignTasksResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class TaskAssignmentController extends Controller
{

    public function assignTaskToUser(Request $request, User $user)
    {

        $task = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'sometimes',
            'deadline' => 'required|date',
            'is_completed' => 'sometimes',
            'progress' => 'sometimes',
            'priority' => 'required|in:' . implode(',', array_values(Task::$priorities))
        ]);

        if ($task->fails()) {
            return response()->json(['errors' => $task->errors()], 400);
        }

        $task = new AdminAssignTask();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->deadline = \Carbon\Carbon::parse($request->deadline);
        // $task->deadline = $request->deadline;
        $task->admin_id = auth()->user()->id;
        $task->user_id = $user->id;
        $task->priority = $request->priority;
        $task->save();


        $token = Str::random(60);
        $user->notify(new TaskAssginmentNotification($task, $token));

        // here may be we need to make another migration specially for task assigned by admin which will help to create section
        // of task status as like user accepted or not and other creativity so no need to save it will be user if user accept it then it will be save
        // and automatic admin can see in his dashboard for that refer TaskAssignmentController.php 
        // $task->save();
        return response()->json(['message' => "Task Detailes are sent successfully to {$user->name}"]);
    }

    public function getAssignedTaskFromUsersEndWhenTaskIsPending($taskId)
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

    public function acceptOrRejectAssignedTask(Request $request, $taskId)
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

    public function getAllAssignedTasksFromAdminsEnd()
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
