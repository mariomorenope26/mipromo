const STATIC_CACHE = 'mipromo-static-v1';
const RUNTIME_CACHE = 'mipromo-runtime-v1';

const PRECACHE_URLS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/apple-touch-icon.png',
    '/images/logo_jmsperu.png',
];

// Rutas dinámicas / autenticadas que jamás deben servirse cacheadas ni alterarse
const BYPASS_PREFIXES = [
    '/admin',
    '/mi-cuenta',
    '/checkout',
    '/carrito',
    '/login',
    '/registro',
    '/logout',
    '/manual',
    '/pedido',
    '/api/',
    '/install.php',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys
                .filter((key) => key !== STATIC_CACHE && key !== RUNTIME_CACHE)
                .map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    const pathname = url.pathname;
    if (BYPASS_PREFIXES.some((prefix) => pathname.startsWith(prefix))) {
        if (request.mode === 'navigate') {
            event.respondWith(
                fetch(request).catch(() => caches.match('/offline.html'))
            );
        }
        return;
    }

    // Assets compilados Vite, iconos e imágenes de catálogo: Cache-first con actualización en segundo plano
    if (pathname.startsWith('/build/') || pathname.startsWith('/icons/') || pathname.startsWith('/images/')) {
        event.respondWith(
            caches.open(STATIC_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                const networkPromise = fetch(request).then((response) => {
                    if (response && response.ok && response.type === 'basic') {
                        cache.put(request, response.clone());
                    }
                    return response;
                }).catch(() => null);

                if (cached) {
                    return cached;
                }

                const networkResponse = await networkPromise;
                return networkResponse || Response.error();
            })
        );
        return;
    }

    // Navegación pública (tienda, colegios, campañas): Network-first con respaldo offline
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response && response.ok && response.type === 'basic') {
                        const clone = response.clone();
                        caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(async () => {
                    const cachedPage = await caches.match(request);
                    return cachedPage || caches.match('/offline.html');
                })
        );
    }
});
