<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../dosyalar/config.php';
require_once __DIR__ . '/../dosyalar/Ydil.php';
require_once __DIR__ . '/../dosyalar/oturum.php';

$db = new Ydil();
$pdo = $db->conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Geçersiz istek']);
    exit;
}

$sozlesme_id = (int) ($_POST['sozlesme_id'] ?? 0);
if ($sozlesme_id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Sözleşme ID geçersiz']);
    exit;
}

// 1. Sözleşmeye ait ödeme var mı?
// Taksit ödemeleri (taksit1.odendi_tutar > 0)
// Veya Peşinat (odeme1 tablosunda bu sözleşmeye ait, ama taksit ödemesi olmayan kayıtlar da olabilir)
// En garanti yöntem: kasa_hareketleri1 veya odeme1 tablosunu kontrol etmek.

// Taksit kontrol
$taksitOdemeSql = "SELECT COUNT(*) FROM taksit1 WHERE sozlesme_id = :sid AND odendi_tutar > 0";
$tStmt = $pdo->prepare($taksitOdemeSql);
$tStmt->execute(['sid' => $sozlesme_id]);
if ($tStmt->fetchColumn() > 0) {
    echo json_encode(['ok' => false, 'msg' => 'Bu sözleşmeye ait ödenmiş taksitler var. Önce tahsilatları iptal edin.']);
    exit;
}

// Kasa hareketi / Odeme tablosu kontrol
// (Peşinat girilmişse burada görünür)
$odemeSql = "SELECT COUNT(*) FROM odeme1 WHERE sozlesme_id = :sid";
$oStmt = $pdo->prepare($odemeSql);
$oStmt->execute(['sid' => $sozlesme_id]);
if ($oStmt->fetchColumn() > 0) {
    echo json_encode(['ok' => false, 'msg' => 'Bu sözleşmeye ait kasa/ödeme kayıtları var (Peşinat vb.). Önce bunları silin.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Taksitleri sil
    $delTaksit = $pdo->prepare("DELETE FROM taksit1 WHERE sozlesme_id = :sid");
    $delTaksit->execute(['sid' => $sozlesme_id]);

    // 2. Sözleşmeyi sil
    $delSozlesme = $pdo->prepare("DELETE FROM sozlesme1 WHERE sozlesme_id = :sid");
    $delSozlesme->execute(['sid' => $sozlesme_id]);

    // Logla
    $db->log('sozlesme1', $sozlesme_id, 'SILME', 'Sözleşme silindi. ID: ' . $sozlesme_id);

    $pdo->commit();

    echo json_encode(['ok' => true, 'msg' => 'Sözleşme başarıyla silindi.']);

} catch (Throwable $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    error_log("Sozlesme Sil Hata: " . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Silme işlemi başarısız: ' . $e->getMessage()]);
}