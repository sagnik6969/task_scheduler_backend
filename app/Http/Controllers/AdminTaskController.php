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

        try {
            $user = Auth::user();
            if (!$user || !$user->is_admin) {
                return response()->json(['error' => 'Unauthorized Access'], 400);
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

    public function singleUserWithTasks(User $user)
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


    // public function allUSerAnalysys()
    // {

    //     $authUser = Auth::user();
    //     if (!$authUser || !$authUser->is_admin) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $usersData = User::where('is_admin', 0)->withCount([
    //         'tasks as incomplete_task_count' => function ($query) {
    //             $query->where('is_completed', false);
    //         },
    //         'tasks as complete_task_count' => function ($query) {
    //             $query->where('is_completed', true);
    //         }
    //     ])->get();

    //     return response()->json($usersData);
    // }
    // public function taskAcceptance(User $user, Task $task)
    // {
    // }

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

    // public function userTasksAnalysis(User $user)
    // {

    //     $incompleteTasks = $user->tasks()
    //         ->where('is_completed', false)
    //         ->orderByDesc('updated_at')
    //         ->get();

    //     $completeTasks = $user->tasks()
    //         ->where('is_completed', true)
    //         ->orderByDesc('updated_at')
    //         ->get();


    //     return response()->json([
    //         'incomplete' => $incompleteTasks,
    //         'complete' => $completeTasks,
    //     ]);
    // }


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

        for ($i = 0; $i <= 90; $i += 10) {
            $start = $i == 0 ? $i : $i + 1;
            $end = $i + 10;
            $response['labels'][] = "From {$start}% to {$end}%";
            $response['series'][] = Task::whereHas('user', fn($q) => $q->where('is_admin', 0))->whereBetween('progress', [$start, $end])
                ->where('is_completed', 0)
                ->count();
        }

        $response['labels'][] = 'Completed';
        $response['series'][] = Task::whereHas('user', fn($q) => $q->where('is_admin', 0))->where('is_completed', 1)
            ->count();

        return response()->json($response);


    }



}
