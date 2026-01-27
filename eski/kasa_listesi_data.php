<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";
header('Content-Type: application/json; charset=utf-8');

$page     = max(1, (int)($_POST['page'] ?? 1));
$perPage  = max(1, min(100, (int)($_POST['per_page'] ?? 10)));
$start    = trim($_POST['start_date'] ?? '');
$end      = trim($_POST['end_date'] ?? '');
$sube_id  = (int)($_POST['sube_id'] ?? 0);

$where = [];
if ($start !== '') $where[] = "kgc.tarih >= '{$start} 00:00:00'";
if ($end   !== '') $where[] = "kgc.tarih <= '{$end} 23:59:59'";
if ($sube_id > 0 ) $where[] = "kgc.sube_id = {$sube_id}";
$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

/* Toplam satır sayısı */
$cntSql = "SELECT COUNT(*) AS c FROM kasa_gir_cik kgc {$whereSql}";
$cntRow = $Ydil->getone($cntSql);
$total  = (int)($cntRow['c'] ?? 0);

$offset = ($page - 1) * $perPage;

/* Veri + kümülatif bakiye (window function) */
$dataSql = "
SELECT *
FROM (
  SELECT
    kgc.id,
    kht.tur_adi,
    CONCAT(p.personel_adi,' ',p.personel_soyadi) AS personel_adi,
    s.sube_adi,
    kgc.islem_tipi,
    kgc.tutar,
    CASE WHEN kgc.islem_tipi='giris' THEN kgc.tutar ELSE 0 END AS giris,
    CASE WHEN kgc.islem_tipi='cikis' THEN kgc.tutar ELSE 0 END AS cikis,
    kgc.aciklama,
    kgc.tarih,
    SUM(
      CASE
        WHEN kgc.islem_tipi='giris' THEN  kgc.tutar
        WHEN kgc.islem_tipi='cikis' THEN -kgc.tutar
        ELSE 0
      END
    ) OVER(ORDER BY kgc.tarih ASC, kgc.id ASC) AS bakiye
  FROM kasa_gir_cik AS kgc
  LEFT JOIN kasa_hareket_turleri AS kht ON kht.tur_id    = kgc.tur_id
  LEFT JOIN personel              AS p   ON p.personel_id = kgc.personel_id
  LEFT JOIN sube                  AS s   ON s.sube_id     = kgc.sube_id
  {$whereSql}
  ORDER BY kgc.tarih ASC, kgc.id ASC
) t
LIMIT {$perPage} OFFSET {$offset}";
$rows = $Ydil->get($dataSql) ?: [];

/* Çıktıyı sadeleştir + tarih formatı */
$out = [];
foreach ($rows as $r) {
    $bakiye = (float)($r['bakiye'] ?? 0);
    $out[] = [
        'id'           => (int)$r['id'],
        'personel_adi' => (string)($r['personel_adi'] ?? ''),
        'tur_adi'      => (string)($r['tur_adi'] ?? ''),
        'sube_adi'     => (string)($r['sube_adi'] ?? ''),
        'giris'        => (float)($r['giris'] ?? 0),
        'cikis'        => (float)($r['cikis'] ?? 0),
        'bakiye'       => $bakiye,
        'bakiye_neg'   => ($bakiye < 0),
        'aciklama'     => (string)($r['aciklama'] ?? ''),
        'tarih'        => (string)($r['tarih'] ?? ''),
        'tarih_fmt'    => !empty($r['tarih']) ? date('d.m.Y H:i', strtotime($r['tarih'])) : ''
    ];
}

echo json_encode([
    'total' => $total,
    'page'  => $page,
    'rows'  => $out
], JSON_UNESCAPED_UNICODE);