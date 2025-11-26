<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\TasksManager;


Route::get('/', TasksManager::class)->name('tasks.manager');
