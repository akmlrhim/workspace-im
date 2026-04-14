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

		$allowedPositions = $module['allowed_positions'] ?? [];

		$hasModuleAccess = ! $user->isGuest() && (
			empty($allowedPositions) ||
			$user->isAdmin() ||
			in_array($user->position, $allowedPositions)
		);

		if ($hasModuleAccess) {
			return $next($request);
		}

		Flux::toast('Anda tidak memiliki izin untuk mengakses modul ' . $module['name'] . '.', variant: 'danger');

		return redirect()->route('dashboard');
	}
}
