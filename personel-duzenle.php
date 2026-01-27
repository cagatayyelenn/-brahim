<?php
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
header('Content-Type: application/json');

$db = new Ydil();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>0,'message'=>'Geçersiz istek']);
    exit;
}

$pid   = (int)($_POST['personel_id'] ?? 0);
$adi   = trim($_POST['personel_adi'] ?? '');
$soyad = trim($_POST['personel_soyadi'] ?? '');
$tel   = preg_replace('/\D+/', '', $_POST['personel_tel'] ?? '');
$mail  = trim($_POST['personel_mail'] ?? '');
$dogum = $_POST['personel_dogumtar'] ?? null;
$cins  = $_POST['personel_cinsiyet'] ?? '';
$adres = trim($_POST['personel_adres'] ?? '');
$yetki = (int)($_POST['yetki'] ?? 0);
$sube  = $_POST['sube_id'] ?? null;
$durum = (int)($_POST['durum'] ?? 1);

if ($pid<=0 || $adi==='' || $soyad==='' || $tel==='' || !in_array($yetki,[1,2])) {
    echo json_encode(['status'=>0,'message'=>'Eksik veya hatalı alanlar var.']);
    exit;
}

if ($yetki === 1) { $sube = null; }

$cols = [
    'personel_adi','personel_soyadi','personel_tel','personel_mail',
    'personel_cinsiyet','personel_dogumtar','personel_adres',
    'yetki','sube_id','durum'
];
$vals = [
    $adi, $soyad, $tel, $mail,
    $cins, $dogum ?: null, $adres,
    $yetki, $sube ?: null, $durum
];

$upd = $db->update('personel1', $cols, $vals, 'personel_id', $pid);

if ($upd['status']==1)
    echo json_encode(['status'=>1,'title'=>'Güncellendi','message'=>'Personel bilgileri başarıyla güncellendi.']);
else
    echo json_encode(['status'=>0,'title'=>'Hata','message'=>'Güncelleme işlemi başarısız.']);