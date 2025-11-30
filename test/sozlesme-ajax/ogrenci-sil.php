<?php
// /test/ajax/ogrenci-sil.php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../dosyalar/config.php';
require_once __DIR__ . '/../dosyalar/Ydil.php';
require_once __DIR__ . '/../dosyalar/oturum.php';

$db  = new Ydil();
$pdo = $db->conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'msg'=>'Geçersiz istek yöntemi']); exit;
}

$ogr_no = trim($_POST['ogrenci_numara'] ?? '');
if ($ogr_no === '') {
    echo json_encode(['ok'=>false,'msg'=>'Öğrenci numarası eksik']); exit;
}

try {
    // 1) öğrenci_id bul
    $stmt = $pdo->prepare("SELECT ogrenci_id, aktif FROM ogrenci1 WHERE ogrenci_numara = :no LIMIT 1");
    $stmt->execute([':no' => $ogr_no]);
    $ogr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ogr) {
        echo json_encode(['ok'=>false,'msg'=>'Öğrenci bulunamadı.']); exit;
    }

    $ogrenci_id = (int)$ogr['ogrenci_id'];

    // 2) BORÇ HESABI:
    // taksit1 üzerinden ödenmemiş/kısmi/gecikmiş taksit bakiyesi
    $sqlBorc = "
        SELECT COALESCE(SUM(t.tutar - t.odendi_tutar), 0) AS bakiye
        FROM taksit1 t
        INNER JOIN sozlesme1 s ON s.sozlesme_id = t.sozlesme_id
        WHERE s.ogrenci_id = :oid
          AND t.durum IN ('Odenmedi','Kismi','Gecikmis')
    ";
    $stB = $pdo->prepare($sqlBorc);
    $stB->execute([':oid' => $ogrenci_id]);
    $bakiye = (float)$stB->fetchColumn();

    if ($bakiye > 0) {
        echo json_encode([
            'ok'   => false,
            'code' => 'BORC_VAR',
            'msg'  => 'Öğrencinin ödenmemiş borcu var. İşlem iptal edildi.',
            'bakiye' => number_format($bakiye, 2, ',', '.')
        ]);
        exit;
    }

    // 3) Pasife al
    $del = $pdo->prepare("DELETE FROM ogrenci1 WHERE ogrenci_id = :id");
    $del->execute([':id' => $ogrenci_id]);

    echo json_encode([
        'ok'  => true,
        'msg' => 'Öğrenci tamamen silindi.'
    ]);
    exit;

} catch (Throwable $e) {
    error_log("ogrenci-sil.php ERROR: ".$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Sunucu hatası.']);
    exit;
}