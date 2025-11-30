<?php
require_once "../dosyalar/config.php";
require_once "../dosyalar/Ydil.php";

header('Content-Type: application/json; charset=utf-8');

$db = new Ydil();
$pdo = $db->conn;

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok'=>false, 'msg'=>'Geçersiz ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM kasa_hareketleri1 WHERE hareket_id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['ok'=>true, 'msg'=>'Kasa hareketi başarıyla silindi.']);
}
catch (Exception $e){
    echo json_encode(['ok'=>false, 'msg'=>'Hata: '.$e->getMessage()]);
}