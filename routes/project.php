<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Livewire\Project\DailyTaskView;
use App\Livewire\Project\GeneralTaskboard;
use App\Livewire\Project\ListNotes;
use App\Livewire\Project\MyTasks;
use App\Livewire\Project\TaskBoard;
use App\Livewire\Project\TaskCalendar;
use App\Livewire\Project\TaskGantt;
use App\Livewire\Project\TaskListShow;
use App\Livewire\Project\WorkloadDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', CheckModuleAccess::class.':project'])->name('project-management.')->group(function () {
    Route::redirect('/general', '/general-taskboard')->name('index');

    Route::get('/spaces/{space}/lists/{taskList}', TaskListShow::class)->name('lists.show');
    Route::get('/spaces/{space}/lists/{taskList}/board', TaskBoard::class)->name('lists.board');
    Route::get('/spaces/{space}/lists/{taskList}/gantt', TaskGantt::class)->name('lists.gantt');
    Route::get('/spaces/{space}/lists/{taskList}/calendar', TaskCalendar::class)->name('lists.calendar');
    Route::get('/spaces/{space}/lists/{taskList}/daily', DailyTaskView::class)->name('lists.daily');
    Route::get('/spaces/{space}/lists/{taskList}/notes', ListNotes::class)->name('lists.notes');

    Route::get('/general-taskboard', GeneralTaskboard::class)->name('general-taskboard');
    Route::get('/my-tasks', MyTasks::class)->name('my-tasks');
    Route::get('/workload', WorkloadDashboard::class)->name('workload');
});
