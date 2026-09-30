<?php
require_once __DIR__ . '/config.php';
$title = 'Anggota'; $active = 'anggota';
ob_start();
?>
<div class="card reveal" style="padding:16px">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
    <div style="font-family:var(--font-display);font-size:18px">Anggota</div>
    <button class="btn sm brown" onclick="openSheet('s')">+ Tambah</button>
  </div>
  <div class="search"><input class="input" id="q" placeholder="Cari nama…" oninput="render()"></div>
  <div id="list"><div class="empty">Memuat…</div></div>
</div>
<div class="sheet" id="s"><div class="bg"></div><div class="box">
  <div class="handle"></div><h3 style="font-family:var(--font-display)">Anggota baru</h3>
  <label class="lbl">Nama</label><input class="input" id="f_nama" placeholder="Cth: Budi Santoso">
  <label class="lbl">No WA</label><input class="input" id="f_wa" placeholder="62812…">
  <label class="lbl">Role</label><select class="input" id="f_role"><option value="anggota">anggota</option><option value="bendahara">bendahara</option><option value="admin">admin</option></select>
  <div style="display:flex;gap:10px;margin-top:16px"><button class="btn ghost" onclick="closeSheet('s')">Batal</button><button class="btn" onclick="simpan()">Simpan</button></div>
</div></div>
<?php
$content = ob_get_clean();
$pageJS = <<<JS
<script>
let DATA=[];
async function load(){
  try{
    const d=await api('list','users');
    if(!Array.isArray(d)) throw new Error('Respon server tidak valid.');
    DATA=d;render();
  }catch(e){document.getElementById('list').innerHTML=errBox(e.message)}
}
function render(){
  const q=document.getElementById('q').value.toLowerCase();
  const f=DATA.filter(x=>(x.nama||'').toLowerCase().includes(q));
  document.getElementById('list').innerHTML=f.map(x=>
   `<div class="tx"><div class="avatar" style="margin:0">\${esc((x.nama||'?')[0].toUpperCase())}</div>
    <div><b>\${esc(x.nama)}</b><small>\${esc(x.role||'')} · \${esc(x.status||'')} \${x.no_wa?'· '+esc(x.no_wa):''}</small></div>
    <div style="margin-left:auto"><button class="btn sm ghost" onclick="hapus('\${x.id}')">Hapus</button></div></div>`).join('')||'<div class="empty">Belum ada anggota.</div>';
}
async function simpan(){
  const body={nama:document.getElementById('f_nama').value,no_wa:document.getElementById('f_wa').value,role:document.getElementById('f_role').value,status:'aktif'};
  if(!body.nama){toast('Isi nama');return}
  try{await api('create','users',{method:'POST',body});closeSheet('s');toast('Anggota ditambah');load();}
  catch(e){toast(e.message)}
}
async function hapus(id){
  if(!confirm('Hapus anggota + seluruh iurannya?'))return;
  try{const r=await fetch('api.php?action=delete&table=users&id='+id);const j=await r.json();if(!r.ok||j.ok===false)throw new Error(j.error||'Gagal menghapus');toast('Dihapus');load();}
  catch(e){toast(e.message)}
}
load();
</script>
JS;
include __DIR__ . '/layout.php';
