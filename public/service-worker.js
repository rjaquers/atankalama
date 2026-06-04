const CACHE_NAME = "rkm-cache-v6-1";

const urlsToCache = [
  "./",
  "./assets/css/style.css",
  "./assets/js/app.js",
  "./assets/js/offline-sync.js",
  "./assets/js/qr-scanner.js"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache)).catch(()=>{})
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
    ))
  );
});

self.addEventListener("fetch", (event) => {
  const req = event.request;

  // Network-first para HTML
  if (req.headers.get("accept") && req.headers.get("accept").includes("text/html")) {
    event.respondWith(
      fetch(req).then(res => {
        const copy = res.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(req, copy));
        return res;
      }).catch(() => caches.match(req))
    );
    return;
  }

  // Cache-first para assets
  event.respondWith(
    caches.match(req).then((cached) => cached || fetch(req))
  );
});
