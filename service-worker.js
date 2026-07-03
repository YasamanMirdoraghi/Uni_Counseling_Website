
const CACHE_NAME = "counseling-cache-v3";

// فقط فایل‌های استاتیک 
const urlsToCache = [
  // فایل‌های CSS
  "/WebsitePage/assets/css/Style.css",
  "/WebsitePage/assets/css/admin.css",
  
  // فایل‌های JS
  "/WebsitePage/assets/js/theme.js",
  "/WebsitePage/assets/js/index.js",
  "/WebsitePage/assets/js/about.js",
  "/WebsitePage/assets/js/signup.js",
  "/WebsitePage/assets/js/header.js",
  
  // تصاویر
  "/WebsitePage/assets/img/arm.png",
  "/WebsitePage/assets/img/one.png",
  "/WebsitePage/assets/img/two.png",
  "/WebsitePage/assets/img/fg4-min.png",
  "/WebsitePage/assets/img/default.jpg"
];

// install Service Worker
self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log(" کش کردن فایل‌های استاتیک...");
        return cache.addAll(urlsToCache).catch(err => {
          console.warn(" خطا در کش:", err);
        });
      })
  );
  self.skipWaiting();
});


self.addEventListener("fetch", event => {
  const url = event.request.url;
  
  if (!url.startsWith("http")) {
    return;
  }

  if (url.includes(".php")) {
    event.respondWith(fetch(event.request));
    return;
  }

  if (url.includes("/ajax/")) {
    event.respondWith(fetch(event.request));
    return;
  }

  if (url.includes("/api/")) {
    event.respondWith(fetch(event.request));
    return;
  }

  //manage 
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request)
          .then(networkResponse => {
            if (!networkResponse || networkResponse.status !== 200) {
              return networkResponse;
            }
            const cloned = networkResponse.clone();
            caches.open(CACHE_NAME)
              .then(cache => {
                try {
                  cache.put(event.request, cloned);
                } catch (e) {}
              });
            return networkResponse;
          })
          .catch(() => {
            return new Response(" آفلاین", { status: 503 });
          });
      })
  );
});

//clear old cache
self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys()
      .then(keys => {
        return Promise.all(
          keys.map(key => {
            if (key !== CACHE_NAME) {
              console.log(" حذف کش قدیمی:", key);
              return caches.delete(key);
            }
          })
        );
      })
      .then(() => {
        console.log(" Service Worker فعال شد!");
        return self.clients.claim();
      })
  );
});