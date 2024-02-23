<?php


namespace App\Http\Controllers;


use App\Http\Resources\TaskCollection;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\Task as TaskResouces;

class UserTaskController extends Controller
{
    
    public function __construct(){
        //  user authentication
    }

    public function index(Request $request)
    {
        $user = $request->user();
        return $user->tasks;
    }

    // task creation -- frontend
    public function create()
    {
        // return view('task.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'is_completed' => 'sometimes|boolean',
            'progress' => 'sometimes|integer|min:0|max:100',
            'priority' => 'required|in:' . implode(',', array_keys(Task::$priorities)),
        ]); 

        // var_dump($validatedData);
     
        $task = Task::create([
            'user_id' => 2,
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'deadline' => $validatedData['deadline'],
            'is_completed' => $validatedData['is_completed'] ?? false,
            'progress' => $validatedData['progress'] ?? 0, 
            'priority' => $validatedData['priority'],
        ]);
        // changes done
        // var_dump($task);

        return new TaskResouces($task,'create');
        // $tasks = new Collection([$task]);


        // return new TaskCollection($task,'create');
    }

    public function edit(Task $myTask){
        return new TaskResouces($myTask,'edit');
    }

    public function update(Request $request, Task $task)
    {
        $validatedData = request()->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'is_completed' => 'sometimes|boolean',
            'progress' => 'sometimes|integer|min:0|max:100',
            'priority' => 'required|in:' . implode(',', array_keys(Task::$priorities)),
        ]); 

        $task->update($validatedData);
        return new TaskCollection($task,'update');
    }

    public function show(Task $id)
    {
        return new TaskResouces($id,'show');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return new TaskCollection($task,'delete');
    }
}
