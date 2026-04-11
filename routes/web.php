<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('storage/{path}', function (string $path) {
	$storageBase = realpath(storage_path('app/public'));
	$fullPath = realpath(storage_path('app/public/' . ltrim($path, '/\\')));

	// Block path traversal: resolved path must stay inside storage/app/public
	abort_unless($fullPath && $storageBase && str_starts_with($fullPath, $storageBase . DIRECTORY_SEPARATOR), 403);
	abort_unless(is_file($fullPath), 404);

	return response()->file($fullPath);
})->where('path', '.*');

Route::redirect('/', '/login')->name('home');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/google/link', [GoogleAuthController::class, 'redirectForLink'])->middleware('auth')->name('auth.google.link');
Route::post('/auth/google/unlink', [GoogleAuthController::class, 'unlink'])->middleware('auth')->name('auth.google.unlink');

Route::middleware(['auth', 'verified'])->group(function () {
	Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/project.php';
require __DIR__ . '/hr.php';
require __DIR__ . '/finance.php';
require __DIR__ . '/users.php';
