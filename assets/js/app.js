function toast(m){const t=document.getElementById('toast');t.textContent=m;t.classList.add('show');clearTimeout(t._x);t._x=setTimeout(()=>t.classList.remove('show'),3200)}
function rp(n){return 'Rp '+Number(n||0).toLocaleString('id-ID')}
async function api(action,table,opts={}){
  const q=new URLSearchParams({action,table,...(opts.params||{})});
  let res;
  try{res=await fetch('api.php?'+q.toString(),{method:opts.method||'GET',headers:{'Content-Type':'application/json'},body:opts.body?JSON.stringify(opts.body):undefined});}
  catch(e){throw new Error('Server PHP tidak terjangkau. Pastikan php -S localhost:8000 berjalan.')}
  let j=null;try{j=await res.json();}catch(e){throw new Error('Respon server rusak (HTTP '+res.status+').')}
  if(!res.ok||(j&&j.ok===false))throw new Error((j&&j.error)||('Gagal memuat (HTTP '+res.status+')'));
  return j;
}
function errBox(msg){
  return `<div class="empty"><b style="color:var(--red)">Tidak bisa memuat data</b><br>`
    +`<span style="font-size:12.5px">${esc(msg)}</span><br><br>`
    +`<a class="btn sm brown" href="status.php">Buka Status Sistem</a></div>`;
}
function openSheet(id){document.getElementById(id)?.classList.add('open')}
function closeSheet(id){document.getElementById(id)?.classList.remove('open')}
document.addEventListener('click',e=>{if(e.target.classList?.contains('bg'))e.target.closest('.sheet')?.classList.remove('open')});
function esc(s){return (s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
