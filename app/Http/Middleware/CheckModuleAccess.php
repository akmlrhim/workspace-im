<?php

namespace App\Http\Middleware;

use Closure;
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

        if (empty($allowedPositions)) {
            return $next($request);
        }

        if (in_array($user->position, $allowedPositions)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke modul ini.');
    }
}
