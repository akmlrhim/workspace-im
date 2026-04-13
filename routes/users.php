<?php

use App\Http\Middleware\CheckModuleAccess;
use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', CheckModuleAccess::class . ':users'])->prefix('users')->name('users.')->group(function () {
	Route::livewire('/', UserIndex::class)->name('index');
});
