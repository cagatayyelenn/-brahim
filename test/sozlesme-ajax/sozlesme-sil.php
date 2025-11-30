<?php
ob_start();
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__."/../dosyalar/config.php";
require_once __DIR__."/../dosyalar/Ydil.php";
require_once __DIR__."/../dosyalar/oturum.php";

$db = new Ydil();
$pdo = $db->conn;
 
$sozlesme_id = (int)($_POST['id'] ?? 0);

if (!$sozlesme_id) {
    echo json_encode(['ok'=>false,'msg'=>'Sözleşme ID bulunamadı']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1) kasa hareketleri
    $pdo->prepare("DELETE FROM kasa_hareketleri1 WHERE sozlesme_id = :id")
        ->execute([':id' => $sozlesme_id]);

    // 2) ödemeler
    $pdo->prepare("DELETE FROM odeme1 WHERE sozlesme_id = :id")
        ->execute([':id' => $sozlesme_id]);

    // 3) taksitler
    $pdo->prepare("DELETE FROM taksit1 WHERE sozlesme_id = :id")
        ->execute([':id' => $sozlesme_id]);

    // 4) sözleşme ana kaydı
    $pdo->prepare("DELETE FROM sozlesme1 WHERE sozlesme_id = :id")
        ->execute([':id' => $sozlesme_id]);

    $pdo->commit();

    echo json_encode(['ok' => true, 'msg' => 'Sözleşme silindi']);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['ok'=>false,'msg'=>'Hata: '.$e->getMessage()]);
    exit;
}