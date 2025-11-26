<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Repositories\ProjectsAndTasksRepository;

class TasksManager extends Component
{
    /**
     * Browser tab title
     * @var string
     */
    public string $title = 'Tasks Manager';

    /**
     * Selected task name 
     * Used to edit or create a task
     * @var string
     */
    public string $taskName = '';

    /**
     * Editing task ID
     * Used to identify the task being edited
     * @var int|null
     */
    public ?int $editingTaskId = null;

    /**
     * The selected Project ID
     * Used to identify the currently selected project
     * @var int
     */
    public int $selectedProjectId = 0;

    /**
     * projects
     * The list of projects
     * 
     * @var Collection
     */
    public Collection $projects;

    /**
     * newProjectName
     * The name for the new project to be created
     * 
     * @var string
     */
    public string $newProjectName = '';

    /**
     * projectsAndTasksRepository
     * 
     * @var ProjectsAndTasksRepository
     */
    private ProjectsAndTasksRepository $projectsAndTasksRepository;

    /**
     * The event listeners for the component.
     * 
     * @var array
     */
    protected $listeners = [
        'tasksOrderChanged',
    ];

    public function boot(ProjectsAndTasksRepository $projectsAndTasksRepository): void
    {
        // You can initialize the repository here if needed
        $this->projectsAndTasksRepository = $projectsAndTasksRepository;
    }

    /**
     * Called when a component is created
     * 
     * @return void
     */
    public function mount()
    {
        // Initialization code can go here
        $this->projects = $this->projectsAndTasksRepository->getProjects();
    }

    /**
     * updatedSelectedProjectId
     * Magic method called when selectedProjectId is updated.
     * Resets relevant properties to ensure a clean state when switching projects.
     * 
     * @return void
     */
    public function updatedSelectedProjectId(): void
    {
        $this->reset(['taskName', 'editingTaskId', 'newProjectName']);
    }

    /**
     * getProjectTasksProperty
     * This asures the updating of the tasks list in the blade view when there are changes.
     * 
     * @return Collection
     */
    public function getProjectTasksProperty(): Collection
    {
        if ($this->selectedProjectId) {
            try {
                return $this->projectsAndTasksRepository->getTasksInProject($this->selectedProjectId);
            } catch (\Exception $e) {
                Log::error("Failed to load tasks for project ID {$this->selectedProjectId}: " . $e->getMessage());
                return new Collection();
            }
        }
        return new Collection();
    }

    /**
     * updateTask
     * handle the click event on Update button.
     * 
     * @return void
     */
    public function updateTask()
    {
        if ($this->projectsAndTasksRepository->updateTask($this->editingTaskId, ['name' => $this->taskName])) {
            // Task updated successfully
            $this->reset(['taskName', 'editingTaskId']);
        } else {
            // Handle the failure case if needed
            Log::error("Failed to update task with ID {$this->editingTaskId}");
            session()->flash('error.task', 'Failed to update the task. Please try again.');
        }
    }

    /**
     * createTask
     * handle the click event on Add button.
     * 
     * @return void
     */
    public function createTask()
    {
        if (empty($this->taskName)) {
            session()->flash('error.task', 'Task name cannot be empty.');
            return;
        }
        $task = $this->projectsAndTasksRepository->createTask([
            'name' => $this->taskName,
            'project_id' => $this->selectedProjectId
        ]);
        if (!$task) {
            Log::error("Failed to create task in project ID {$this->selectedProjectId}");
            session()->flash('error.task', 'Failed to create the task. Please try again.');
            return;
        }
        $this->reset('taskName');
        $this->render();
    }

    /**
     * editTask 
     * handle the click event on Edit button.
     * 
     * @param int $taskId
     * @return void
     */
    public function editTask(int $taskId)
    {
        if ($this->projectsAndTasksRepository->editTask($taskId, ['name' => $this->taskName])) {
            // Task edited successfully
            $this->reset(['taskName', 'editingTaskId']);
            $this->editingTaskId = $taskId;
            return;
        } else {
            // Handle the failure case if needed
            Log::error("Failed to edit task with ID {$taskId}");
            session()->flash('error.task', 'Failed to edit the task. Please try again.');
            return;
        }
    }

    /**
     * deleteTask 
     * handle the click event on Delete button.
     * 
     * @param int $taskId
     * @return void
     */
    public function deleteTask(int $taskId)
    {
        if ($this->projectsAndTasksRepository->deleteTask($taskId)) {
            // Task deleted successfully
            $this->reset(['taskName', 'editingTaskId']);
            $this->reorderAfterDelete();
            return;
        } else {
            // Handle the failure case if needed
            Log::error("Failed to delete task with ID {$taskId}");
            session()->flash('error.task', 'Failed to delete the task. Please try again.');
            return;
        }
    }

    /**
     * reorderAfterDelete
     * Reorders the task priorities after a task has been deleted to maintain a continuous sequence.
     * @return void
     */
    private function reorderAfterDelete()
    {
        $this->projectsAndTasksRepository->reorderTasks($this->selectedProjectId);
    }

    /**
     * tasksOrderChanged
     * Handles the reordering of tasks based on the new order of task IDs.
     * 
     * @param string $orderedTaskIds
     * @return void
     */
    public function tasksOrderChanged(string $orderedTaskIdsStr): void
    {
        $this->projectsAndTasksRepository->setNewTaksOrder($orderedTaskIdsStr);
    }

    /**
     * createProject
     * 
     * @return void
     */
    public function createProject()
    {
        if (empty($this->newProjectName)) {
            session()->flash('error.project', 'Project name cannot be empty.');
            return;
        }
        $newProject = $this->projectsAndTasksRepository->createProject([
            'name' => $this->newProjectName,
        ]);
        if ($newProject === null) {
            Log::error("Failed to create new project with name {$this->newProjectName}");
            session()->flash('error.project', 'Failed to create the project. Please try again.');
            return;
        }
        $this->projects->push($newProject);
        $this->reset(['taskName', 'editingTaskId', 'newProjectName']);
        $this->selectedProjectId = $newProject->id;
        $this->dispatch('selectNewProject', ['selectedProjectId' => $this->selectedProjectId]);
    }

    /**
     * deleteProject
     * 
     * @param int $projectId
     * 
     * @return void
     */
    public function deleteProject(int $projectId)
    {
        if ($this->projectsAndTasksRepository->deleteProject($projectId) === false) {
            Log::error("Failed to delete project with ID {$projectId}");
            session()->flash('error.project', 'Failed to delete the project. Please try again.');
            return;
        }
        $this->projects = $this->projectsAndTasksRepository->getProjects();
        $this->selectedProjectId = 0;
        $this->reset(['taskName', 'editingTaskId', 'newProjectName']);
    }
    /**
     *  Lifecycle Hook - Called when the component is rendered
     * 
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        return view('livewire.tasks-manager')
            ->layout('components.layouts.app', ['title' => $this->title]);
    }
}
