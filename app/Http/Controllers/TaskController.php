<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with([
            'project',
            'assignee' => function ($q) { $q->select('id', 'name'); },
        ])->paginate(20);

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:todo,in-progress,done',
        ]);

        $task = Task::create($data);
        $task->load(['project', 'assignee' => function ($q) { $q->select('id', 'name'); }]);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task,
        ], 201);
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assignee' => function ($q) { $q->select('id', 'name'); }]);

        return response()->json([
            'message' => 'Task retrieved successfully',
            'data' => $task,
        ], 200);
    }

    /**
     * Return a more detailed view of a task (with project, assignee and any relations)
     */
    public function detail(Task $task)
    {
        $task->load(['project', 'assignee' => function ($q) { $q->select('id', 'name'); }]);

        return response()->json([
            'message' => 'Task detail retrieved successfully',
            'data' => $task,
        ], 200);
    }

    /**
     * Return tasks assigned to the authenticated user or belonging to projects owned by the user.
     */
    public function mine(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $tasks = Task::where('assigned_to', $user->id)
            ->orWhereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })
            ->with([
                'project',
                'assignee' => function ($q) { $q->select('id', 'name'); },
            ])
            ->paginate(20);

        return response()->json([
            'message' => 'User tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'project_id' => 'sometimes|exists:projects,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:todo,in-progress,done',
        ]);

        $task->update($data);
        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task,
        ], 200);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent();

    }
}
