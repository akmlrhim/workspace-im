<?php

namespace App\Http\Middleware;

use Closure;
use Flux\Flux;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Closure(Request): (Response)  $next
	 */
	public function handle(Request $request, Closure $next, string $moduleKey): Response
	{
		$user = $request->user();

		if (! $user) {
			abort(403);
		}

		$module = config("erp.modules.{$moduleKey}");

		if (! $module) {
			abort(404);
		}

		$requiredRole = $module['required_role'] ?? null;
		$allowedPositions = $module['allowed_positions'] ?? [];

		$hasModuleAccess = ! $user->isGuest() && match (true) {
			$requiredRole !== null => $user->role === $requiredRole,
			empty($allowedPositions) => true,
			$user->isAdmin() => true,
			default => in_array($user->position, $allowedPositions),
		};

		if ($hasModuleAccess) {
			return $next($request);
		}

		$message = 'Anda tidak memiliki izin untuk mengakses modul ' . $module['name'] . '.';

		try {
			Flux::toast($message, variant: 'danger');
		} catch (\Throwable) {
			session()->flash('toast', ['message' => $message, 'variant' => 'danger']);
		}

		return redirect()->route('profile.edit');
	}
}
