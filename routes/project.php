<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('project-management')->name('project-management.')->group(function () {
	// Spaces (dashboard)
	Route::get('/', App\Livewire\Project\SpaceIndex::class)->name('index');

	// Space detail
	Route::get('/spaces/{space}', App\Livewire\Project\SpaceShow::class)->name('spaces.show');

	// List view
	Route::get('/spaces/{space}/lists/{taskList}', App\Livewire\Project\TaskListShow::class)->name('lists.show');

	// Board / Kanban view
	Route::get('/spaces/{space}/lists/{taskList}/board', App\Livewire\Project\TaskBoard::class)->name('lists.board');

	// Gantt view
	Route::get('/spaces/{space}/lists/{taskList}/gantt', App\Livewire\Project\TaskGantt::class)->name('lists.gantt');

	// Calendar view
	Route::get('/spaces/{space}/lists/{taskList}/calendar', App\Livewire\Project\TaskCalendar::class)->name('lists.calendar');

	// My Tasks
	Route::get('/my-tasks', App\Livewire\Project\MyTasks::class)->name('my-tasks');
});
