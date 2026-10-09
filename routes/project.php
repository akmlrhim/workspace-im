<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Livewire\DailyTaskView;
use App\Livewire\GeneralTaskboard;
use App\Livewire\ListNotes;
use App\Livewire\MyTasks;
use App\Livewire\TaskBoard;
use App\Livewire\TaskCalendar;
use App\Livewire\TaskDetailPage;
use App\Livewire\TaskGantt;
use App\Livewire\TaskListShow;
use App\Livewire\WorkloadDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', CheckModuleAccess::class.':project'])->group(function () {
    Route::redirect('/general', '/general-taskboard')->name('index');

    Route::get('/spaces/{space}/lists/{taskList}', TaskListShow::class)->name('lists.show');
    Route::get('/spaces/{space}/lists/{taskList}/board', TaskBoard::class)->name('lists.board');
    Route::get('/spaces/{space}/lists/{taskList}/gantt', TaskGantt::class)->name('lists.gantt');
    Route::get('/spaces/{space}/lists/{taskList}/calendar', TaskCalendar::class)->name('lists.calendar');
    Route::get('/spaces/{space}/lists/{taskList}/daily', DailyTaskView::class)->name('lists.daily');
    Route::get('/spaces/{space}/lists/{taskList}/notes', ListNotes::class)->name('lists.notes');

    Route::get('/general-taskboard', GeneralTaskboard::class)->name('general-taskboard');
    Route::get('/my-tasks', MyTasks::class)->name('my-tasks');
    Route::get('/tasks/{task}', TaskDetailPage::class)->name('tasks.show');
    Route::get('/workload', WorkloadDashboard::class)->name('workload');
});
