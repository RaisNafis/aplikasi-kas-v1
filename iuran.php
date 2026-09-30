<?php
require_once __DIR__ . '/config.php';
$title = 'Iuran Anggota'; $active = 'iuran';
ob_start();
?>
<div class="hero-art reveal"><div style="font-size:30px">▤</div><div><b>Iuran <?= e(periodeID()) ?></b><br><small style="color:var(--muted)">Ketuk “Lunas” untuk catat + otomatis masuk kas</small></div>
<button class="btn sm" style="margin-left:auto" onclick="openSheet('s')">+ Tagih</button></div>
<div class="cat-row" style="margin-top:12px">
  <button class="cat on" onclick="setF('semua',this)">Semua</button>
  <button class="cat" onclick="setF('belum',this)">Belum</button>
  <button class="cat" onclick="setF('lunas',this)">Lunas</button>
</div>
<div class="card reveal" id="list" style="margin-top:6px"><div class="empty">Memuat…</div></div>
<div class="sheet" id="s"><div class="bg"></div><div class="box">
  <div class="handle"></div><h3 style="font-family:var(--font-display)">Buat tagihan iuran</h3>
  <label class="lbl">Anggota</label><select class="input" id="f_user"></select>
  <label class="lbl">Periode (YYYY-MM)</label><input class="input" id="f_per" value="<?= date('Y-m') ?>">
  <label class="lbl">Nominal</label><input class="input" id="f_nom" type="number" value="20000">
  <div style="display:flex;gap:10px;margin-top:16px"><button class="btn ghost" onclick="closeSheet('s')">Batal</button><button class="btn" onclick="buat()">Buat</button></div>
</div></div>
<?php
$content = ob_get_clean();
$pageJS = <<<JS
<script>
let DATA=[],F='semua';
async function load(){
  try{
    const [p,u]=await Promise.all([api('list','pembayaran_kas'),api('list','users')]);
    if(!Array.isArray(p)||!Array.isArray(u)) throw new Error('Respon server tidak valid.');
    DATA=p;
    document.getElementById('f_user').innerHTML=u.map(x=>`<option value="\${x.id}">\${esc(x.nama)}</option>`).join('');
    render();
  }catch(e){document.getElementById('list').innerHTML=errBox(e.message)}
}
function setF(v,el){F=v;document.querySelectorAll('.cat').forEach(c=>c.classList.remove('on'));el.classList.add('on');render()}
function render(){
  const f=DATA.filter(x=>F==='semua'||x.status===F);
  document.getElementById('list').innerHTML=f.length?f.slice(0,80).map(x=>
   `<div class="tx"><div class="tx-ic" style="background:\${x.status==='lunas'?'#EAF3EC':'#FBF0D3'}">\${x.status==='lunas'?'✓':'•'}</div>
    <div><b>\${esc(x.users?.nama||'-')}</b><small>\${esc(x.periode)} · \${rp(x.jumlah)} · \${esc(x.metode||'')}</small></div>
    <div style="margin-left:auto;text-align:right"><span class="badge \${x.status==='lunas'?'b-lunas':'b-belum'}">\${x.status}</span><br>
    \${x.status!=='lunas'?`<button class="btn sm" style="margin-top:8px" onclick="lunas('\${x.id}')">Lunas</button>`:''}</div></div>`).join('')
   :'<div class="empty">Tidak ada tagihan pada filter ini.</div>';
}
async function lunas(id){
  try{const r=await fetch('api.php?action=bayar',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});const j=await r.json();if(!r.ok||j.ok===false)throw new Error(j.error||'Gagal');toast('Ditandai lunas + masuk kas');load();}
  catch(e){toast(e.message)}
}
async function buat(){
  const body={user_id:document.getElementById('f_user').value,periode:document.getElementById('f_per').value,jumlah:parseFloat(document.getElementById('f_nom').value),status:'belum'};
  try{await api('create','pembayaran_kas',{method:'POST',body});closeSheet('s');toast('Tagihan dibuat');load();}
  catch(e){toast(e.message)}
}
load();
</script>
JS;
include __DIR__ . '/layout.php';
