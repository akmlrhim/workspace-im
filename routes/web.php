<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/google/link', [GoogleAuthController::class, 'redirectForLink'])->middleware('auth')->name('auth.google.link');
Route::post('/auth/google/unlink', [GoogleAuthController::class, 'unlink'])->middleware('auth')->name('auth.google.unlink');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/project.php';
require __DIR__.'/hr.php';
require __DIR__.'/finance.php';
