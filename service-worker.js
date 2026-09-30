const CACHE='huang-kas-v2';
const ASSETS=['./','./index.php','./manifest.json','./assets/css/style.css','./assets/js/app.js'];
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(ASSETS)).then(()=>self.skipWaiting()))});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(ks=>Promise.all(ks.filter(k=>k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim()))});
self.addEventListener('fetch',e=>{
  const u=new URL(e.request.url);
  // JANGAN pernah cache: API lokal, Supabase, atau non-GET
  if(e.request.method!=='GET')return;
  if(u.pathname.endsWith('api.php'))return;
  if(u.hostname.includes('supabase.co'))return;
  if(u.origin!==location.origin)return;
  e.respondWith(fetch(e.request).then(r=>{const c=r.clone();caches.open(CACHE).then(x=>x.put(e.request,c));return r}).catch(()=>caches.match(e.request).then(m=>m||caches.match('./index.php'))));
});
