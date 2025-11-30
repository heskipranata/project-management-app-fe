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
            'assignee' => function ($q) {
                $q->select('id', 'name');
            },
        ])->paginate(20);

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Create a new task",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"project_id","name"},
     *             @OA\Property(property="project_id", type="integer", format="int64", example=1),
     *             @OA\Property(property="name", type="string", example="Design hero section"),
     *             @OA\Property(property="description", type="string", example="Create the hero section layout"),
     *             @OA\Property(property="assigned_to", type="integer", format="int64", example=2),
     *             @OA\Property(property="priority", type="string", example="medium"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-12-10"),
     *             @OA\Property(property="status", type="string", example="todo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Task"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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
        $task->load(['project', 'assignee' => function ($q) {
            $q->select('id', 'name');
        }]);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task,
        ], 201);
    }


    /**
     * @OA\Get(
     *     path="/api/tasks/{task}",
     *     tags={"Tasks"},
     *     summary="Get a single task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *     @OA\Response(
     *         response=200,
     *         description="Task retrieved successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Task"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Task $task)
    {
        $task->load(['project', 'assignee' => function ($q) {
            $q->select('id', 'name');
        }]);

        return response()->json([
            'message' => 'Task retrieved successfully',
            'data' => $task,
        ], 200);
    }


    public function detail(Task $task)
    {
        $task->load(['project', 'assignee' => function ($q) {
            $q->select('id', 'name');
        }]);

        return response()->json([
            'message' => 'Task detail retrieved successfully',
            'data' => $task,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/tasks",
     *     tags={"Tasks"},
     *     summary="Get tasks assigned to authenticated user or belonging to user's projects",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User tasks retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User tasks retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Task")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
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
                'assignee' => function ($q) {
                    $q->select('id', 'name');
                },
            ])
            ->paginate(20);

        return response()->json([
            'message' => 'User tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{task}",
     *     tags={"Tasks"},
     *     summary="Update a task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="project_id", type="integer", format="int64", example=1),
     *             @OA\Property(property="name", type="string", example="Updated task name"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="assigned_to", type="integer", format="int64", example=3),
     *             @OA\Property(property="priority", type="string", example="high"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-12-15"),
     *             @OA\Property(property="status", type="string", example="in-progress")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Task updated successfully", @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Task"))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     * @OA\Patch(
     *     path="/api/tasks/{task}",
     *     tags={"Tasks"},
     *     summary="Partially update a task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Task updated successfully", @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Task"))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/tasks/{task}",
     *     tags={"Tasks"},
     *     summary="Delete a task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *     @OA\Response(response=204, description="Task deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent();
    }
}
