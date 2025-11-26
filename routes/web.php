<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\TasksManager;

Route::get('/', function () {
    return view('welcome');
})->name('home');
