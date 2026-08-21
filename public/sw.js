self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open('printing-tracker-v1').then((cache) => cache.addAll([
      '/',
    ])),
  );
});

self.addEventListener('fetch', (e) => {
  // basic network-first fallback
  e.respondWith(
    fetch(e.request).catch(() => caches.match(e.request))
  );
});
