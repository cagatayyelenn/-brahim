<?php
include "c/fonk.php";
include "c/config.php";

$page     = (int)($_POST['page']     ?? 1);
$per_page = (int)($_POST['per_page'] ?? 10);
$start    = $_POST['start_date'] ?? '';
$end      = $_POST['end_date']   ?? '';

$offset = ($page-1) * $per_page;

$where = "oa.kapandi = 0";
if ($start !== '' && $end !== '') {
    $where .= " AND oa.baslangic BETWEEN '$start' AND '$end'";
}

// Toplam kayıt
$totalRow = $Ydil->getone("
    SELECT COUNT(*) AS cnt
    FROM ogretmen_atama oa
    LEFT JOIN ogrenci o   ON o.ogrenci_id   = oa.ogrenci_id
    LEFT JOIN ogretmen og ON og.ogretmen_id = oa.ogretmen_id
    WHERE $where
");
$total = (int)($totalRow['cnt'] ?? 0);

// Asıl veriler
$rows = $Ydil->get("
    SELECT 
        oa.id,
        CONCAT(o.ogrenci_adi,' ',o.ogrenci_soyadi) AS ogrenci,
        CONCAT(og.ogretmen_adi,' ',og.ogretmen_soyadi) AS ogretmen,
        oa.baslangic AS ders_tarihi,
        oa.saat      AS ders_saati
    FROM ogretmen_atama oa
    LEFT JOIN ogrenci o   ON o.ogrenci_id   = oa.ogrenci_id
    LEFT JOIN ogretmen og ON og.ogretmen_id = oa.ogretmen_id
    WHERE $where
    ORDER BY oa.baslangic DESC, oa.saat ASC
    LIMIT $offset, $per_page
");


// JSON çıktı
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "total" => $total,
    "page"  => $page,
    "rows"  => array_map(function($r){
        return [
            "id"        => $r['id'],
            "ogrenci"   => $r['ogrenci'] ?: '-',
            "ogretmen"  => $r['ogretmen'] ?: '-',
            "ders_tarihi"=> $r['ders_tarihi'] ?: '-',
            "ders_saati" => $r['ders_saati'] ?: '-',
            "islem"     => '<a href="ders-kapat-islem.php?id='.$r['id'].'" class="btn btn-sm btn-outline-danger">Dersi Kapat</a>'
        ];
    }, $rows ?? [])
], JSON_UNESCAPED_UNICODE);