<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\RoleManager;
use App\Livewire\Settings\RolePermissionManager;
use App\Livewire\Settings\Security;
use App\Livewire\Settings\UserRoleManager;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth'])->group(function () {
	Route::redirect('settings', 'settings/profile');

	Route::livewire('settings/profile', Profile::class)->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
	Route::livewire('settings/appearance', Appearance::class)->name('appearance.edit');

	Route::livewire('settings/security', Security::class)
		->middleware(
			when(
				Features::canManageTwoFactorAuthentication()
					&& Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
				['password.confirm'],
				[],
			),
		)
		->name('security.edit');

	// Roles & Permissions Management
	Route::livewire('settings/roles', RoleManager::class)->name('settings.roles');
	Route::livewire('settings/roles/{role}/permissions', RolePermissionManager::class)->name('settings.roles.permissions');
	Route::livewire('settings/users/roles', UserRoleManager::class)->name('settings.users.roles');
});

