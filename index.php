<?php
require_once __DIR__ . '/config.php';
$title = 'Beranda'; $active = 'home';
ob_start();
?>
<div class="saldo-card reveal" style="--i:0">
  <div class="lbl">Total Saldo Kas</div>
  <div class="num" id="saldo">Rp …</div>
  <div class="saldo-row">
    <div class="saldo-chip"><span><span class="dot" style="background:#7BC79A"></span>Masuk</span><b id="masuk">…</b></div>
    <div class="saldo-chip"><span><span class="dot" style="background:#E8978A"></span>Keluar</span><b id="keluar">…</b></div>
  </div>
</div>
<div id="saldoErr" style="margin-top:12px"></div>

<div class="grid3 reveal" style="--i:1;margin-top:12px">
  <div class="card stat"><div class="v" id="stAnggota">…</div><div class="k">Anggota aktif</div></div>
  <div class="card stat"><div class="v" id="stTunggak">…</div><div class="k">Belum lunas</div></div>
  <div class="card stat"><div class="v">10</div><div class="k">Jatuh tempo</div></div>
</div>

<div class="sec-t"><h2>Aksi cepat</h2></div>
<div class="quick reveal" style="--i:2">
  <a class="q" href="kas-masuk.php"><div class="ic" style="background:#EAF3EC">↓</div>Kas Masuk</a>
  <a class="q" href="kas-keluar.php"><div class="ic" style="background:#FCECEA">↑</div>Kas Keluar</a>
  <a class="q" href="iuran.php"><div class="ic" style="background:#FBF0D3">▤</div>Tagih Iuran</a>
  <a class="q" href="anggota.php"><div class="ic" style="background:#E7F2FA">◍</div>Tambah Anggota</a>
</div>

<div class="cols2" style="margin-top:6px">
  <div>
    <div class="sec-t"><h2>Transaksi terakhir</h2><a href="laporan.php">Lihat semua</a></div>
    <div class="card reveal" style="--i:3" id="recent"><div class="empty">Memuat…</div></div>
  </div>
  <div>
    <div class="sec-t"><h2>Progress iuran <?= e(date('M Y')) ?></h2><a href="iuran.php">Kelola</a></div>
    <div class="card reveal" style="--i:4;padding:16px" id="progress"><div class="empty">Memuat…</div></div>
  </div>
</div>

<?php
$content = ob_get_clean();
$pageJS = <<<JS
<script>
(async()=>{
  try{
    const s=await api('saldo','-');
    document.getElementById('saldo').textContent=rp(s.saldo);
    document.getElementById('masuk').textContent=rp(s.masuk);
    document.getElementById('keluar').textContent=rp(s.keluar);
    document.getElementById('stAnggota').textContent=s.anggota;
    document.getElementById('stTunggak').textContent=s.tunggakan;
  }catch(e){
    document.getElementById('saldo').textContent='Tidak termuat';
    document.getElementById('saldoErr').innerHTML='<div class="card">'+errBox(e.message)+'</div>';
  }
  try{
    const m=await api('list','kas_masuk');
    const k=await api('list','kas_keluar');
    if(!Array.isArray(m)||!Array.isArray(k)) throw new Error('Respon server tidak valid.');
    const all=[
      ...m.slice(0,4).map(x=>({...x,_t:'masuk'})),
      ...k.slice(0,4).map(x=>({...x,_t:'keluar'}))
    ].sort((a,b)=>new Date(b.tanggal||b.created_at)-new Date(a.tanggal||a.created_at)).slice(0,5);
    document.getElementById('recent').innerHTML=all.length?all.map(x=>
      \`<div class="tx"><div class="tx-ic" style="background:\${x._t==='masuk'?'#EAF3EC':'#FCECEA'}">\${x._t==='masuk'?'↓':'↑'}</div>
      <div><b>\${esc(x.keterangan||x.kategori?.nama||'-')}</b><small>\${esc(x.tanggal||'')} · \${esc(x.kategori?.nama||x.sumber||'')}</small></div>
      <div class="amt \${x._t==='masuk'?'in':'out'}">\${x._t==='masuk'?'+':'−'} \${rp(x.jumlah)}</div></div>\`).join('')
      :'<div class="empty">Belum ada transaksi.<br>Tambahkan lewat menu Kas Masuk / Kas Keluar.</div>';
  }catch(e){document.getElementById('recent').innerHTML=errBox(e.message)}
  try{
    const p=await api('list','pembayaran_kas');
    if(!Array.isArray(p)) throw new Error('Respon server tidak valid.');
    const bl=new Date().toISOString().slice(0,7);
    const cur=p.filter(x=>(x.periode||'').startsWith(bl));
    const lunas=cur.filter(x=>x.status==='lunas').length;
    const pct=cur.length?Math.round(lunas/cur.length*100):0;
    document.getElementById('progress').innerHTML=cur.length?\`
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px"><b>\${lunas}/\${cur.length} sudah bayar</b><span style="color:var(--muted)">\${pct}%</span></div>
      <div style="height:10px;background:#F1ECE2;border-radius:99px;overflow:hidden"><div style="height:100%;width:\${pct}%;background:var(--brand);border-radius:99px"></div></div>\`
      :'<div class="empty">Belum ada tagihan bulan ini.<br><a href="iuran.php" style="color:var(--brand);font-weight:700">Buat tagihan →</a></div>';
  }catch(e){document.getElementById('progress').innerHTML=errBox(e.message)}
})();
</script>
JS;
include __DIR__ . '/layout.php';
