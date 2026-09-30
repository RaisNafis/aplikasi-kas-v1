<?php
require_once __DIR__ . '/config.php';
$title = 'Menu'; $active = 'menu';
ob_start();
?>
<div class="card reveal" style="padding:8px">
  <a class="tx" href="status.php"><div class="tx-ic" style="background:#EAF3EC">●</div><div><b>Status Sistem</b><small>Diagnosa koneksi + data contoh</small></div><div class="amt">›</div></a>
  <a class="tx" href="anggota.php"><div class="tx-ic" style="background:#E7F2FA">◍</div><div><b>Anggota</b><small>Kelola users</small></div><div class="amt">›</div></a>
  <a class="tx" href="kategori.php"><div class="tx-ic" style="background:#F3EAD9">✦</div><div><b>Kategori</b><small>Masuk / keluar / iuran</small></div><div class="amt">›</div></a>
  <a class="tx" href="laporan.php"><div class="tx-ic" style="background:#EAF3EC">≣</div><div><b>Laporan</b><small>Rekap + cetak</small></div><div class="amt">›</div></a>
  <a class="tx" href="menu.php" onclick="toast('Di HP: menu browser → Add to Home Screen');return false;"><div class="tx-ic" style="background:#FBF0D3">⤓</div><div><b>Install Aplikasi</b><small>PWA — tambah ke layar utama</small></div><div class="amt">›</div></a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
