<?php

use App\Livewire\TasksManager;
use App\Repositories\ProjectsAndTasksRepository;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Task;
use App\Models\Project;

it ('tests if getProjects() returns all projects in the database', function () {
    // Create some projects in the database
    Project::factory()->create(['name' => 'Project 1']);
    Project::factory()->create(['name' => 'Project 2']);

    $repository = new ProjectsAndTasksRepository();
    $projects = $repository->getProjects();

    expect($projects)->toBeInstanceOf(Collection::class)
        ->and($projects->count())->toBe(2)
        ->and(Project::where('name', 'Project 1')->exists())->toBeTrue()
        ->and(Project::where('name', 'Project 2')->exists())->toBeTrue();
});

it ('tests if createProject() creates a new project in the database', function () {
    $repository = new ProjectsAndTasksRepository();
    $projectData = ['name' => 'New Project'];
    $project = $repository->createProject($projectData);

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->name)->toBe('New Project')
        ->and(Project::where('name', 'New Project')->exists())->toBeTrue();
});

it ('tests if deleteProject() deletes a project and its tasks from the database', function () {
    // Create a project with tasks
    $project = Project::factory()->create(['name' => 'Project to Delete']);
    Task::factory()->create(['name' => 'Task 1', 'project_id' => $project->id]);
    Task::factory()->create(['name' => 'Task 2', 'project_id' => $project->id]);

    $repository = new ProjectsAndTasksRepository();
    $result = $repository->deleteProject($project->id);

    expect($result)->toBeTrue()
        ->and(Project::where('id', $project->id)->exists())->toBeFalse()
        ->and(Task::where('project_id', $project->id)->count())->toBe(0);
});

it ('tests if getTasksInProject() returns tasks for a specific project', function () {
    // Create a project with tasks
    $project = Project::factory()->create(['name' => 'Project with Tasks']);
    Task::factory()->create(['name' => 'Task A', 'project_id' => $project->id]);
    Task::factory()->create(['name' => 'Task B', 'project_id' => $project->id]);

    $repository = new ProjectsAndTasksRepository();
    $tasks = $repository->getTasksInProject($project->id);

    expect($tasks)->toBeInstanceOf(Collection::class)
        ->and($tasks->count())->toBe(2)
        ->and(Task::where('name', 'Task A')->exists())->toBeTrue()
        ->and(Task::where('name', 'Task B')->exists())->toBeTrue();
});

it ('tests if updateTask() updates a task\'s details in the database', function () {
    // Create a task
    $project = Project::factory()->create(['name' => 'Project for Task Update']);
    $task = Task::factory()->create(['name' => 'Old Task Name', 'project_id' => $project->id]);

    sleep(1); // Ensure updated_at is different from created_at for testing

    $repository = new ProjectsAndTasksRepository();
    $updatedData = ['name' => 'Updated Task Name'];
    $updatedTask = $repository->updateTask($task->id, $updatedData);

    expect($updatedTask)->toBe(true)
        ->and(Task::where('id', $task->id)->first()->name)->toBe('Updated Task Name')
        ->and(Task::where('id', $task->id)->first()->created_at)->not->toEqual($task->update);
});

it('tests if createTask() creates a new task in the selected project', function () {
    // Create a project to associate the task with
    $project = Project::factory()->create(['name' => 'Project for New Task']);

    $repository = new ProjectsAndTasksRepository();
    $taskData = ['name' => 'New Task', 'project_id' => $project->id];
    $task = $repository->createTask($taskData);

    expect($task)->toBeInstanceOf(Task::class)
        ->and($task->id)->toBe(1)
        ->and($task->priority)->toBe(1)
        ->and($task->created_at)->not->toBeNull()
        ->and($task->updated_at)->not->toBeNull()
        ->and($task->created_at)->toEqual($task->updated_at)
        ->and($task->name)->toBe('New Task')
        ->and(Task::where('name', 'New Task')->where('project_id', $project->id)->exists())->toBeTrue();
});
it('tests if editTask() retrieves the correct task name for editing', function () {
    // Create a task
    $project = Project::factory()->create(['name' => 'Project for Edit Task']);
    $task = Task::factory()->create(['name' => 'Task to Edit', 'project_id' => $project->id]);

    $repository = new ProjectsAndTasksRepository();
    $taskName = $repository->editTask($task->id);

    expect($taskName)->toBe('Task to Edit');
});

it('tests if deleteTask() deletes a task from the database', function () {
    // Create a task
    $project = Project::factory()->create(['name' => 'Project for Delete Task']);
    $task = Task::factory()->create(['name' => 'Task to Delete', 'project_id' => $project->id]);

    $repository = new ProjectsAndTasksRepository();
    $result = $repository->deleteTask($task->id);

    expect($result)->toBeTrue()
        ->and(Task::where('id', $task->id)->exists())->toBeFalse();
});

it('tests if reorderTasks() correctly reorders tasks within a project in the database based on a new priorities order', function () {
    // Create a project with tasks
    $project = Project::factory()->create(['name' => 'Project for Reorder Tasks']);
    $task1 = Task::factory()->create(['name' => 'Task 1', 'project_id' => $project->id, 'priority' => 1]);
    $task2 = Task::factory()->create(['name' => 'Task 2', 'project_id' => $project->id, 'priority' => 2]);
    $task3 = Task::factory()->create(['name' => 'Task 3', 'project_id' => $project->id, 'priority' => 3]);

    $repository = new ProjectsAndTasksRepository();

    Task::where('id', $task2->id)->delete();

    $result = $repository->reorderTasks($project->id);

    expect($result)->toBeTrue()
        ->and(Task::where('id', $task1->id)->first()->priority)->toBe(1)
        ->and(Task::where('id', $task3->id)->first()->priority)->toBe(2);
});

it('tests if setNewTaksOrder() correctly updates tasks order based on a new ordered list of task IDs', function () {
    // Create a project with tasks
    $project = Project::factory()->create(['name' => 'Project for Set New Tasks Order']);
    $task1 = Task::factory()->create(['name' => 'Task 1', 'project_id' => $project->id, 'priority' => 1]);
    $task2 = Task::factory()->create(['name' => 'Task 2', 'project_id' => $project->id, 'priority' => 2]);
    $task3 = Task::factory()->create(['name' => 'Task 3', 'project_id' => $project->id, 'priority' => 3]);

    $repository = new ProjectsAndTasksRepository();
    $orderedTaskIdsStr = "{$task3->id},{$task1->id},{$task2->id}";

    $repository->setNewTaksOrder($orderedTaskIdsStr);

    expect(Task::where('id', $task3->id)->first()->priority)->toBe(1)
        ->and(Task::where('id', $task1->id)->first()->priority)->toBe(2)
        ->and(Task::where('id', $task2->id)->first()->priority)->toBe(3);
});