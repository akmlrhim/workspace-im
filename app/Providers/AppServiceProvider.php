<?php

namespace App\Providers;

use App\Mail\BrevoApiTransport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		$this->configureDefaults();
		$this->configureGates();
		$this->configureMail();
	}

	/**
	 * Register Brevo HTTP API transport for outbound mail.
	 */
	protected function configureMail(): void
	{
		Mail::extend('brevo', fn() => new BrevoApiTransport((string) config('services.brevo.key')));
	}

	/**
	 * Configure authorization gates.
	 */
	protected function configureGates(): void
	{
		Gate::define('manage-users', fn(User $user) => $user->isSuperUser());
	}

	/**
	 * Configure default behaviors for production-ready applications.
	 */
	protected function configureDefaults(): void
	{
		Date::use(CarbonImmutable::class);

		DB::prohibitDestructiveCommands(
			app()->isProduction(),
		);

		if (app()->isProduction()) {
			URL::forceScheme('https');
		}

		Password::defaults(
			fn(): Password => app()->isProduction()
				? Password::min(12)
				->mixedCase()
				->letters()
				->numbers()
				->symbols()
				->uncompromised()
				: Password::min(8),
		);
	}
}
