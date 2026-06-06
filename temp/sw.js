// Service Worker · Sistema de Temperaturas · Hotel Atankalama
// ------------------------------------------------------------
// Maneja el cache básico para íconos, manifest y recursos estáticos,
// pero evita cachear el formulario principal (index.php).

const CACHE_NAME = 'temp-cache-v2';
const URLS_TO_CACHE = [
    './manifest.json',
    './icon-192.png',
    './icon-512.png'
];

// Instalación inicial del Service Worker
self.addEventListener('install', event => {
    console.log('🧊 Service Worker instalado');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(URLS_TO_CACHE);
        })
    );
});

// Activación y limpieza de versiones antiguas
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        })
    );
    console.log('🧹 Service Worker activo y limpio');
});

// Interceptar solicitudes
self.addEventListener('fetch', event => {
    const reqUrl = event.request.url;

    // 🚫 No cachear la página principal ni el formulario
    if (reqUrl.includes('index.php') || reqUrl.endsWith('/temp/') || reqUrl.endsWith('/temp')) {
        console.log('🟢 Saltando caché para:', reqUrl);
        return event.respondWith(fetch(event.request));
    }

    // ✅ Cachear los demás recursos
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
