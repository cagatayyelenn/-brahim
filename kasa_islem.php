<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
// kasa_islem.php (basit JSON)
header('Content-Type: application/json; charset=utf-8');

include "c/fonk.php";
include "c/config.php";
include "c/user.php";


// Zorunlu sessionlar
$personel_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$sube_id     = isset($_SESSION['subedurum']) ? (int)$_SESSION['subedurum'] : 0;

if ($personel_id <= 0 || $sube_id <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Oturum bulunamadı.']);
    exit;
}

// Girdiler
$islem_tipi = ($_POST['islem_tipi'] ?? 'giris') === 'cikis' ? 'cikis' : 'giris';
$tarih      = trim($_POST['tarih'] ?? '');
$tutar      = (float)($_POST['tutar'] ?? 0);
$odeme_turu = strtolower(trim($_POST['odeme_turu'] ?? '')); // 'nakit','kredikarti','bankahavalesi'
$aciklama   = trim($_POST['aciklama'] ?? '');

// Boş tarih gelirse şimdi
if ($tarih === '') {
    $tarih = date('Y-m-d H:i:s');
}

// Sade haritalama (aynen bıraktım)
$map = [
    'nakit'         => 1,
    'kredikarti'    => 2,
    'bankahavalesi' => 3,
];
$tur_id = $map[$odeme_turu] ?? 1;

// Basit validasyon
if ($tutar <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Tutar 0\'dan büyük olmalı.']);
    exit;
}

// Insert
$columns = ['sube_id','personel_id','islem_tipi','tur_id','tutar','tarih','aciklama'];
$values  = [$sube_id, $personel_id, $islem_tipi, $tur_id, $tutar, $tarih, $aciklama];

$insertId = $Ydil->newInsert('kasa_gir_cik', $columns, $values);
 
  

// JSON cevap
if ($insertId) {
    echo json_encode([
        'ok' => true,
        'id' => (int)$insertId,
        'message' => 'Kayıt oluşturuldu.'
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'message' => 'Kayıt oluşturulamadı.'
    ]);
}
