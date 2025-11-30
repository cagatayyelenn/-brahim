<?php
ob_start();
require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';
$db  = new Ydil();
header('Content-Type: application/json');

$db = new Ydil();

$kisi_id = (int)($_POST['kisi_id'] ?? 0);
if ($kisi_id <= 0) {
    echo json_encode(['status'=>0,'message'=>'Geçersiz ID.']);
    exit;
}

$sql = "UPDATE kullanici_giris1 SET sifre = '0' WHERE kisi_id = :id LIMIT 1";
$stmt = $db->conn->prepare($sql);

if ($stmt->execute([':id'=>$kisi_id])) {
    echo json_encode(['status'=>1,'message'=>'Şifre başarıyla sıfırlandı.']);
} else {
    echo json_encode(['status'=>0,'message'=>'Şifre sıfırlanamadı.']);
}