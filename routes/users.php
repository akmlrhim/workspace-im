<?php

use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-users'])->prefix('users')->name('users.')->group(function () {
    Route::livewire('/', UserIndex::class)->name('index');
});
