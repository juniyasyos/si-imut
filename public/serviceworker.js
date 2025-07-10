importScripts("/serviceworker-files.js");

const CACHE_NAME = "siimut-cache-v2";

self.addEventListener("install", function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(FILES_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys
                    .filter(function (key) {
                        return key !== CACHE_NAME;
                    })
                    .map(function (key) {
                        return caches.delete(key);
                    })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener("fetch", function (event) {
    const url = new URL(event.request.url);

    // Jika bukan GET, jangan di-handle
    if (event.request.method !== "GET") return;

    // Cek apakah ini request Livewire, API, Filament, atau auth
    const excludedPaths = [
        "/login",
        "/register",
        "/logout",
        "/livewire",
        "/api",
        "/admin",
        "/",
    ];
    const isExcluded = excludedPaths.some(
        (path) => url.pathname === path || url.pathname.startsWith(path + "/")
    );

    const isLivewire = event.request.headers.get("X-Livewire") !== null;

    // Jangan handle request Livewire/API
    if (isExcluded || isLivewire) {
        return;
    }

    event.respondWith(
        caches
            .match(event.request, { ignoreSearch: true })
            .then(function (cachedResponse) {
                if (cachedResponse) {
                    return cachedResponse;
                }

                return fetch(event.request)
                    .then(function (networkResponse) {
                        // Hanya cache response file statis (bukan HTML dokumen)
                        if (
                            networkResponse &&
                            networkResponse.status === 200 &&
                            networkResponse.type === "basic" &&
                            event.request.destination !== "document"
                        ) {
                            const responseToCache = networkResponse.clone();
                            caches.open(CACHE_NAME).then(function (cache) {
                                cache.put(event.request, responseToCache);
                            });
                        }

                        return networkResponse;
                    })
                    .catch(function () {
                        // Kalau user sedang offline dan membuka halaman (navigate)
                        if (event.request.mode === "navigate") {
                            return caches.match("/offline");
                        }
                    });
            })
    );
});
