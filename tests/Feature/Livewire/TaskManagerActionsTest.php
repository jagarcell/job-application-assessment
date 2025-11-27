<?php

use App\Livewire\TasksManager;
use App\Repositories\ProjectsAndTasksRepository;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Task;
use App\Models\Project;

it('tests if getProjectTasksProperty returns tasks when a project is selected', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::create(['name' => 'Fake Project 1']));
    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::create([
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

it('tests if getProjectTasksProperty returns empty tasks collection when no project is selected', function () {
    Livewire::test(TasksManager::class, ['selectedProjectId' => 0])
        ->tap(function ($component) {
            expect($component->projectTasks)->toBeInstanceOf(Collection::class);
            expect($component->projectTasks)->toBeEmpty();
        });
});


it('tests if updatedSelectedProjectId updates reset taskName and newProjectName', function () {
    Livewire::test(TasksManager::class)
        ->set('selectedProjectId', 1);

    Livewire::test(TasksManager::class)
        ->set('taskName', 'Some Task')
        ->set('newProjectName', 'Some Project')
        ->assertSet('taskName', 'Some Task')
        ->assertSet('newProjectName', 'Some Project');

    Livewire::test(TasksManager::class)
        ->set('selectedProjectId', 2)
        ->assertSet('taskName', '')
        ->assertSet('newProjectName', '');
});

it('tests if createTask does not create a task when taskName is empty', function () {
    Livewire::test(TasksManager::class)
        ->set('taskName', '')
        ->set('selectedProjectId', 1)
        ->call('createTask')
        ->assertSee(__('tasks.name_empty'));
});

it('tests if createTask resets taskName on success', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::create([
            'name' => 'Fake Task 2',
            'project_id' => 1,
            'priority' => 2,
        ])
    );

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);

    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks);

    $repo->shouldReceive('createTask')
        ->with([
            'name' => 'New Task',
            'project_id' => 1
        ])
        ->andReturn(new Task());

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1])
        ->set('taskName', 'New Task')
        ->call('createTask')
        ->assertSet('taskName', '');
});

it('tests if createTask does not reset taskName on failure and send error message', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::create([
            'name' => 'Fake Task 2',
            'project_id' => 1,
            'priority' => 2,
        ])
    );

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);

    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks)
        ->byDefault();

    $repo->shouldReceive('createTask')
        ->with([
            'name' => 'New Task',
            'project_id' => 1
        ])
        ->andReturn(null);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1])
        ->set('taskName', 'New Task')
        ->call('createTask')
        ->assertSet('taskName', 'New Task');
});

it('tests if editTask sets editingTaskId and taskName on success', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::create([
            'id' => 5,
            'name' => 'Fake Task 2',
            'project_id' => 1,
            'priority' => 2,
        ])
    );

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);

    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks)
        ->byDefault();

    $repo->shouldReceive('editTask')
        ->with(5)
        ->andReturn('Fake Task 2');

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'editingTaskId' => null, 'taskName' => ''])
        ->call('editTask', 5)
        ->assertSet('editingTaskId', 5)
        ->assertSet('taskName', 'Fake Task 2');
});

it('tests if editTask resets editingTaskId and taskName on failure and shows error message', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::create([
            'id' => 5,
            'name' => 'Fake Task 2',
            'project_id' => 1,
            'priority' => 2,
        ])
    );

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);

    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks)
        ->byDefault();

    $repo->shouldReceive('editTask')
        ->with(5)
        ->andReturn('');

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'editingTaskId' => null, 'taskName' => ''])
        ->call('editTask', 5)
        ->assertSet('editingTaskId', null)
        ->assertSet('taskName', '');
});

it('tests if updateTask does not reset fields on failure', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);
    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('updateTask')
        ->once()
        ->with(10, ['name' => 'Updated Task Name'])
        ->andReturn(false);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class)
        ->set('editingTaskId', 10)
        ->set('taskName', 'Updated Task Name')
        ->call('updateTask')
        ->assertSet('taskName', 'Updated Task Name')
        ->assertSet('editingTaskId', 10);
});

it('tests if deleteTask resets taskName and editingTaskId', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::create([
            'id' => 5,
            'name' => 'Fake Task 2',
            'project_id' => 1,
            'priority' => 2,
        ])
    );

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);
    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks)
        ->byDefault();

    $repo->shouldReceive('deleteTask')
        ->once()
        ->with(15)
        ->andReturn(true);

    $repo->shouldReceive('reorderTasks')
        ->once()
        ->with(1)
        ->andReturn(true);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'taskName' => 'Some Task', 'editingTaskId' => 15])
        ->call('deleteTask', 15)
        ->assertSet('taskName', '')
        ->assertSet('editingTaskId', null);
});
