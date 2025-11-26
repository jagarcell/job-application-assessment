<div wire:key="tasks-manager" class="p-6">
    <h2 class="text-xl font-bold mb-4">Task Manager</h2>

    {{-- Project selector section --}}
    <div class=" flex flex-row">
        <select id="projects-select" wire:model.change="selectedProjectId" class="border p-2 mb-4">
            <option value="0">Select a project</option>
            @foreach ($this->projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>
        @if ($selectedProjectId)
            <button wire:click="deleteProject({{ $selectedProjectId }})"
                class="bg-red-500 text-white px-4 h-auto mb-4 ml-2">Delete Project</button>
        @endif
    </div>
    {{-- Project name input --}}
    <div class="flex flex-row mb-4">
        <input type="text" wire:model="newProjectName" class="border p-2 w-1/3"
            placeholder="To add a project, enter name here">
        <button wire:click="createProject" class="bg-green-500 text-white h-auto p-2 ml-2">Add Project</button>
    </div>
    {{-- Project Error message display --}}
    @if (session()->has('error.project'))
        <div class="bg-red-200 text-red-800 p-2 mb-4 rounded">
            {{ session('error.project') }}
        </div>
    @endif

    {{-- Task management section --}}
    @if ($selectedProjectId)
        <div class="flex gap-2 mb-4">
            <input type="text" wire:model="taskName" class="border p-2 w-full" placeholder="Task name">

            @if ($editingTaskId)
                <button wire:click="updateTask" class="bg-blue-500 text-white px-4">Update</button>
            @else
                <button wire:click="createTask" class="bg-green-500 text-white px-4">Add</button>
            @endif
        </div>
        {{-- Task Error message display --}}
        @if (session()->has('error.task'))
            <div class="bg-red-200 text-red-800 p-2 mb-4 rounded">
                {{ session('error.task') }}
            </div>
        @endif
        <div class="grid grid-cols-4 font-semibold pb-2 border-b">
            <span>Priority</span>
            <span>Task</span>
            <span>Timestamps</span>
            <span class="text-right">Actions</span>
        </div>

        {{-- Task list --}}
        <ul id="tasksList" class="space-y-2 mt-3">
            @foreach ($this->projectTasks as $task)
                <li data-taskid="{{ $task->id }}" class="p-3 bg-gray-100 border rounded cursor-move">
                    <div class="grid grid-cols-4 items-center gap-4">

                        {{-- Priority --}}
                        <span>{{ $task->priority }}.</span>

                        {{-- Task name --}}
                        <span>{{ $task->name }}</span>

                        {{-- Timestamps --}}
                        <span>
                            <div>Created: {{ $task->created_at->format('Y-m-d H:i:s') }}</div>
                            <div>Updated: {{ $task->updated_at->format('Y-m-d H:i:s') }}</div>
                        </span>

                        {{-- Buttons --}}
                        <div class="flex justify-end gap-3">
                            <button wire:click="editTask({{ $task->id }})" class="text-blue-600">Edit</button>
                            <button wire:click="deleteTask({{ $task->id }})" class="text-red-600">Delete</button>
                        </div>

                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initializeSortable();

            Livewire.hook('morph.updated', () => {
                initializeSortable();
            });
            document.addEventListener('selectNewProject', (event) => {
                let projectsSelect = document.getElementById('projects-select');
                projectsSelect.value = event.detail[0].selectedProjectId;
            });
        });

        function initializeSortable() {
            const el = document.getElementById('tasksList');
            // Ensure the element exists and is not already initialized
            if (!el || el.dataset.sortableInitialized) return;

            el.dataset.sortableInitialized = true;

            new Sortable(el, {
                animation: 150,
                onEnd: function() {
                    // A sort change occurred, gather the new order of task IDs
                    const orderedTaskIds = Array.from(el.children).map(li => li.getAttribute('data-taskid'));
                    const orderedTaskIdsString = orderedTaskIds.join(',');
                    // Using string instead of array to avoid serialization issues
                    Livewire.dispatch('tasksOrderChanged', orderedTaskIdsString);
                }
            });
        }
    </script>

    {{-- Sortable Library Documentation: https://sortablejs.github.io/Sortable --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

</div>
