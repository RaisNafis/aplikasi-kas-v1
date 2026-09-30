<?php
require_once __DIR__ . '/lib/SupabaseClient.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$table = $_GET['table'] ?? $_POST['table'] ?? '';
$allow = ['users','kas_masuk','kas_keluar','pembayaran_kas','kategori','pengaturan','log_aktivitas'];

function out($d, $code = 200) { http_response_code($code); echo json_encode($d); exit; }

// Ambil pesan error Supabase yang mudah dibaca manusia
function sbMsg($r) {
  $d = $r['data'] ?? null;
  if (is_array($d) && isset($d['message'])) {
    $m = $d['message'];
    if (strpos($m, 'schema cache') !== false) return 'Tabel belum dibuat di Supabase (PGRST205). Jalankan supabase_schema.sql di SQL Editor.';
    if (stripos($m, 'row-level security') !== false || ($d['code'] ?? '') === '42501') return 'Ditolak RLS policy. Aktifkan policy "public read write" (lihat supabase_schema.sql).';
    return $m;
  }
  if (!empty($r['error'])) return 'Jaringan ke Supabase gagal: ' . $r['error'];
  return 'Supabase HTTP ' . ($r['code'] ?? '?');
}
function isList($d) { return is_array($d) && (function_exists('array_is_list') ? array_is_list($d) : !isset($d['message'])); }

try {
// ---- Diagnosa: cek semua tabel, dipakai halaman status.php ----
if ($action === 'ping') {
  $tables = ['users','kategori','kas_masuk','kas_keluar','pembayaran_kas','pengaturan'];
  $res = [];
  foreach ($tables as $t) {
    $r = Supabase::selectCustom($t, '?select=*&limit=1');
    $res[$t] = ['ok' => ($r['code'] >= 200 && $r['code'] < 300 && isList($r['data'])),
                'code' => $r['code'], 'msg' => ($r['code'] >= 200 && $r['code'] < 300) ? 'OK' : sbMsg($r)];
  }
  out(['ok' => true, 'tables' => $res]);
}
// ---- Isi data contoh sekali klik ----
if ($action === 'seed') {
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') out(['ok' => false, 'error' => 'Gunakan POST'], 405);
  $probe = Supabase::selectCustom('users', '?select=id&limit=1');
  if ($probe['code'] >= 400 || !isList($probe['data'])) out(['ok' => false, 'error' => sbMsg($probe)], 502);
  $mk = function($t, $b) {
    $r = Supabase::insert($t, $b);
    if ($r['code'] >= 400) out(['ok' => false, 'error' => "Gagal isi $t: " . sbMsg($r)], 502);
    return $r['data'];
  };
  $us = $mk('users', [
    ['nama' => 'Budi Santoso', 'no_wa' => '628123450001', 'role' => 'bendahara', 'status' => 'aktif'],
    ['nama' => 'Siti Aminah', 'no_wa' => '628123450002', 'role' => 'anggota', 'status' => 'aktif'],
    ['nama' => 'Agus Wijaya', 'no_wa' => '628123450003', 'role' => 'anggota', 'status' => 'aktif'],
  ]);
  $bln = date('Y-m');
  foreach ($us as $u) $mk('pembayaran_kas', ['user_id' => $u['id'], 'periode' => $bln, 'jumlah' => 20000, 'status' => 'belum', 'metode' => 'cash']);
  $mk('kas_masuk', [['tanggal' => date('Y-m-d'), 'jumlah' => 60000, 'keterangan' => 'Iuran ' . $bln, 'sumber' => 'iuran'], ['tanggal' => date('Y-m-d'), 'jumlah' => 150000, 'keterangan' => 'Donasi warga', 'sumber' => 'donasi']]);
  $mk('kas_keluar', [['tanggal' => date('Y-m-d'), 'jumlah' => 45000, 'keterangan' => 'Beli ATK rapat', 'penerima' => 'Toko Barokah']]);
  out(['ok' => true, 'pesan' => 'Data contoh dibuat: 3 anggota + tagihan + kas masuk/keluar.']);
}
if ($action === 'list') {
  if (!in_array($table, $allow)) out(['ok' => false, 'error' => 'tabel tidak diizinkan'], 400);
  $q = '?select=*&order=created_at.desc&limit=200';
  if ($table === 'kas_masuk' || $table === 'kas_keluar') $q = '?select=*,kategori(nama)&order=tanggal.desc&limit=200';
  if ($table === 'pembayaran_kas') $q = '?select=*,users(nama)&order=periode.desc&limit=300';
  $r = Supabase::selectCustom($table, $q);
  if ($r['code'] >= 400 || !isList($r['data'])) out(['ok' => false, 'error' => sbMsg($r)], 502);
  out($r['data']);
}
if ($action === 'saldo') {
  $m = Supabase::selectCustom('kas_masuk', '?select=jumlah');
  $k = Supabase::selectCustom('kas_keluar', '?select=jumlah');
  $u = Supabase::selectCustom('users', '?select=id&status=eq.aktif');
  $t = Supabase::selectCustom('pembayaran_kas', '?select=id&status=neq.lunas');
  foreach (['kas_masuk' => $m, 'kas_keluar' => $k, 'users' => $u, 'pembayaran_kas' => $t] as $tn => $r) {
    if ($r['code'] >= 400 || !isList($r['data'])) out(['ok' => false, 'error' => sbMsg($r)], 502);
  }
  $tm = array_sum(array_column($m['data'], 'jumlah'));
  $tk = array_sum(array_column($k['data'], 'jumlah'));
  out(['masuk' => $tm, 'keluar' => $tk, 'saldo' => $tm - $tk,
       'anggota' => count($u['data']), 'tunggakan' => count($t['data'])]);
}
if ($action === 'create') {
  if (!in_array($table, $allow)) out(['ok' => false, 'error' => 'tabel tidak diizinkan'], 400);
  $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  unset($body['action'], $body['table']);
  foreach ($body as $kk => $vv) if ($vv === '') $body[$kk] = null;
  if (isset($body['jumlah'])) $body['jumlah'] = (float)$body['jumlah'];
  $r = Supabase::insert($table, $body);
  if ($r['code'] >= 400) out(['ok' => false, 'error' => sbMsg($r)], 502);
  out($r['data']);
}
if ($action === 'update') {
  if (!in_array($table, $allow)) out(['ok' => false, 'error' => 'tabel tidak diizinkan'], 400);
  $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  $id = $body['id'] ?? $_GET['id'] ?? null;
  unset($body['action'], $body['table'], $body['id']);
  foreach ($body as $kk => $vv) if ($vv === '') $body[$kk] = null;
  $r = Supabase::update($table, 'id', $id, $body);
  if ($r['code'] >= 400) out(['ok' => false, 'error' => sbMsg($r)], 502);
  out($r['data']);
}
if ($action === 'delete') {
  if (!in_array($table, $allow)) out(['ok' => false, 'error' => 'tabel tidak diizinkan'], 400);
  $r = Supabase::delete($table, 'id', $_GET['id'] ?? null);
  if ($r['code'] >= 400) out(['ok' => false, 'error' => sbMsg($r)], 502);
  out(['ok' => true]);
}
if ($action === 'bayar') {
  $body = json_decode(file_get_contents('php://input'), true) ?: [];
  $r = Supabase::update('pembayaran_kas', 'id', $body['id'] ?? null, [
    'status' => 'lunas', 'tanggal_bayar' => date('Y-m-d'), 'metode' => $body['metode'] ?? 'cash']);
  if ($r['code'] >= 400) out(['ok' => false, 'error' => sbMsg($r)], 502);
  out($r['data']);
}
out(['ok' => false, 'error' => 'action tidak dikenal'], 400);
} catch (Throwable $ex) { out(['ok' => false, 'error' => $ex->getMessage()], 500); }
