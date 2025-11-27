<?php

use Livewire\Livewire;
use App\Livewire\TasksManager;

it('tests if Livewire task manager component exists in the home page', function () {
    /** @var \Tests\TestCase $this */
    $this->get('/')->assertSeeLivewire(TasksManager::class);
});

it('tests if Livewire TasksManager component can render', function () {
    Livewire::test(TasksManager::class)
        ->assertStatus(200);
});

it('tests if tasksList is available in the component\'s blade', function () {
    Livewire::test(TasksManager::class)
        ->assertSeeHtml('id="tasksList"');
});