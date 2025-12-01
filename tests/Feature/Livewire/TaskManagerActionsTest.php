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
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));
    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
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
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
            'name' => 'Fake Task 1',
            'project_id' => 1,
            'priority' => 1,
        ])
    );
    // Mock the repository
    $mockRepo = Mockery::mock(ProjectsAndTasksRepository::class);

    $mockRepo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $mockRepo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $mockRepo);

    Livewire::test(TasksManager::class)
        ->set('taskName', '')
        ->set('selectedProjectId', 1)
        ->call('createTask')
        ->assertSee(__('tasks.name_empty'));
});

it('tests if createTask resets taskName on success', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
            'name' => 'Fake Task 1',
            'project_id' => 1,
            'priority' => 1,
        ])
    );

    // Mock the repository
    $mockRepo = Mockery::mock(ProjectsAndTasksRepository::class);

    $mockRepo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $mockRepo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks);

    $newTask = Task::factory()->create([
        'name' => 'New Task 1',
        'project_id' => 1,
        'priority' => 2,
    ]);
    $mockRepo->shouldReceive('createTask')
        ->with([
            'name' => 'New Task 1',
            'project_id' => 1
        ])
        ->andReturn($newTask);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $mockRepo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1])
        ->set('taskName', 'New Task 1')
        ->call('createTask')
        ->assertSet('taskName', '');
});

it('tests if createTask does not reset taskName on failure and send error message', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
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
            'name' => 'New Task 2',
            'project_id' => 1
        ])
        ->andReturn(null);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1])
        ->set('taskName', 'New Task 2')
        ->call('createTask')
        ->assertSet('taskName', 'New Task 2');
});

it('tests if editTask sets editingTaskId and taskName on success', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
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
        ->with(1)
        ->andReturn('Fake Task 2');

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'editingTaskId' => null, 'taskName' => ''])
        ->call('editTask', 1)
        ->assertSet('editingTaskId', 1)
        ->assertSet('taskName', 'Fake Task 2');
});

it('tests if editTask resets editingTaskId and taskName on failure and shows error message', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
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
        ->with(1)
        ->andReturn('');

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'editingTaskId' => null, 'taskName' => ''])
        ->call('editTask', 1)
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
        ->with(1, ['name' => 'Updated Task Name'])
        ->andReturn(false);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class)
        ->set('editingTaskId', 1)
        ->set('taskName', 'Updated Task Name')
        ->call('updateTask')
        ->assertSet('taskName', 'Updated Task Name')
        ->assertSet('editingTaskId', 1);
});

it('tests if deleteTask resets taskName and editingTaskId', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
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
        ->with(1)
        ->andReturn(true);

    $repo->shouldReceive('reorderTasks')
        ->once()
        ->with(1)
        ->andReturn(true);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'taskName' => 'Some Task', 'editingTaskId' => 1])
        ->call('deleteTask', 1)
        ->assertSet('taskName', '')
        ->assertSet('editingTaskId', null);
});

it('tests if tasksOrderChanged calls setNewTaksOrder in repository', function () {
    // Fake tasks to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $task1 = Task::factory()->create([
        'name' => 'Fake Task 1',
        'project_id' => 1,
        'priority' => 1,
    ]);
    $fakeTasks->push($task1);

    $task2 = Task::factory()->create([
        'name' => 'Fake Task 2',
        'project_id' => 1,
        'priority' => 2,
    ]);
    $fakeTasks->push($task2);

    $task3 = Task::factory()->create([
        'name' => 'Fake Task 3',
        'project_id' => 1,
        'priority' => 3,
    ]);
    $fakeTasks->push($task3);

    $task4 = Task::factory()->create([
        'name' => 'Fake Task 4',
        'project_id' => 1,
        'priority' => 4,
    ]);
    $fakeTasks->push($task4);

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);
    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('getTasksInProject')
        ->with(1)
        ->andReturn($fakeTasks)
        ->byDefault();

    $repo->shouldReceive('setNewTaksOrder')
        ->once()
        ->with('3,1,2,4')
        ->andReturnNull();

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1])
        ->call('tasksOrderChanged', '3,1,2,4');

});

it('tests if createProject does not create a project when newProjectName is empty and shows an error message', function () {
    Livewire::test(TasksManager::class)
        ->set('newProjectName', '')
        ->call('createProject')
        ->assertSee(__('projects.name_empty'));
});

it('tests if createProject creates a new project and shows it among the options, select it on the blade and resets fields on success', function () {
    // Fake projects to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $task = Task::factory()->create([
        'name' => 'Fake Task 1',
        'project_id' => 1,
        'priority' => 1,
    ]);

    $fakeTasks = new Collection();
    $fakeTasks->push($task);

    // Mock the repository
    $repo = Mockery::mock(ProjectsAndTasksRepository::class);

    $repo->shouldReceive('getProjects')
        ->andReturn($fakeProjects);

    $repo->shouldReceive('getTasksInProject')
        ->with(2)
        ->andReturn($fakeTasks)
        ->byDefault();

    $newProject = Project::factory()->create(
        [
            'name' => 'New Project', 
            'description' => '',
        ]
    );

    $repo->shouldReceive('createProject')
        ->with([
            'name' => 'New Project',
        ])
        ->andReturn($newProject);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class)
        ->set('newProjectName', 'New Project')
        ->call('createProject')
        ->assertSet('newProjectName', '')
        ->assertSet('taskName', '')
        ->assertSet('editingTaskId', null)
        ->assertSet('selectedProjectId', 2)
        ->assertSee('New Project');
});

it('tests if deleteProject deletes the project\'s record and resets fields on success', function () {
    // Fake projects to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
            'name' => 'Fake Task 1',
            'project_id' => 1,
            'priority' => 1,
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

    $repo->shouldReceive('deleteProject')
        ->with(1)
        ->andReturn(true);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'taskName' => 'Some Task', 'editingTaskId' => 1, 'newProjectName' => 'Some Project'])
        ->call('deleteProject', 1)
        ->assertSet('taskName', '')
        ->assertSet('editingTaskId', null)
        ->assertSet('newProjectName', '')
        ->assertSet('selectedProjectId', 0);
});

it('tests if deleteProject shows an error message on failure', function () {
    // Fake projects to return from the repository
    $fakeProjects = new Collection();
    $fakeProjects->push(Project::factory()->create(['name' => 'Fake Project 1']));

    $fakeTasks = new Collection();
    $fakeTasks->push(
        Task::factory()->create([
            'name' => 'Fake Task 1',
            'project_id' => 1,
            'priority' => 1,
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

    $repo->shouldReceive('deleteProject')
        ->with(1)
        ->andReturn(false);

    // Bind mock into container
    app()->instance(ProjectsAndTasksRepository::class, $repo);

    Livewire::test(TasksManager::class, ['selectedProjectId' => 1, 'taskName' => 'Some Task', 'editingTaskId' => 1, 'newProjectName' => 'Some Project'])
        ->call('deleteProject', 1)
        ->assertSee(__('projects.delete_failed'));
});