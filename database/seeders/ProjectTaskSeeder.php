<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ProjectTaskSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(5)->create();

        Project::factory(10)->create()->each(function (Project $project) use ($users) {
            $project->owner_id = $users->random()->id;
            $project->save();

            Task::factory(rand(3, 8))->create([
                'project_id' => $project->id,
                'assigned_to' => $users->random()->id,
            ]);
        });
    }
}
