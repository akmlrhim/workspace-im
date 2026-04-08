<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('hr')->name('hr.')->group(function () {
	Route::view('/', 'hr.dashboard')->name('dashboard');
	// Employees, Payroll, Leaves - coming soon
});
