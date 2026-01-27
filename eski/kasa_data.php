<?php
// kasa_data.php
header('Content-Type: application/json; charset=utf-8');

// kasa_data.php
header('Content-Type: application/json; charset=utf-8');

require_once "c/fonk.php";
require_once "c/config.php";
include "c/user.php";

/** ---- Güvenli kaçış helper'ı ----
 * Projenizde $Ydil->escape varsa onu kullanır; yoksa basit bir fallback uygular.
 */
function esc($str)
{
    global $Ydil;
    if (isset($Ydil) && method_exists($Ydil, 'escape')) {
        return $Ydil->escape($str);
    }
    // Basit fallback: HTML/etiket temizliği + addslashes
    return addslashes(strip_tags($str));
}

/** ---- Yardımcılar ---- */
function normDate($s)
{
    if (!$s) return null;
    $ts = strtotime($s);
    return $ts ? date('Y-m-d', $ts) : null;
}

$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$per_page = isset($_POST['per_page']) ? max(1, (int)$_POST['per_page']) : 10;

$start_raw = trim($_POST['start_date'] ?? '');
$end_raw = trim($_POST['end_date'] ?? '');
$tur_raw = trim($_POST['tur'] ?? '');   // <--- EKLENDİ (örn. "Nakit")

$start = normDate($start_raw);
$end = normDate($end_raw);

// Tarih filtre parçaları
$kasaWhere = "1=1";
$odmWhere = "1=1";

// Tarih filtreleri
if ($start && $end) {
    $kasaWhere .= " AND DATE(kgc.tarih) BETWEEN '{$start}' AND '{$end}'";
    $odmWhere .= " AND DATE(o.odeme_tarihi) BETWEEN '{$start}' AND '{$end}'";
} elseif ($start && !$end) {
    $kasaWhere .= " AND DATE(kgc.tarih) >= '{$start}'";
    $odmWhere .= " AND DATE(o.odeme_tarihi) >= '{$start}'";
} elseif (!$start && $end) {
    $kasaWhere .= " AND DATE(kgc.tarih) <= '{$end}'";
    $odmWhere .= " AND DATE(o.odeme_tarihi) <= '{$end}'";
}

// TUR filtresi (küçük/büyük harfe duyarsız eşitlik)
// - kasa kolunda: kht.tur_adi
// - taksit_odeme kolunda: ot.ad
if ($tur_raw !== '') {
    $tur_lc = mb_strtolower($tur_raw, 'UTF-8');
    $tur_lc = esc($tur_lc);
    $kasaWhere .= " AND LOWER(COALESCE(kht.tur_adi,'')) = '{$tur_lc}'";
    $odmWhere .= " AND LOWER(COALESCE(ot.ad,''))      = '{$tur_lc}'";
}

// UNION baz sorgu
$baseSql = "
(
  SELECT
    'kasa' AS kaynak,
    kgc.id,
    NULL     AS satis_id,
    NULL     AS taksit_id,
    NULL     AS ogrenci,
    CONCAT(p.personel_adi,' ',p.personel_soyadi) AS personel,
    s.sube_adi AS sube,
    kht.tur_adi,
    kgc.islem_tipi,          -- 'giris' / 'cikis'
    kgc.tutar,
    kgc.tarih,
    kgc.aciklama
  FROM kasa_gir_cik kgc
  LEFT JOIN kasa_hareket_turleri kht ON kht.tur_id     = kgc.tur_id
  LEFT JOIN personel            p     ON p.personel_id = kgc.personel_id
  LEFT JOIN sube                s     ON s.sube_id     = kgc.sube_id
  WHERE {$kasaWhere}
)
UNION ALL
(
  SELECT
    'taksit_odeme' AS kaynak,
    o.id,
    o.satis_id,
    o.taksit_id,
    CONCAT(og.ogrenci_adi,' ',og.ogrenci_soyadi) AS ogrenci,
    NULL AS personel,
    NULL AS sube,
    CONCAT('Taksit Ödemesi (', COALESCE(ot.ad,'-'), ')') AS tur_adi,
    'giris' AS islem_tipi,   -- öğrenci ödemesi kasaya GİRİŞ
    o.tutar,
    o.odeme_tarihi AS tarih,
    o.aciklama
  FROM odemeler o
  LEFT JOIN taksitler      t  ON t.id = o.taksit_id
  LEFT JOIN odeme_turleri  ot ON ot.id = t.odeme_tur_id
  LEFT JOIN ogrenci        og ON og.ogrenci_id = o.ogrenci_id
  WHERE {$odmWhere}
)
";

// Toplam kayıt
$totalRow = $Ydil->getone("SELECT COUNT(*) AS c FROM ({$baseSql}) AS u");
$total = (int)($totalRow['c'] ?? 0);

// Sayfalama
$offset = ($page - 1) * $per_page;
$offset = max(0, $offset);
$per = max(1, $per_page);

// Sayfa verisi
$sql = "
SELECT
  u.*
FROM ({$baseSql}) AS u
ORDER BY u.tarih DESC, u.id DESC
LIMIT {$per} OFFSET {$offset}
";

$rows = $Ydil->get($sql) ?: [];

// İsteğe bağlı: tarih formatı
foreach ($rows as &$r) {
    if (!empty($r['tarih'])) {
        $ts = strtotime($r['tarih']);
        $r['tarih'] = $ts ? date('d.m.Y H:i', $ts) : $r['tarih'];
    }
}
unset($r);

echo json_encode([
    'total' => $total,
    'page' => $page,
    'rows' => $rows,
], JSON_UNESCAPED_UNICODE);