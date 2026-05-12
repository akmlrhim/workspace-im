<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ServiceWorkerController;
use Illuminate\Support\Facades\Route;

Route::get('/sw.js', ServiceWorkerController::class)->name('sw');

Route::get('storage/{path}', function (string $path) {
    $storageBase = realpath(storage_path('app/public'));
    $fullPath = realpath(storage_path('app/public/'.ltrim($path, '/\\')));

    abort_unless($fullPath && $storageBase && str_starts_with($fullPath, $storageBase.DIRECTORY_SEPARATOR), 403);
    abort_unless(is_file($fullPath), 404);

    return response()->file($fullPath);
})->where('path', '.*');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('project-management.general-taskboard')
        : redirect()->route('login');
})->name('home');

Route::redirect('/project-management/{any?}', '/general-taskboard', 301)->where('any', '.*');

Route::redirect('/dashboard', '/general-taskboard', 301)->name('dashboard');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:20,1')->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:20,1')->name('auth.google.callback');
Route::get('/auth/google/link', [GoogleAuthController::class, 'redirectForLink'])->middleware(['auth', 'throttle:10,1'])->name('auth.google.link');
Route::post('/auth/google/unlink', [GoogleAuthController::class, 'unlink'])->middleware(['auth', 'throttle:10,1'])->name('auth.google.unlink');

require __DIR__.'/settings.php';
require __DIR__.'/project.php';
require __DIR__.'/users.php';
