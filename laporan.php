<?php
require_once __DIR__ . '/config.php';
$title = 'Laporan'; $active = 'laporan';
ob_start();
?>
<div class="card reveal" style="padding:16px">
  <div style="font-family:var(--font-display);font-size:18px">Laporan Arus Kas</div>
  <small style="color:var(--muted)">Ringkasan + tabel gabungan, siap print</small>
  <div class="grid2" style="margin-top:12px">
    <div class="card stat" style="background:#EAF3EC;border-color:#D7E8DB"><div class="v in" id="tM">…</div><div class="k">Total masuk</div></div>
    <div class="card stat" style="background:#FCECEA;border-color:#F3D5D1"><div class="v out" id="tK">…</div><div class="k">Total keluar</div></div>
  </div>
  <div class="card stat" style="margin-top:12px;background:#1C1917;color:#FBF7EF;border:none"><div class="v" id="tS">…</div><div class="k" style="color:#CBBFAF">Saldo akhir</div></div>
  <button class="btn" style="margin-top:12px" onclick="window.print()">Cetak / Simpan PDF</button>
  <div id="list" style="margin-top:12px"><div class="empty">Memuat…</div></div>
</div>
<?php
$content = ob_get_clean();
$pageJS = <<<JS
<script>
(async()=>{
  try{
    const m=await api('list','kas_masuk');
    const k=await api('list','kas_keluar');
    if(!Array.isArray(m)||!Array.isArray(k)) throw new Error('Respon server tidak valid.');
    const tm=m.reduce((a,x)=>a+Number(x.jumlah||0),0);
    const tk=k.reduce((a,x)=>a+Number(x.jumlah||0),0);
    document.getElementById('tM').textContent=rp(tm);
    document.getElementById('tK').textContent=rp(tk);
    document.getElementById('tS').textContent=rp(tm-tk);
    const all=[...m.map(x=>({...x,_t:'masuk'})),...k.map(x=>({...x,_t:'keluar'}))]
      .sort((a,b)=>new Date(b.tanggal)-new Date(a.tanggal));
    document.getElementById('list').innerHTML=all.length?`<div class="tbl-wrap"><table class="t"><tr><th>Tanggal</th><th>Keterangan</th><th style="text-align:right">Nominal</th></tr>`+
      all.map(x=>`<tr><td style="white-space:nowrap">\${esc(x.tanggal||'')}</td><td>\${esc(x.keterangan||'-')}<br><small style="color:var(--muted)">\${x._t}</small></td><td style="text-align:right;font-weight:700;white-space:nowrap;color:\${x._t==='masuk'?'var(--green)':'var(--red)'}">\${x._t==='masuk'?'+':'−'} \${rp(x.jumlah)}</td></tr>`).join('')+`</table></div>`
      :'<div class="empty">Belum ada transaksi.</div>';
  }catch(e){document.getElementById('list').innerHTML=errBox(e.message)}
})();
</script>
JS;
include __DIR__ . '/layout.php';
