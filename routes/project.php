<?php

use App\Livewire\Project\MyTasks;
use App\Livewire\Project\SpaceIndex;
use App\Livewire\Project\SpaceShow;
use App\Livewire\Project\TaskBoard;
use App\Livewire\Project\TaskCalendar;
use App\Livewire\Project\TaskGantt;
use App\Livewire\Project\TaskListShow;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('project-management')->name('project-management.')->group(function () {
	// Spaces (dashboard)
	Route::get('/', SpaceIndex::class)->name('index');

	// Space detail
	Route::get('/spaces/{space}', SpaceShow::class)->name('spaces.show');

	// List view
	Route::get('/spaces/{space}/lists/{taskList}', TaskListShow::class)->name('lists.show');

	// Board / Kanban view
	Route::get('/spaces/{space}/lists/{taskList}/board', TaskBoard::class)->name('lists.board');

	// Gantt view
	Route::get('/spaces/{space}/lists/{taskList}/gantt', TaskGantt::class)->name('lists.gantt');

	// Calendar view
	Route::get('/spaces/{space}/lists/{taskList}/calendar', TaskCalendar::class)->name('lists.calendar');

	// My Tasks
	Route::get('/my-tasks', MyTasks::class)->name('my-tasks');
});
