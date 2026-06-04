const CACHE_NAME = 'atankalama-inventario-v1';
const urlsToCache = [
    '/inventario/',
    '/inventario/index.php',
    '/inventario/assets/icons/icon-192.png',
    '/inventario/assets/icons/icon-512.png',
    '/inventario/assets/css/',
    '/inventario/assets/js/'
];

// Instalar y cachear recursos base
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache);
        })
    );
});

// Activar y limpiar versiones antiguas
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        })
    );
});

// Interceptar peticiones y servir desde caché o red
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request).catch(() => {
                // Puedes agregar aquí una página offline personalizada si quieres
                return new Response('Sin conexión. Intenta nuevamente.', { status: 503 });
            });
        })
    );
});
