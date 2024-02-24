<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskAssignmentController extends Controller
{
    public function assignTask($taskId)
    {
        
        $user = Auth::user();

        $task = Task::findOrFail($taskId);

        if ($user->tasks()->where('id', $taskId)->exists()) {
            return response()->json([
                'message' => 'Task is already accepted.',
                'link' => '/'
            ]);
        }

        $user->tasks()->attach($task);

        return response()->json([
            'message' => 'Task assigned successfully.',
            'link' => '/'
        ]);
    }
}
