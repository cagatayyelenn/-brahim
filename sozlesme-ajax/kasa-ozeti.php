<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';

$db = new Ydil();

// --- Girdi ---
$start   = isset($_GET['start']) ? trim($_GET['start']) : '';
$end     = isset($_GET['end'])   ? trim($_GET['end'])   : '';
$kasa_id = isset($_GET['kasa_id']) ? (int)$_GET['kasa_id'] : 0;
$sube_id = isset($_GET['sube_id']) ? (int)$_GET['sube_id'] : 0;

// tarih aralığı (boşsa bugüne sabitle)
if ($start === '' || $end === '') {
    $start = date('Y-m-d');
    $end   = date('Y-m-d');
}
$startDT = $start.' 00:00:00';
$endDT   = $end.' 23:59:59';

// --- Tip bazlı özet (Nakit / POS / Banka) ---
$sumSql = "
SELECT
    k.kasa_tipi AS tipi,
    SUM(CASE WHEN kh.yon='GIRIS' THEN kh.tutar ELSE 0 END) AS giris,
    SUM(CASE WHEN kh.yon='CIKIS' THEN kh.tutar ELSE 0 END) AS cikis
FROM kasa_hareketleri1 kh
JOIN kasa1 k ON k.kasa_id = kh.kasa_id
WHERE k.durum=1
  AND k.sube_id = :sid
  AND kh.hareket_tarihi BETWEEN :start AND :end
";
$params = [
    ':sid'   => $sube_id,
    ':start' => $startDT,
    ':end'   => $endDT,
];

if ($kasa_id > 0) {
    $sumSql .= " AND kh.kasa_id = :kid";
    $params[':kid'] = $kasa_id;
}

$sumSql .= " GROUP BY k.kasa_tipi";

$tipler = $db->get($sumSql, $params);

// map
$ozet = [
    'NAKIT' => ['giris'=>0,'cikis'=>0,'toplam'=>0],
    'POS'   => ['giris'=>0,'cikis'=>0,'toplam'=>0],
    'BANKA' => ['giris'=>0,'cikis'=>0,'toplam'=>0],
];

$genel_giris = 0; $genel_cikis = 0;

foreach ($tipler as $t) {
    $tip  = strtoupper($t['tipi']); // 'Nakit','POS','Banka'
    $gir  = (float)$t['giris'];
    $cik  = (float)$t['cikis'];
    $net  = $gir - $cik;

    if ($tip === 'NAKIT' || $tip === 'POS' || $tip === 'BANKA') {
        $ozet[$tip]['giris']  = $gir;
        $ozet[$tip]['cikis']  = $cik;
        $ozet[$tip]['toplam'] = $net;
    }

    $genel_giris += $gir;
    $genel_cikis += $cik;
}

$genel = [
    'giris'  => $genel_giris,
    'cikis'  => $genel_cikis,
    'toplam' => $genel_giris - $genel_cikis
];

// --- JSON ---
echo json_encode([
    'status' => 1,
    'ozet'   => $ozet,
    'genel'  => $genel,
], JSON_UNESCAPED_UNICODE);