<?php

use App\Livewire\TasksManager;
use App\Repositories\ProjectsAndTasksRepository;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Task;
use App\Models\Project;

it('test if getProjectTasksProperty returns tasks when a project is selected', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::create(['name' => 'Fake Project 1']));
    $fakeTasks = new Collection();
    $fakeTasks->push(Task::create([
        'name' => 'Fake Task 2',
        'project_id' => 1,
        'priority' => 2,
        ])
    );

    // Mock the repository
    $mockRepo = Mockery::mock(ProjectsAndTasksRepository::class);
    $mockRepo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $mockRepo->shouldReceive('getTasksInProject')
        ->with(1)
        ->once()
        ->andReturn($fakeTasks);

    // Bind the mock to the container so Livewire receives it
    app()->instance(ProjectsAndTasksRepository::class, $mockRepo);

    // Test Livewire component

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1])
        ->tap(function ($component) use ($fakeTasks) {
            // Access the computed property
            expect($component->projectTasks)->toEqual($fakeTasks);
        });
});

it('test if getProjectTasksProperty returns empty tasks collection when no project is selected', function () {
    Livewire::test(TasksManager::class, ['selectedProjectId' => 0])
        ->tap(function ($component) {
            expect($component->projectTasks)->toBeInstanceOf(Collection::class);
            expect($component->projectTasks)->toBeEmpty();
        });
});