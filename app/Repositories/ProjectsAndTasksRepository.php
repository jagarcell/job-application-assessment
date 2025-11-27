<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class ProjectsAndTasksRepository
{
    /**
     * getProjects
     * @return Collection
     */
    public function getProjects(): Collection
    {
        // Logic to retrieve projects from the database
        return Project::all();
    }

    /**
     * createProject
     * @param array $data
     */
    public function createProject(array $data): ?Project
    {
        // Logic to create a new project
        try {
            return Project::create($data);
        } catch (\Exception $e) {
            \Log::error("Failed to create project: " . $e->getMessage());
            return null;
        }
    }

    /**
     * deleteProject
     * @param int $projectId
     * @return bool
     */
    public function deleteProject(int $projectId): bool
    {
        // Logic to delete a project
        try {
            DB::transaction(function () use ($projectId) {
                $project = Project::findOrFail($projectId);
                $project->tasks()->delete(); // Delete associated tasks first
                $project->delete();
            });
            return true;
        } catch (\Exception $e) {
            // Handle the exception, e.g., log the error or show a message
            Log::error("Failed to delete project with ID {$projectId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * getTasksInProject
     * @param int $projectId
     * @return Collection
     */
    public function getTasksInProject(int $projectId): Collection
    {
        // Logic to retrieve tasks for a specific project
        if (!$projectId) {
            return new Collection();
        }
        return Task::where('project_id', $projectId)
            ->orderBy('priority', 'asc')
            ->get();
    }

    /**
     * updateTask
     * @param int $taskId
     * @param array $data
     * @return bool
     */
    public function updateTask(int $taskId, array $data): bool
    {
        // Logic to update a task
        try {
            $task = Task::findOrFail($taskId);
            $task->update($data);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to update task ID {$taskId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * createTask
     * @param array $data
     * @return Task|null
     */
    public function createTask(array $data): ?Task
    {
        $data['priority'] = Task::where('project_id', $data['project_id'])->count() + 1;
        // Logic to create a new task
        try {
            // Additional logic can be added here if needed
            return Task::create($data);
        } catch (\Exception $e) {
            \Log::error("Failed to create task: " . $e->getMessage());
            return null;
        }
    }

    /**
     * editTask
     * @param int $taskId
     * @param array $data
     * @return string
     */
    public function editTask(int $taskId): string
    {
        // Logic to edit a task
        try {
            $task = Task::findOrFail($taskId);
            return $task->name;
        } catch (\Exception $e) {
            Log::error("Failed to edit task ID {$taskId}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * deleteTask
     * @param int $taskId
     * @return bool
     */
    public function deleteTask(int $taskId): bool
    {
        // Logic to delete a task
        try {
            $task = Task::findOrFail($taskId);
            $task->delete();
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to delete task ID {$taskId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * reorderTasks
     * @param int $projectId
     * @return bool
     */
    public function reorderTasks(int $projectId): bool
    {
        // Logic to reorder tasks within a project
        try {
            // Reorder task priorities using a MySQL user-defined variable
            // This is more efficient thank sticking to Eloquent for this operation
            DB::statement('SET @rank := 0;');
            DB::statement('UPDATE tasks SET priority = (@rank := @rank + 1), updated_at = NOW() WHERE project_id = ? ORDER BY priority;', [$projectId]);

            // We could have done something like this with Eloquent, but it would be less efficient leading to N + 1 queries:

            //  $tasksToReorder = Task::where('project_id', $this->selectedProject)
            //              ->orderBy('priority')
            //              ->get();

            // foreach ($tasksToReorder as $index => $task) {
            //     $task->update(['priority' => $index + 1]);
            // }
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to reorder tasks in project ID {$projectId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * setNewTaksOrder
     * @param string $orderedTaskIdsStr
     * @return void
     */
    public function setNewTaksOrder(string $orderedTaskIds): void
    {
        if (empty($orderedTaskIdsStr)) {
            return;
        }
        // Let's convert the string back to an array for easier processing
        $orderedTaskIds = explode(',', $orderedTaskIdsStr);

        // We use a raw query to update the priorities in a single query for efficiency
        // Instead of looping through each task and updating them one by one using Eloquent.

        // Build the CASE SQL query to update priorities in a single query
        $setPrioritiesSqlQuery = "CASE id ";
        foreach ($orderedTaskIds as $index => $taskId) {
            $priority = $index + 1;
            $setPrioritiesSqlQuery .= "WHEN {$taskId} THEN {$priority} ";
        }
        $setPrioritiesSqlQuery .= "END";
        $ids = implode(',', $orderedTaskIds);
        // Execute the update query to reorder tasks
        DB::statement("UPDATE tasks SET priority = {$setPrioritiesSqlQuery}, updated_at = NOW() WHERE id IN ({$ids})");

        // We could have done something like this with Eloquent, but it would be less efficient leading to N queries iunstead of just one:

        // foreach ($orderedTaskIds as $index => $taskId) {
        //     Task::where('id', $taskId)->update(['priority' => $index + 1]);
        // }

    }
}
