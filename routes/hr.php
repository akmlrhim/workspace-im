<?php

use App\Http\Middleware\CheckModuleAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', CheckModuleAccess::class . ':hr'])->prefix('hr')->name('hr.')->group(function () {
	Route::view('/', 'hr.dashboard')->name('dashboard');
});
