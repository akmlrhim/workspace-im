<?php

use App\Livewire\Project\GeneralTaskboard;
use App\Livewire\Project\MyTasks;
use App\Livewire\Project\SpaceIndex;
use App\Livewire\Project\SpaceShow;
use App\Livewire\Project\TaskBoard;
use App\Livewire\Project\TaskCalendar;
use App\Livewire\Project\TaskGantt;
use App\Livewire\Project\TaskListShow;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('project-management')->name('project-management.')->group(function () {
	Route::redirect('/', '/project-management/general-taskboard');

	Route::get('/spaces', SpaceIndex::class)->name('index');

	Route::get('/spaces/{space}', SpaceShow::class)->name('spaces.show');

	Route::get('/spaces/{space}/lists/{taskList}', TaskListShow::class)->name('lists.show');

	Route::get('/spaces/{space}/lists/{taskList}/board', TaskBoard::class)->name('lists.board');

	Route::get('/spaces/{space}/lists/{taskList}/gantt', TaskGantt::class)->name('lists.gantt');

	Route::get('/spaces/{space}/lists/{taskList}/calendar', TaskCalendar::class)->name('lists.calendar');

	Route::get('/general-taskboard', GeneralTaskboard::class)->name('general-taskboard');

	Route::get('/my-tasks', MyTasks::class)->name('my-tasks');
});
