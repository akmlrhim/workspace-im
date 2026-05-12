<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $manifestPath = public_path('build/manifest.json');

        // Derive cache version from Vite build manifest hash so every new
        // deployment automatically invalidates the old service worker cache.
        $cacheVersion = file_exists($manifestPath)
            ? substr(md5_file($manifestPath), 0, 10)
            : 'dev';

        return response()
            ->view('sw', ['cacheVersion' => $cacheVersion, 'isBuilt' => $cacheVersion !== 'dev'])
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
