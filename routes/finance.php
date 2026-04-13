<?php

use App\Http\Middleware\CheckModuleAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', CheckModuleAccess::class.':finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::view('/', 'finance.dashboard')->name('dashboard');
    // Invoices, Expenses, Reports - coming soon
});
