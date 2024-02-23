<?php


namespace App\Http\Controllers;


use App\Http\Resources\TaskCollection;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\Task as TaskResouces;
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
            $task = Task::all();
            return new TaskCollection($task, 'index');
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'deadline' => 'required',
            'is_completed' => 'sometimes',
            'progress' => 'sometimes',
            'priority' => 'required|in:' . implode(',', array_keys(Task::$priorities)),
        ]);
        if ($data->fails()) {
            return response()->json(['message' => 'Validation failed'], 400);
        }
        $title = $request->title;
        $description = $request->description;
        $deadline = $request->deadline;
        $is_completed = $request->is_completed ?? false;
        $progress = $request->progress ?? 0;
        $priority = $request->priority;
        try {
            $task = Task::create([
                'title' => $title,
                'description' => $description,
                'deadline' => $deadline,
                'is_completed' => $is_completed,
                'progress' => $progress,
                'priority' => $priority,
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


    public function update(Request $request, string $id)
    {
        $data = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'deadline' => 'required',
            'is_completed' => 'sometimes',
            'progress' => 'sometimes',
            'priority' => 'required|in:' . implode(',', array_keys(Task::$priorities)),
        ]);
        if ($data->fails()) {
            return response()->json(['message' => 'Validation failed'], 400);
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
}
