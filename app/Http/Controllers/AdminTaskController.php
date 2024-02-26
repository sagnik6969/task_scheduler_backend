<?php

// app/Http/Controllers/AdminTaskController.php


namespace App\Http\Controllers;

use App\Models\AdminAssignedTask;
use App\Notifications\TaskAssginmentNotification;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminTaskController extends Controller
{
    public function index()
    {
        // $admintasks = auth()->user()->admintasks;
        // return $admintasks;

        try {
            $user = Auth::user();
            if (!$user || !$user->is_admin) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $users = User::where('is_admin', 0)->with('tasks')->get();

            return response()->json(['users' => $users]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function userTasks(User $user)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser || !$authUser->is_admin) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if ($user->is_admin) {
                return response()->json(['error' => 'The provided user is an admin'], 401);
            }

            $userWithTasks = User::with('tasks')->find($user->id);

            return response()->json(['user' => $userWithTasks]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function destroy(Task $task)
    {
        try {
            $authUser = Auth::user();
            if (!$authUser || !$authUser->is_admin) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $task->delete();
            return response()->json(['message' => 'Task deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    // public function gettotalTasksAssignedByAdmin(User $user)
    // {
    //     $user->admintasks()->count();
    //     $totalTasks = Task::where('admin_id', $user->id)->count();
    //     return response()->json(['total_tasks_assigned' => $totalTasks]);
    // }

    public function assignTaskTouser(Request $request, User $user)
    {

        $task = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'description' => 'sometimes',
            'deadline' => 'required|date',
        ]);

        if ($task->fails()) {
            return response()->json(['errors' => $task->errors()], 400);
        }

        $task = new AdminAssignedTask();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->deadline = \Carbon\Carbon::parse($request->deadline);
        $task->admin_id = auth()->user()->id;
        $task->user_id = $user->id;
        $token = Str::random(60);
        $user->notify(new TaskAssginmentNotification($task, $token));

        // here may be we need to make another migration specially for task assigned by admin which will help to create section
        // of task status as like user accepted or not and other creativity so no need to save it will be user if user accept it then it will be save
        // and automatic admin can see in his dashboard for that refer TaskAssignmentController.php 
        $task->save();
        return response()->json(['message' => 'Task assigned successfully']);
    }

    public function allUSerAnalysys()
    {

        $authUser = Auth::user();
        if (!$authUser || !$authUser->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $usersData = User::where('is_admin', 0)->withCount([
            'tasks as incomplete_task_count' => function ($query) {
                $query->where('is_completed', false);
            },
            'tasks as complete_task_count' => function ($query) {
                $query->where('is_completed', true);
            }
        ])->get();

        return response()->json($usersData);
    }
    public function taskAcceptance(User $user, Task $task)
    {
    }

    public function makeAdmin(User $user)
    {

        $authUser = Auth::user();
        // if (!$authUser || !$authUser->is_admin) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        if ($user->is_admin) {
            return response()->json(['error' => 'The provided user is already an admin'], 401);
        }

        $user->is_admin = 1;
        $user->save();
        return response()->json(['message' => 'User {$user->id} is now admin'], 200);
    }

    public function userTasksAnalysis(User $user)
    {

        $incompleteTasks = $user->tasks()
            ->where('is_completed', false)
            ->orderByDesc('updated_at')
            ->get();

        $completeTasks = $user->tasks()
            ->where('is_completed', true)
            ->orderByDesc('updated_at')
            ->get();


        return response()->json([
            'incomplete' => $incompleteTasks,
            'complete' => $completeTasks,
        ]);
    }

    // public function deleteUser(User $user)
    // {
    //     $user->delete();
    //     return response()->json(['message' => 'User deleted successfully']);
    // }

}
