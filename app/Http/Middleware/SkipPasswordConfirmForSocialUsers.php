<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkipPasswordConfirmForSocialUsers
{
    /**
     * Handle an incoming request.
     *
     * For users who signed in via Google (no password), automatically mark
     * the password as confirmed so they are not blocked by password.confirm middleware.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && is_null($request->user()->password)) {
            $request->session()->put('auth.password_confirmed_at', now()->unix());
        }

        return $next($request);
    }
}
