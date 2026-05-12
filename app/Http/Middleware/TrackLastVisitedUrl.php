<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastVisitedUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->is('login', 'register', 'logout', 'two-factor-challenge', 'password/*', 'email/*', 'sw.js', 'storage/*', 'favicon/*', 'build/*')
            && $response->isSuccessful()
            && str_contains($response->headers->get('Content-Type', ''), 'text/html')
        ) {
            session(['last_visited_url' => $request->fullUrl()]);
        }

        return $response;
    }
}
