<?php
// $title, $active harus di-set sebelum include
if (!isset($title)) $title = 'Huang Kas';
if (!isset($active)) $active = 'home';
$nav = [
  'home'     => ['Beranda', 'index.php', '◉'],
  'masuk'    => ['Kas Masuk', 'kas-masuk.php', '↓'],
  'keluar'   => ['Kas Keluar', 'kas-keluar.php', '↑'],
  'iuran'    => ['Iuran', 'iuran.php', '▤'],
  'anggota'  => ['Anggota', 'anggota.php', '◍'],
  'kategori' => ['Kategori', 'kategori.php', '✦'],
  'laporan'  => ['Laporan', 'laporan.php', '≣'],
  'status'   => ['Status', 'status.php', '●'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?> — Huang Kas</title>
<meta name="theme-color" content="#1C1917">
<meta name="description" content="Huang Kas — aplikasi uang kas RT, kos, komunitas. Catat iuran, pemasukan, pengeluaran.">
<link rel="manifest" href="manifest.json">
<link rel="apple-touch-icon" href="icons/icon-192.png">
<link rel="icon" href="icons/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="side-brand"><div class="side-logo">H</div><div><b>Huang Kas</b><small>Uang kas</small></div></div>
    <nav class="side-nav">
      <?php foreach ($nav as $k => $n): ?>
        <a class="<?= $active===$k?'on':'' ?>" href="<?= $n[1] ?>"><span><?= $n[2] ?></span><?= $n[0] ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot"><b>Install aplikasi</b><small>Di HP: browser → Add to Home Screen untuk mode fullscreen.</small></div>
  </aside>
  <div class="main">
    <div class="topbar"><h1>Huang Kas</h1></div>
    <div class="wrap">
<?= $content ?? '' ?>
    </div>
    <nav class="bottomnav">
      <a class="nav-i <?= $active==='home'?'on':'' ?>" href="index.php"><span class="e">◉</span>Beranda</a>
      <a class="nav-i <?= $active==='masuk'?'on':'' ?>" href="kas-masuk.php"><span class="e">↓</span>Masuk</a>
      <a class="nav-i <?= $active==='keluar'?'on':'' ?>" href="kas-keluar.php"><span class="e">↑</span>Keluar</a>
      <a class="nav-i <?= $active==='iuran'?'on':'' ?>" href="iuran.php"><span class="e">▤</span>Iuran</a>
      <a class="nav-i <?= in_array($active,['menu','anggota','kategori','laporan','status'])?'on':'' ?>" href="menu.php"><span class="e">☰</span>Menu</a>
    </nav>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="assets/js/app.js"></script>
<?php if (isset($pageJS)) echo $pageJS; ?>
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => navigator.serviceWorker.register('service-worker.js').catch(()=>{}));
}
</script>
</body>
</html>
