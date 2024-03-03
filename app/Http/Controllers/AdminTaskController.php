<?php

// app/Http/Controllers/AdminTaskController.php


namespace App\Http\Controllers;

use App\Models\AdminAssignedTask;
use App\Models\AdminAssignTask;
use App\Notifications\MakeAdminNotification;
use App\Notifications\TaskAssginmentNotification;
use Illuminate\Database\Eloquent\Builder;
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

            $users = User::where('is_admin', 0)
                ->with('tasks')
                ->withCount([
                    'tasks as completed_tasks' => fn(Builder $query) => $query->where('is_completed', 1),
                    'tasks as incomplete_tasks' => fn(Builder $query) => $query->where('is_completed', 0)
                ])
                ->get();

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
            $task->adminassigntasks()->delete();
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
        return response()->json(['message' => `Task Detailes are sent successfully to {$user->name}`]);
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
        if (!$authUser || !$authUser->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->is_admin) {
            return response()->json(['message' => 'The provided user is already an admin'], 401);
        }

        $user->is_admin = 1;
        $user->save();
        $user->notify(new MakeAdminNotification());
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


    public function allUserTaskProgressAnalysis()
    {
        $authUser = request()->user();
        if (!$authUser->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 401);

        }

        $response = [
            'series' => [],
            'labels' => []
        ];

        for ($i = 0; $i < 90; $i += 10) {
            $start = $i == 0 ? $i : $i + 1;
            $end = $i + 10;
            $response['labels'][] = "From {$start}% to {$end}%";
            $response['series'][] = Task::whereBetween('progress', [$start, $end])
                ->count();

        }

        $response['labels'][] = 'Completed';
        $response['series'][] = Task::where('is_completed', 1)
            ->count();

        return response()->json($response);

        // $lessThan25percentProgress =
        //     Task::where('progress', '<', 25)
        //         ->count();

        // $from25to50percentProgress =
        //     Task::whereBetween('progress', [25, 50])
        //         ->count();

        // $from51to75percentProgress = Task::whereBetween('progress', [51, 75])
        //     ->count();

        // $moreThan75percentProgress = Task::whereBetween('progress', [75, 99])
        //     ->count();

        // $noOfCompletedTasks =
        //     Task::where('is_completed', 1)
        //         ->count();

        // return response()->json([
        //     'series' => [
        //         $lessThan25percentProgress,
        //         $from25to50percentProgress,
        //         $from51to75percentProgress,
        //         $moreThan75percentProgress,
        //         $noOfCompletedTasks
        //     ],
        //     'labels' => [
        //         'Less than 25%',
        //         'From 25% to 50%',
        //         'From 51% to 75%',
        //         'More than 75%',
        //         'Completed'
        //     ]
        // ]);

    }

    // public function deleteUser(User $user)
    // {
    //     $user->delete();
    //     return response()->json(['message' => 'User deleted successfully']);
    // }

}
