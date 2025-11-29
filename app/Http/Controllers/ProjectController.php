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
     * Return projects owned by the authenticated user.
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

    /**
     * Return detailed project with tasks and their assignees
     */
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

    /**
     * List tasks for a specific project
     */
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

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully'], 204);
    }
}
