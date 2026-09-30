<?php
require_once __DIR__ . '/config.php';
$title = 'Status Sistem'; $active = 'status';
ob_start();
?>
<div class="card reveal" style="padding:16px">
  <div style="font-family:var(--font-display);font-size:18px">Status Koneksi</div>
  <small style="color:var(--muted)">Cek langsung tiap tabel Supabase</small>
  <div id="tblStatus" style="margin-top:12px"><div class="empty">Memeriksa…</div></div>
  <button class="btn sm ghost" style="margin-top:8px" onclick="check()">Periksa ulang</button>
</div>

<div class="card reveal" style="padding:16px;margin-top:12px">
  <div style="font-family:var(--font-display);font-size:18px">Cara memperbaiki</div>
  <ol class="steps">
    <li>Buka <a href="https://supabase.com/dashboard/project/qetmmcdnydswfmiywfyt/sql" target="_blank" rel="noopener" style="color:var(--brand);font-weight:700">SQL Editor</a> project Supabase kamu.</li>
    <li>New Query → copy seluruh isi file <span class="kbd">supabase_schema.sql</span> → Run.</li>
    <li>Kembali ke sini → Periksa ulang. Semua tabel harus OK.</li>
  </ol>
</div>

<div class="card reveal" style="padding:16px;margin-top:12px">
  <div style="font-family:var(--font-display);font-size:18px">Data contoh</div>
  <small style="color:var(--muted)">Buat 3 anggota + tagihan bulan ini + contoh kas masuk/keluar.</small>
  <button class="btn brown" style="margin-top:12px" onclick="seed()">Buat data contoh</button>
</div>

<div class="card reveal" style="padding:16px;margin-top:12px">
  <b>Info proyek</b>
  <div style="font-size:12.5px;color:var(--muted);margin-top:6px;word-break:break-all">
    URL: <?= e(SUPABASE_REST) ?><br>Key: <?= e(substr(SUPABASE_KEY,0,18)) ?>…
  </div>
</div>
<?php
$content = ob_get_clean();
$pageJS = <<<JS
<script>
async function check(){
  const box=document.getElementById('tblStatus');
  box.innerHTML='<div class="empty">Memeriksa…</div>';
  try{
    const r=await api('ping','-');
    box.innerHTML='<div class="tbl-wrap"><table class="t"><tr><th>Tabel</th><th>Status</th><th>Keterangan</th></tr>'+
      Object.entries(r.tables).map(([t,v])=>`<tr><td><span class="kbd">\${t}</span></td><td><span class="badge \${v.ok?'b-lunas':'b-belum'}">\${v.ok?'OK':'Gagal'}</span></td><td style="font-size:12.5px">\${esc(v.msg)} (HTTP \${v.code})</td></tr>`).join('')+'</table></div>';
  }catch(e){box.innerHTML=errBox(e.message)}
}
async function seed(){
  if(!confirm('Buat data contoh?'))return;
  try{const r=await api('seed','-',{method:'POST',body:{}});toast(r.pesan||'Data contoh dibuat');check();}
  catch(e){toast(e.message)}
}
check();
</script>
JS;
include __DIR__ . '/layout.php';
