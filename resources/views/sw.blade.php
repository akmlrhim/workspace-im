@php
// This file is served as application/javascript — no HTML output.
$cacheKey = 'erp-im-' . $cacheVersion;
@endphp
const CACHE_NAME = {!! json_encode($cacheKey) !!};

// --- Install: activate immediately without waiting for existing tabs to close ---
self.addEventListener('install', () => {
    self.skipWaiting();
});

// --- Activate: purge all stale caches from previous deployments ---
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

@if ($isBuilt)
// --- Fetch: only cache static assets; never intercept app pages or API calls ---
self.addEventListener('fetch', event => {
    const { request } = event;

    // Skip non-GET, non-same-origin, and cross-origin except Google Fonts
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Never cache Livewire AJAX, API, Sanctum, or debug routes
    const skip = ['/livewire/', '/api/', '/sanctum/', '/_debugbar/', '/telescope/'];
    if (skip.some(prefix => url.pathname.startsWith(prefix))) return;

    // Vite build assets — content-hashed, safe to cache indefinitely
    if (url.origin === location.origin && url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request, { maxEntries: 60 }));
        return;
    }

    // Favicons & PWA icons — rarely change
    if (url.origin === location.origin && url.pathname.startsWith('/favicon/')) {
        event.respondWith(cacheFirst(request, { maxEntries: 20 }));
        return;
    }

    // Google Fonts binary files (gstatic) — immutable once fetched
    if (url.origin === 'https://fonts.gstatic.com') {
        event.respondWith(cacheFirst(request, { maxEntries: 20 }));
        return;
    }

    // Google Fonts CSS — stale-while-revalidate so updates propagate
    if (url.origin === 'https://fonts.googleapis.com') {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // All other same-origin requests (HTML pages, etc.) — network only.
    // Keeps the app always fresh; pages are not served stale.
});

// --- Cache-first with LRU eviction ---
async function cacheFirst(request, { maxEntries = 50 } = {}) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    if (response.ok) {
        await cache.put(request, response.clone());
        await evictIfNeeded(cache, maxEntries);
    }
    return response;
}

// --- Stale-while-revalidate ---
async function staleWhileRevalidate(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    const networkFetch = fetch(request).then(response => {
        if (response.ok) cache.put(request, response.clone());
        return response;
    }).catch(() => null);

    return cached ?? await networkFetch;
}

// --- Evict oldest entries when cache exceeds maxEntries ---
async function evictIfNeeded(cache, maxEntries) {
    const keys = await cache.keys();
    if (keys.length > maxEntries) {
        // Delete oldest entries (FIFO based on insertion order)
        const toDelete = keys.slice(0, keys.length - maxEntries);
        await Promise.all(toDelete.map(k => cache.delete(k)));
    }
}
@else
// Development mode: SW registered but caching disabled.
// Assets are served by the Vite dev server and must never be cached.
self.addEventListener('fetch', () => {});
@endif
