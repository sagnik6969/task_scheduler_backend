<?php


namespace App\Http\Controllers;


use App\Http\Resources\Notification;
use App\Http\Resources\TaskCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\Task as TaskResouces;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserTaskController extends Controller
{

    public function __construct()
    {
        //  user authentication
    }

    public function index()
    {
        try {
            //these need to change according to the auth user
            // $tasks = Task::where('user_id', 2)->get();
            $tasks = auth()->user()->tasks;
            return new TaskCollection($tasks, 'index');
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'sometimes',
            'deadline' => 'required|date',
            'is_completed' => 'sometimes',
            'progress' => 'sometimes',
            'priority' => 'required|in:' . implode(',', array_values(Task::$priorities)),
        ]);
        if ($data->fails()) {
            return response()->json(['errors' => $data->errors()], 422);
        }
        $title = $request->title;
        $description = $request->description;
        $deadline = $request->deadline;
        $is_completed = $request->is_completed ?? false;
        $progress = $request->progress ?? 0;
        $priority = $request->priority;
        $user_id = auth()->user()->id;
        try {
            $task = Task::create([
                'title' => $title,
                'description' => $description,
                'deadline' => $deadline,
                'is_completed' => $is_completed,
                'progress' => $progress,
                'priority' => $priority,
                'user_id' => $user_id
            ]);
            return new TaskResouces($task, 'create');
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    //one task
    public function show(string $id)
    {
        try {
            $task = Task::findOrfail($id);
            if ($task) {
                return new TaskResouces($task, 'show');
            } else {
                return response()->json(['message' => 'Task not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    protected $priorities = [
        'normal' => 'Normal',
        'important' => 'Important',
        'very_important' => 'Very Important',
    ];

    public function update(Request $request, string $id)
    {
        $data = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'sometimes',
            'deadline' => 'required|date',
            'is_completed' => 'sometimes',
            'progress' => 'sometimes',
            'priority' => 'required|in:' . implode(',', array_values(Task::$priorities)),
        ]);
        if ($data->fails()) { 
            return response()->json($data->errors(), 422);
            // return response()->json(['message' => 'Validation failed'], 400);
        }
        $title = $request->title;
        $description = $request->description;
        $deadline = $request->deadline;
        $is_completed = $request->is_completed ?? false;
        $progress = $request->progress ?? 0;
        $priority = $request->priority;
        try {
            $task = Task::findOrfail($id);
            if ($task) {
                $task->update([
                    'title' => $title,
                    'description' => $description,
                    'deadline' => $deadline,
                    'is_completed' => $is_completed,
                    'progress' => $progress,
                    'priority' => $priority,
                ]);
                return new TaskResouces($task, 'update');
            } else {
                return response()->json(['message' => 'Task not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function edit(string $id)
    {
        try {
            $task = Task::findOrfail($id);
            if ($task) {
                return new TaskResouces($task, 'edit');
            } else {
                return response()->json(['message' => 'Task not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $task = Task::findOrfail($id);
            if ($task) {
                $task->delete();
                return response()->json(['message' => 'Task deleted'], 200);
            } else {
                return response()->json(['message' => 'Task not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function userTasksAnalysis()
    {
        $user = Auth::user();

        $timeRange = request()->time_range;
        $statistics = request()->statistics;

        $getTime = [
            'last_hour' => [now()->subHour(), now()],
            'today' => [now()->subDay(), now()],
            'past_weak' => [now()->subWeek(), now()],
            'past_month' => [now()->subMonth(), now()],
            'past_year' => [now()->subYear(), now()],
            'all' => [null, null]
        ];

        if ($statistics == 'completed_vs_pending_tasks') {
            $numberOfCompletedTasks = $user
                ->tasks()
                ->where('is_completed', 1)
                ->timeFilter($getTime[$timeRange][0])
                ->count();

            $numberOfIncompleteTasks = $user->tasks()
                ->where('is_completed', 0)
                ->timeFilter($getTime[$timeRange][0])
                ->count();

            return response()->json([
                'series' => [$numberOfCompletedTasks, $numberOfIncompleteTasks],
                'labels' => ['Completed Tasks', 'Incomplete Tasks']
            ]);
        } elseif ($statistics == 'task_distribution_by_progress') {

            $lessThan25percentProgress = $user->tasks()
                ->where('progress', '<', 25)
                ->timeFilter($getTime[$timeRange][0])
                ->count();

            $from25to50percentProgress = $user->tasks()
                ->whereBetween('progress', [25, 50])
                ->timeFilter($getTime[$timeRange][0])
                ->count();

            $from51to75percentProgress = $user->tasks()
                ->whereBetween('progress', [51, 75])
                ->timeFilter($getTime[$timeRange][0])
                ->count();

            $moreThan75percentProgress = $user->tasks()
                ->whereBetween('progress', [75, 99])
                ->timeFilter($getTime[$timeRange][0])
                ->count();

            $noOfCompletedTasks = $user->tasks()
                ->where('is_completed', 1)
                ->timeFilter($getTime[$timeRange][0])
                ->count();

            return response()->json([
                'series' => [
                    $lessThan25percentProgress,
                    $from25to50percentProgress,
                    $from51to75percentProgress,
                    $moreThan75percentProgress,
                    $noOfCompletedTasks
                ],
                'labels' => [
                    'Less than 25%',
                    'From 25% to 50%',
                    'From 51% to 75%',
                    'More than 75%',
                    'Completed'
                ]
            ]);
        } else if ($statistics == 'task_distribution_by_priority') {
            $response = [
                'series' => [],
                'labels' => []
            ];
            foreach (Task::$priorities as $priority) {
                $response['series'][] = Task::where('priority', $priority)
                    ->timeFilter($getTime[$timeRange][0])
                    ->count();
                $response['labels'][] = $priority;
            }

            return response()->json($response);
        }
    }

    public function getNotifications()
    {
        $notifications = auth()->user()->unreadNotifications;

        return Notification::collection($notifications);
    }

    public function makeNotificationsAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => 'all unread notifications are marked as read'
        ]);
    }
    public function calculateOverallEfficiency()
    {
        $user = auth()->user();
        $completedTasks = $user->tasks()->where('is_completed', 1)->get();
        $totalEfficiency = 0;
        $totalTasks = $completedTasks->count();

        foreach ($completedTasks as $task) {
            $createdAt = Carbon::parse($task->created_at);
            $updatedAt = Carbon::parse($task->updated_at);

            $differenceInHours = $createdAt->diffInHours($updatedAt);

            if ($differenceInHours <= 24) {
                $efficiencyRating = 5;
            } elseif ($differenceInHours <= 48) {
                $efficiencyRating = 4;
            } elseif ($differenceInHours <= 72) {
                $efficiencyRating = 3;
            } else {
                $efficiencyRating = 2;
            }

            $totalEfficiency += $efficiencyRating;
        }

        $averageEfficiency = $totalTasks > 0 ? $totalEfficiency / $totalTasks : 0;

        if ($averageEfficiency >= 4.5) {
            $overallEfficiencyRating = 'Excellent';
        } elseif ($averageEfficiency >= 3.5) {
            $overallEfficiencyRating = 'Good';
        } elseif ($averageEfficiency >= 2.5) {
            $overallEfficiencyRating = 'Average';
        } else {
            $overallEfficiencyRating = 'Needs Improvement';
        }

        return response()->json([
            'total_tasks' => $totalTasks,
            'average_efficiency' => $averageEfficiency,
            'overall_efficiency_rating' => $overallEfficiencyRating
        ]);
    }
}
