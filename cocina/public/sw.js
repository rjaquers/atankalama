// ===========================================
// Service Worker - Cocina Atankalama
// ===========================================

const CACHE_NAME = 'cocina-atankalama-v1';
const urlsToCache = [
    '/cocina/public/index.php?page=cocina/index',
    '/cocina/public/css/bootstrap.min.css',
    '/cocina/public/js/bootstrap.bundle.min.js',
    '/cocina/public/icons/icon-192x192.png',
    '/cocina/public/icons/icon-512x512.png'
];

// Instala el SW y guarda recursos
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
});

// Intercepta peticiones y sirve desde cache si no hay conexión
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => response || fetch(event.request))
    );
});

// Actualiza el SW
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.map(key => {
                if (!cacheWhitelist.includes(key)) return caches.delete(key);
            }))
        )
    );
});
