<?php
require_once __DIR__ . '/../config.php';

class Supabase {
  private static function headers($extra = []) {
    return array_merge([
      'apikey: ' . SUPABASE_KEY,
      'Authorization: Bearer ' . SUPABASE_KEY,
      'Content-Type: application/json',
      'Prefer: return=representation'
    ], $extra);
  }

  private static function req($method, $path, $body = null, $query = '') {
    $url = SUPABASE_REST . ltrim($path, '/') . $query;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, self::headers());
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // Fix Windows/XAMPP lokal: CA bundle sering hilang (error 60/20).
    // Di hosting (Linux) biarkan verify ON. Deteksi otomatis:
    $ca = ini_get('curl.cainfo') ?: (ini_get('openssl.cafile') ?: '');
    if ($ca && file_exists($ca)) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    } else {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    return ['code' => $code, 'data' => $data, 'raw' => $res, 'error' => $err];
  }

  public static function select($table, $query = '?select=*&order=created_at.desc') {
    return self::req('GET', $table, null, $query);
  }
  public static function selectCustom($table, $query) {
    return self::req('GET', $table, null, $query);
  }
  public static function insert($table, $body) {
    return self::req('POST', $table, $body);
  }
  public static function update($table, $idCol, $idVal, $body) {
    $idVal = urlencode((string)$idVal);
    return self::req('PATCH', $table, $body, "?{$idCol}=eq.{$idVal}");
  }
  public static function delete($table, $idCol, $idVal) {
    $idVal = urlencode((string)$idVal);
    return self::req('DELETE', $table, null, "?{$idCol}=eq.{$idVal}");
  }
}
