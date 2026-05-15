<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | USER TASKS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return Task::where('user_id', auth()->id())
            ->orderBy('position')
            ->paginate(6);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN TASKS
    |--------------------------------------------------------------------------
    */

    public function adminTasks()
    {
        $tasks = Task::with('user')
            ->orderBy('position')
            ->paginate(10);

        return response()->json([
            'data' => $tasks->items(),
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE TASK
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:todo,progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $lastPosition = Task::max('position') ?? 0;

        $task = Task::create([
            'title' => $request->title,
            'priority' => $request->priority,
            'status' => $request->status,
            'completed' => $request->status === 'completed',
            'user_id' => auth()->id(),
            'due_date' => $request->due_date,
            'position' => $lastPosition + 1,
        ]);

        return response()->json($task, 201);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE TASK
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:todo,progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $validated['completed'] =
            $validated['status'] === 'completed';

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE TASK
    |--------------------------------------------------------------------------
    */

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE TASK
    |--------------------------------------------------------------------------
    */

    public function toggle(Task $task)
    {
        $task->completed = !$task->completed;

        $task->status =
            $task->completed
                ? 'completed'
                : 'todo';

        $task->save();

        return response()->json([
            'message' => 'Task toggled',
            'task' => $task
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REORDER TASKS
    |--------------------------------------------------------------------------
    */

    public function reorder(Request $request)
    {
        foreach ($request->tasks as $taskData) {

            Task::where('id', $taskData['id'])
                ->update([
                    'position' => $taskData['position'],
                    'status' => $taskData['status'],
                    'completed' =>
                        $taskData['status'] === 'completed'
                ]);
        }

        return response()->json([
            'message' => 'Tasks reordered successfully'
        ]);
    }
}