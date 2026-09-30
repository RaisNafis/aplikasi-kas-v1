<?php
require_once __DIR__ . '/config.php';
$title = 'Kas Keluar'; $active = 'keluar';
ob_start();
?>
<div class="card reveal" style="padding:16px">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
    <div><div style="font-family:var(--font-display);font-size:18px">Pengeluaran</div><small style="color:var(--muted)">Operasional, kegiatan</small></div>
    <button class="btn sm brown" onclick="openSheet('s')">+ Tambah</button>
  </div>
  <div class="search"><input class="input" id="q" placeholder="Cari…" oninput="render()"></div>
  <div id="list"><div class="empty">Memuat…</div></div>
</div>
<div class="sheet" id="s"><div class="bg"></div><div class="box">
  <div class="handle"></div>
  <h3 style="font-family:var(--font-display)">Tambah pengeluaran</h3>
  <label class="lbl">Jumlah (Rp)</label><input class="input" id="f_jumlah" type="number" placeholder="100000">
  <label class="lbl">Kategori</label><select class="input" id="f_kat"></select>
  <label class="lbl">Tanggal</label><input class="input" id="f_tgl" type="date" value="<?= date('Y-m-d') ?>">
  <label class="lbl">Keterangan</label><input class="input" id="f_ket" placeholder="Cth: Beli ATK rapat">
  <label class="lbl">Penerima</label><input class="input" id="f_pen" placeholder="Cth: Toko Barokah">
  <div style="display:flex;gap:10px;margin-top:16px"><button class="btn ghost" onclick="closeSheet('s')">Batal</button><button class="btn" onclick="simpan()">Simpan</button></div>
</div></div>
<?php
$content = ob_get_clean();
$pageJS = <<<JS
<script>
let DATA=[];
async function load(){
  try{
    const [d,k]=await Promise.all([api('list','kas_keluar'),api('list','kategori')]);
    if(!Array.isArray(d)||!Array.isArray(k)) throw new Error('Respon server tidak valid.');
    DATA=d;
    document.getElementById('f_kat').innerHTML=k.filter(x=>x.tipe==='keluar').map(x=>`<option value="\${x.id}">\${esc(x.nama)}</option>`).join('');
    render();
  }catch(e){document.getElementById('list').innerHTML=errBox(e.message)}
}
function render(){
  const q=document.getElementById('q').value.toLowerCase();
  const f=DATA.filter(x=>(x.keterangan||'').toLowerCase().includes(q));
  document.getElementById('list').innerHTML=f.length?f.slice(0,50).map(x=>
    `<div class="tx"><div class="tx-ic" style="background:#FCECEA">↑</div>
     <div><b>\${esc(x.keterangan||'-')}</b><small>\${esc(x.tanggal||'')} · \${esc(x.kategori?.nama||'')} \${x.penerima?'· '+esc(x.penerima):''}</small></div>
     <div class="amt out">− \${rp(x.jumlah)}</div></div>
     <div style="text-align:right;padding:0 12px 10px"><button class="btn sm ghost" onclick="hapus('\${x.id}')">Hapus</button></div>`).join('')
    :'<div class="empty">Belum ada data.</div>';
}
async function simpan(){
  const body={jumlah:parseFloat(document.getElementById('f_jumlah').value),kategori_id:document.getElementById('f_kat').value||null,tanggal:document.getElementById('f_tgl').value,keterangan:document.getElementById('f_ket').value,penerima:document.getElementById('f_pen').value};
  if(!body.jumlah||!body.keterangan){toast('Lengkapi jumlah + keterangan');return}
  try{await api('create','kas_keluar',{method:'POST',body});closeSheet('s');toast('Pengeluaran tersimpan');load();}
  catch(e){toast(e.message)}
}
async function hapus(id){
  if(!confirm('Hapus?'))return;
  try{const r=await fetch('api.php?action=delete&table=kas_keluar&id='+id);const j=await r.json();if(!r.ok||j.ok===false)throw new Error(j.error||'Gagal menghapus');toast('Dihapus');load();}
  catch(e){toast(e.message)}
}
load();
</script>
JS;
include __DIR__ . '/layout.php';
