<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('finance')->name('finance.')->group(function () {
    Route::view('/', 'finance.dashboard')->name('dashboard');
    // Invoices, Expenses, Reports - coming soon
});
