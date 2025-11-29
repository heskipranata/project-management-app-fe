<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project)
    {
        return $project->owner_id === $user->id
            || $project->tasks()->where('assigned_to', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project)
    {
        return $project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project)
    {
        return $project->owner_id === $user->id;
    }
}
