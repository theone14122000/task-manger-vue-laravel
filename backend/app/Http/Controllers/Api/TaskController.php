<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        return Task::where('user_id', auth()->id())
            ->latest()
            ->paginate(6);
    }
    public function adminTasks()
    {
        return Task::with('user')
            ->latest()
            ->paginate(6);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);

        return Task::create([
            'title' => $request->title,
            'completed' => false,
            'user_id' => $request->user()->id,
            'priority' => $request->priority ?? 'medium',
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $task->update([
            'title' => $request->title,
            'completed' => $request->completed ?? $task->completed
        ]);

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'deleted']);
    }
    public function toggle(Task $task)
    {
        $task->completed = !$task->completed;

        $task->save();

        return response()->json($task);
    }
    
}