<?php
require_once __DIR__ . '/config.php';
$title = 'Kategori'; $active = 'kategori';
ob_start();
?>
<div class="card reveal" style="padding:16px">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
    <div style="font-family:var(--font-display);font-size:18px">Kategori</div>
    <button class="btn sm brown" onclick="simpan()">+ Tambah</button>
  </div>
  <label class="lbl">Nama</label><input class="input" id="f_nama" placeholder="Cth: Konsumsi">
  <label class="lbl">Tipe</label><select class="input" id="f_tipe"><option value="masuk">masuk</option><option value="keluar">keluar</option><option value="iuran">iuran</option></select>
  <div id="list" style="margin-top:12px"><div class="empty">Memuat…</div></div>
</div>
<?php
$content = ob_get_clean();
$pageJS = <<<JS
<script>
async function load(){
  try{
    const d=await api('list','kategori');
    if(!Array.isArray(d)) throw new Error('Respon server tidak valid.');
    const w={masuk:['#EAF3EC','#3E7A52'],keluar:['#FCECEA','#B4544A'],iuran:['#FBF0D3','#96690F']};
    document.getElementById('list').innerHTML=d.map(x=>
     `<div class="tx"><div class="tx-ic" style="background:\${(w[x.tipe]||w.masuk)[0]};color:\${(w[x.tipe]||w.masuk)[1]}">✦</div>
      <div><b>\${esc(x.nama)}</b><small>\${esc(x.tipe)}</small></div>
      <div style="margin-left:auto"><button class="btn sm ghost" onclick="hapus('\${x.id}')">Hapus</button></div></div>`).join('')||'<div class="empty">Belum ada.</div>';
  }catch(e){document.getElementById('list').innerHTML=errBox(e.message)}
}
async function simpan(){
  const nama=document.getElementById('f_nama').value;if(!nama){toast('Isi nama');return}
  try{await api('create','kategori',{method:'POST',body:{nama,tipe:document.getElementById('f_tipe').value}});document.getElementById('f_nama').value='';toast('Kategori ditambah');load();}
  catch(e){toast(e.message)}
}
async function hapus(id){
  if(!confirm('Hapus?'))return;
  try{const r=await fetch('api.php?action=delete&table=kategori&id='+id);const j=await r.json();if(!r.ok||j.ok===false)throw new Error(j.error||'Gagal menghapus');load();}
  catch(e){toast(e.message)}
}
load();
</script>
JS;
include __DIR__ . '/layout.php';
