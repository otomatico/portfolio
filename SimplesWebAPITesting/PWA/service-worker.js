const CACHE_NAME = 'rest-api-client-v3';
const urlsToCache = [
  '/',
  '/index.html',
  '/styles.css',
  '/assets/icon-192x192.png',
  '/assets/icon-512x512.png'
];

// Instalar el Service Worker y cachear los recursos
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Interceptar solicitudes y servir desde la caché
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});