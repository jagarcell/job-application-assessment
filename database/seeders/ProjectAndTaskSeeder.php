<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Task;

class ProjectAndTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nProjects = 5;
        $nTasksPerProject = 5;

        // Create projects with tasks
        Project::factory($nProjects)->create()->each(function ($project) use ($nTasksPerProject) {
            // Create tasks for each project
            Task::factory()->count($nTasksPerProject)->create()->each(function ($task, $index) use ($project) {
                $task->project_id = $project->id;
                $task->priority = $index + 1;
                $task->save();
            });
        });
    }
}
