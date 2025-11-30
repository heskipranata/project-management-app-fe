<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with([
            'tasks.assignee' => function ($q) {
                $q->select('id', 'name');
            },
            'owner' => function ($q) {
                $q->select('id', 'name');
            },
        ])->paginate(15);

        return response()->json([
            'message' => 'Projects retrieved successfully',
            'data' => $projects,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/projects",
     *     tags={"Projects"},
     *     summary="Get authenticated user's projects",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User projects retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User projects retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Project")
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
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $projects = Project::where('owner_id', $user->id)
            ->with([
                'tasks.assignee' => function ($q) {
                    $q->select('id', 'name');
                },
                'owner' => function ($q) {
                    $q->select('id', 'name');
                },
            ])
            ->paginate(15);

        return response()->json([
            'message' => 'User projects retrieved successfully',
            'data' => $projects,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/projects",
     *     tags={"Projects"},
     *     summary="Create a new project",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Project"),
     *             @OA\Property(property="description", type="string", example="Project description"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-12-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-01-01"),
     *             @OA\Property(property="status", type="string", example="planning"),
     *             @OA\Property(property="owner_id", type="integer", format="int64", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Project"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */

    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:planning,ongoing,completed',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        $authUser = Auth::user();
        if (empty($data['owner_id']) && $authUser !== null) {
            $data['owner_id'] = $authUser->id;
        }

        $project = Project::create($data);
        return response()->json([
            'message' => 'Project created successfully',
            'data' => $project,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/projects/{project}",
     *     tags={"Projects"},
     *     summary="Get a single project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project retrieved successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Project"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        return response()->json([
            'message' => 'Project retrieved successfully',
            'data' => $project->load(['tasks.assignee' => function ($q) {
                $q->select('id', 'name');
            }, 'owner' => function ($q) {
                $q->select('id', 'name');
            }]),
        ], 200);
    }

    public function detail(Project $project)
    {
        $this->authorize('view', $project);
        return response()->json([
            'message' => 'Project detail retrieved successfully',
            'data' => $project->load(['tasks.assignee' => function ($q) {
                $q->select('id', 'name');
            }, 'owner' => function ($q) {
                $q->select('id', 'name');
            }]),
        ], 200);
    }

    public function tasks(Project $project)
    {
        $this->authorize('view', $project);
        $tasks = $project->tasks()->with(['assignee' => function ($q) {
            $q->select('id', 'name');
        }])->paginate(20);

        return response()->json([
            'message' => 'Project tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/projects/{project}",
     *     tags={"Projects"},
     *     summary="Update a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Updated Project Name"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-12-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-01-01"),
     *             @OA\Property(property="status", type="string", example="ongoing"),
     *             @OA\Property(property="owner_id", type="integer", format="int64", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Project updated successfully", @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Project"))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     * @OA\Patch(
     *     path="/api/projects/{project}",
     *     tags={"Projects"},
     *     summary="Partially update a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *     @OA\RequestBody(@OA\JsonContent(type="object")),
     *     @OA\Response(response=200, description="Project updated successfully", @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Project"))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:planning,ongoing,completed',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $project->update($data);
        return response()->json([
            'message' => 'Project updated successfully',
            'data' => $project,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/projects/{project}",
     *     tags={"Projects"},
     *     summary="Delete a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer", format="int64")),
     *     @OA\Response(response=204, description="Project deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully'], 204);
    }
}
