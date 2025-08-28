// service-worker.js
// This file handles caching and offline capabilities for your PWA.

const CACHE_NAME = 'scanit-cache-v1'; // Cache version name
const urlsToCache = [
    '/scanit/login.php', // Adjust this path based on your start_url in manifest.json
    '/scanit/dashboard.php',
    // Add paths to your icons:
    '/scanit/icons/windows11/SmallTile.scale-100.png',
    '/scanit/icons/windows11/SmallTile.scale-125.png',
    '/scanit/icons/windows11/SmallTile.scale-150.png',
    '/scanit/icons/windows11/SmallTile.scale-200.png',
    '/scanit/icons/windows11/SmallTile.scale-400.png',
    '/scanit/icons/windows11/Square150x150Logo.scale-100.png',
    '/scanit/icons/windows11/Square150x150Logo.scale-125.png',
    '/scanit/icons/windows11/Square150x150Logo.scale-150.png',
    '/scanit/icons/windows11/Square150x150Logo.scale-200.png',
    '/scanit/icons/windows11/Square150x150Logo.scale-400.png',
    '/scanit/icons/windows11/Wide310x150Logo.scale-100.png',
    '/scanit/icons/windows11/Wide310x150Logo.scale-125.png',
    '/scanit/icons/windows11/Wide310x150Logo.scale-150.png',
    '/scanit/icons/windows11/Wide310x150Logo.scale-200.png',
    '/scanit/icons/windows11/Wide310x150Logo.scale-400.png',
    '/scanit/icons/windows11/LargeTile.scale-100.png',
    '/scanit/icons/windows11/LargeTile.scale-125.png',
    '/scanit/icons/windows11/LargeTile.scale-150.png',
    '/scanit/icons/windows11/LargeTile.scale-200.png',
    '/scanit/icons/windows11/LargeTile.scale-400.png',
    '/scanit/icons/windows11/Square44x44Logo.scale-100.png',
    '/scanit/icons/windows11/Square44x44Logo.scale-125.png',
    '/scanit/icons/windows11/Square44x44Logo.scale-150.png',
    '/scanit/icons/windows11/Square44x44Logo.scale-200.png',
    '/scanit/icons/windows11/Square44x44Logo.scale-400.png',
    '/scanit/icons/windows11/StoreLogo.scale-100.png',
    '/scanit/icons/windows11/StoreLogo.scale-125.png',
    '/scanit/icons/windows11/StoreLogo.scale-150.png',
    '/scanit/icons/windows11/StoreLogo.scale-200.png',
    '/scanit/icons/windows11/StoreLogo.scale-400.png',
    '/scanit/icons/windows11/SplashScreen.scale-100.png',
    '/scanit/icons/windows11/SplashScreen.scale-125.png',
    '/scanit/icons/windows11/SplashScreen.scale-150.png',
    '/scanit/icons/windows11/SplashScreen.scale-200.png',
    '/scanit/icons/windows11/SplashScreen.scale-400.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-16.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-20.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-24.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-30.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-32.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-36.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-40.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-44.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-48.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-60.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-64.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-72.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-80.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-96.png',
    '/scanit/icons/windows11/Square44x44Logo.targetsize-256.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-16.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-20.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-24.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-30.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-32.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-36.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-40.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-44.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-48.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-60.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-64.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-72.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-80.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-96.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-unplated_targetsize-256.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-16.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-20.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-24.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-30.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-32.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-36.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-40.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-44.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-48.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-60.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-64.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-72.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-80.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-96.png',
    '/scanit/icons/windows11/Square44x44Logo.altform-lightunplated_targetsize-256.png',
    '/scanit/icons/android/android-launchericon-512-512.png',
    '/scanit/icons/android/android-launchericon-192-192.png',
    '/scanit/icons/android/android-launchericon-144-144.png',
    '/scanit/icons/android/android-launchericon-96-96.png',
    '/scanit/icons/android/android-launchericon-72-72.png',
    '/scanit/icons/android/android-launchericon-48-48.png',
    '/scanit/icons/ios/16.png',
    '/scanit/icons/ios/20.png',
    '/scanit/icons/ios/29.png',
    '/scanit/icons/ios/32.png',
    '/scanit/icons/ios/40.png',
    '/scanit/icons/ios/50.png',
    '/scanit/icons/ios/57.png',
    '/scanit/icons/ios/58.png',
    '/scanit/icons/ios/60.png',
    '/scanit/icons/ios/64.png',
    '/scanit/icons/ios/72.png',
    '/scanit/icons/ios/76.png',
    '/scanit/icons/ios/80.png',
    '/scanit/icons/ios/87.png',
    '/scanit/icons/ios/100.png',
    '/scanit/icons/ios/114.png',
    '/scanit/icons/ios/120.png',
    '/scanit/icons/ios/128.png',
    '/scanit/icons/ios/144.png',
    '/scanit/icons/ios/152.png',
    '/scanit/icons/ios/167.png',
    '/scanit/icons/ios/180.png',
    '/scanit/icons/ios/192.png',
    '/scanit/icons/ios/256.png',
    '/scanit/icons/ios/512.png',
    '/scanit/icons/ios/1024.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js'
];

// Install event: Caches static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch event: Serves cached content first, then fetches from network
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Cache hit - return response
                if (response) {
                    return response;
                }
                // No cache hit - fetch from network
                return fetch(event.request);
            })
    );
});

// Activate event: Cleans up old caches
self.addEventListener('activate', (event) => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        // Delete old caches
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
