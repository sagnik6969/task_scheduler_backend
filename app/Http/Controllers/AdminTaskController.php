<?php

// app/Http/Controllers/AdminTaskController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class AdminTaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return $tasks;
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }
}
