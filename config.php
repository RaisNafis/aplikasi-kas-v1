<?php
// Konfigurasi utama aplikasi Kas
define('SUPABASE_URL', 'https://qetmmcdnydswfmiywfyt.supabase.co');
define('SUPABASE_REST', 'https://qetmmcdnydswfmiywfyt.supabase.co/rest/v1/');
define('SUPABASE_KEY', 'sb_publishable_tMSxoJMYT819Obev47vL1g_6U-H-CXe');
define('APP_NAME', 'Huang Kas');
define('APP_FULL', 'Huang Kas');
date_default_timezone_set('Asia/Jakarta');

function rp($n) {
  return 'Rp ' . number_format((float)$n, 0, ',', '.');
}
function e($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
function periodeID($bln = null) {
  $t = $bln ? strtotime($bln) : time();
  $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  return $bulan[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
}
