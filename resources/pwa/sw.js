/**
 * Service Worker — PWA static asset caching ONLY.
 *
 * CRITICAL CONSTRAINT: This service worker caches static assets ONLY.
 * It NEVER caches API responses or dynamic data.
 * Network-first strategy for API calls; cache-first for static assets.
 */

const CACHE_NAME = 'legal-ws-static-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
];

// File extensions considered static assets
const STATIC_EXTENSIONS = [
    '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg',
    '.woff', '.woff2', '.ttf', '.eot', '.ico',
];

/**
 * Install — pre-cache known static assets.
 */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

/**
 * Activate — clean up old caches.
 */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

/**
 * Determine if a URL points to a static asset.
 */
function isStaticAsset(url) {
    const pathname = new URL(url).pathname;
    return STATIC_EXTENSIONS.some((ext) => pathname.endsWith(ext));
}

/**
 * Determine if a URL is an API call (should NEVER be cached).
 */
function isApiCall(url) {
    const pathname = new URL(url).pathname;
    return pathname.startsWith('/api/') ||
           pathname.startsWith('/livewire/') ||
           pathname.startsWith('/sanctum/');
}

/**
 * Fetch — route strategy based on request type.
 *
 * Static assets: cache-first, fallback to network.
 * API/dynamic: network-only, NEVER cache.
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Skip non-GET requests entirely
    if (request.method !== 'GET') {
        return;
    }

    // API calls — ALWAYS network, never cache
    if (isApiCall(request.url)) {
        event.respondWith(fetch(request));
        return;
    }

    // Static assets — cache-first
    if (isStaticAsset(request.url)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    // Update cache in background
                    fetch(request).then((response) => {
                        if (response && response.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, response);
                            });
                        }
                    }).catch(() => {});
                    return cached;
                }
                return fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, clone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Everything else — network-first, no caching
    event.respondWith(fetch(request));
});
