importScripts("/serviceworker-files.js");

const CACHE_NAME = "siimut-cache-v3";
const RUNTIME_CACHE = "siimut-runtime-v3";

self.addEventListener("install", function (event) {
    console.log('PWA Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            console.log('PWA Cache opened, adding files...');
            return cache.addAll(FILES_TO_CACHE);
        }).then(() => {
            console.log('PWA Files cached successfully');
        }).catch(error => {
            console.error('PWA Cache installation failed:', error);
        })
    );
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    console.log('PWA Service Worker activating...');
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys
                    .filter(function (key) {
                        return key !== CACHE_NAME && key !== RUNTIME_CACHE;
                    })
                    .map(function (key) {
                        console.log('PWA Deleting old cache:', key);
                        return caches.delete(key);
                    })
            );
        }).then(() => {
            console.log('PWA Service Worker activated successfully');
        })
    );
    self.clients.claim();
});

self.addEventListener("fetch", function (event) {
    const url = new URL(event.request.url);

    // Jika bukan GET, jangan di-handle
    if (event.request.method !== "GET") return;

    // Cek apakah ini request Livewire, API, atau dynamic content
    const excludedPaths = [
        "/livewire",
        "/api/",
        "/siimut/login",
        "/siimut/register",
        "/siimut/logout",
        "/broadcasting/",
        "/sanctum/",
        "/telescope/"
    ];

    const isDynamicPath = excludedPaths.some(path =>
        url.pathname.includes(path)
    );

    const isLivewire = event.request.headers.get("X-Livewire") !== null;
    const isAPICall = url.pathname.startsWith('/api/') ||
        event.request.headers.get('Accept')?.includes('application/json');

    // Jangan handle request Livewire/API/Dynamic content
    if (isDynamicPath || isLivewire || isAPICall) {
        return;
    }

    // Static assets - cache first strategy
    if (isStaticAsset(url.pathname)) {
        event.respondWith(
            caches.match(event.request, { ignoreSearch: true }).then(function (cachedResponse) {
                if (cachedResponse) {
                    console.log('PWA Serving from cache:', url.pathname);
                    return cachedResponse;
                }

                console.log('PWA Fetching and caching:', url.pathname);
                return fetch(event.request)
                    .then(function (networkResponse) {
                        // Validate response exists and is successful
                        if (!networkResponse || networkResponse.status !== 200) {
                            console.warn('PWA Non-200 response for:', url.pathname, networkResponse?.status);
                            return networkResponse;
                        }

                        // Ensure response is valid for cloning
                        try {
                            const responseToCache = networkResponse.clone();
                            caches.open(RUNTIME_CACHE).then(function (cache) {
                                // Cache both with and without query params
                                cache.put(event.request, responseToCache.clone());
                                // Also cache a clean version without query params
                                const cleanRequest = new Request(url.origin + url.pathname, {
                                    method: event.request.method,
                                    headers: event.request.headers,
                                    mode: event.request.mode,
                                    credentials: event.request.credentials
                                });
                                cache.put(cleanRequest, responseToCache);
                            }).catch(cacheErr => {
                                console.error('PWA Cache write failed for:', url.pathname, cacheErr);
                            });
                        } catch (cloneError) {
                            console.error('PWA Response clone failed for:', url.pathname, cloneError);
                        }
                        return networkResponse;
                    })
                    .catch(function (error) {
                        console.warn('PWA Network fetch failed for:', url.pathname, error?.message);
                        // Try to return from cache if available
                        return caches.match(event.request, { ignoreSearch: true })
                            .then(cachedResponse => {
                                if (cachedResponse) {
                                    console.log('PWA Fallback to cache for:', url.pathname);
                                    return cachedResponse;
                                }
                                // Asset not in cache, return empty response
                                return new Response('Asset not available offline', {
                                    status: 503,
                                    statusText: 'Service Unavailable'
                                });
                            });
                    });
            })
        );
    }
    // Navigation requests - network first, fallback to offline page
    else if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request)
                .catch(function () {
                    return caches.match("/offline");
                })
        );
    }
});

// Helper function to check if request is for static asset
function isStaticAsset(pathname) {
    const staticExtensions = ['.js', '.css', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.ico'];
    const staticPaths = ['/build/', '/css/', '/js/', '/images/', '/fonts/'];

    return staticExtensions.some(ext => pathname.endsWith(ext)) ||
        staticPaths.some(path => pathname.startsWith(path));
}
