<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';

$db = new Ydil();

// --- Yardımcı ---
function fmtDT($dt){
    if(!$dt) return '-';
    try { $d = new DateTime($dt); return $d->format('d.m.Y H:i'); }
    catch(Exception $e){ return '-'; }
}

// --- Girdi ---
$start   = isset($_GET['start']) ? trim($_GET['start']) : '';
$end     = isset($_GET['end'])   ? trim($_GET['end'])   : '';
$kasa_id = isset($_GET['kasa_id']) ? (int)$_GET['kasa_id'] : 0;
$sube_id = isset($_GET['sube_id']) ? (int)$_GET['sube_id'] : 0;

$page  = max(1, (int)($_GET['page']  ?? 1));
$limit = min(500, max(1, (int)($_GET['limit'] ?? 100)));
$offset = ($page-1) * $limit;

// tarih aralığı (boşsa bugüne sabitle)
if ($start === '' || $end === '') {
    $start = date('Y-m-d');
    $end   = date('Y-m-d');
}
$startDT = $start.' 00:00:00';
$endDT   = $end.' 23:59:59';

// --- Ana Sorgu (liste) ---
$sql = "
SELECT
    kh.hareket_id                                     AS id,
    kh.hareket_tarihi                                 AS hareket_tarihi,
    k.kasa_adi                                        AS kasa,
    k.kasa_tipi                                       AS kasa_tipi,
    kh.yon                                            AS yon,
    kh.tutar                                          AS tutar,
    COALESCE(kht.tur_adi, kh.hareket_tipi)            AS kategori,
    oy.yontem_adi                                     AS odeme_turu,
    kh.aciklama                                       AS aciklama,
    CONCAT(p.personel_adi,' ',p.personel_soyadi)      AS islem_yapan,
    o.ogrenci_id                                      AS ogrenci_id,
    o.ogrenci_numara                                  AS ogrenci_numara,
    CONCAT(o.ogrenci_adi,' ',o.ogrenci_soyadi)        AS ogrenci_adsoyad
FROM kasa_hareketleri1 kh
JOIN kasa1 k               ON k.kasa_id = kh.kasa_id
LEFT JOIN odeme1 od        ON od.odeme_id = kh.odeme_id
LEFT JOIN odeme_yontem1 oy ON oy.yontem_id = od.yontem_id
LEFT JOIN kasa_hareket_turleri kht ON kht.tur_id = kh.hareket_tur_id
LEFT JOIN personel1 p      ON p.personel_id = kh.created_by
LEFT JOIN ogrenci1 o       ON o.ogrenci_id = kh.ogrenci_id
WHERE k.durum = 1
  AND k.sube_id = :sid
  AND kh.hareket_tarihi BETWEEN :start AND :end
";

$params = [
    ':sid'   => $sube_id,
    ':start' => $startDT,
    ':end'   => $endDT,
];

if ($kasa_id > 0) {
    $sql .= " AND kh.kasa_id = :kid";
    $params[':kid'] = $kasa_id;
}

$sql .= " ORDER BY kh.hareket_tarihi DESC, kh.hareket_id DESC LIMIT :lim OFFSET :ofs";

// PDO numeric bind için hazırlık
$stmt = $db->conn->prepare($sql);
foreach ($params as $k=>$v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':ofs', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Toplam kayıt sayısı (sayfalama istersen) ---
$countSql = "
SELECT COUNT(*) AS adet
FROM kasa_hareketleri1 kh
JOIN kasa1 k ON k.kasa_id = kh.kasa_id
WHERE k.durum=1
  AND k.sube_id = :sid
  AND kh.hareket_tarihi BETWEEN :start AND :end
";
$countParams = [
    ':sid'   => $sube_id,
    ':start' => $startDT,
    ':end'   => $endDT,
];
if ($kasa_id > 0) {
    $countSql .= " AND kh.kasa_id = :kid";
    $countParams[':kid'] = $kasa_id;
}
$adet = $db->gets($countSql, $countParams);
$total = (int)($adet['adet'] ?? 0);

// --- JSON formatına dönüştür ---
$out = [];
foreach ($rows as $r) {
    $out[] = [
        'id'             => (int)$r['id'],
        'tarih'          => fmtDT($r['hareket_tarihi']),
        'kasa'           => $r['kasa'],
        'kategori'       => $r['kategori'],
        'odeme_turu'     => $r['odeme_turu'],
        'aciklama'       => $r['aciklama'],
        'islem_yapan'    => $r['islem_yapan'],
        'yon'            => $r['yon'],
        'tutar'          => (float)$r['tutar'],
        'ogrenci_numara' => $r['ogrenci_numara'],
        'ogrenci_adsoyad'=> $r['ogrenci_adsoyad'],
    ];
}

echo json_encode([
    'status' => 1,
    'page'   => $page,
    'limit'  => $limit,
    'total'  => $total,
    'rows'   => $out
], JSON_UNESCAPED_UNICODE);