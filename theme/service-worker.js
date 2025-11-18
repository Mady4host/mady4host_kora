self.addEventListener('install', function(event){
  console.log('Kora service worker installed');
});

self.addEventListener('fetch', function(event){
  // basic offline fallback - placeholder
});
