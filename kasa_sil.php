<?php
// kasa_sil.php
header('Content-Type: application/json; charset=utf-8');

include "c/fonk.php";
include "c/config.php";
include "c/user.php";

// (Opsiyonel) oturum kontrolü:
$personel_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$sube_id     = isset($_SESSION['subedurum']) ? (int)$_SESSION['subedurum'] : 0;
if ($personel_id <= 0 || $sube_id <= 0) {
    echo json_encode(['ok'=>false, 'message'=>'Oturum bulunamadı.']); exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['ok'=>false, 'message'=>'Geçersiz kayıt ID']); exit;
}

try {
    // Sadece kasa_gir_cik’tan sil (taksit ödemeleri ayrı iş kuralı olabilir)
    // Not: Projendeki DB helper nasıl dönüyorsa ona göre kontrol et.
    // Çoğu durumda true/false veya etkilenen satır sayısı gelir.
    $aff = $Ydil->delete("kasa_gir_cik", $id, "id");

    $ok = !!$aff;
    echo json_encode([
        'ok' => $ok,
        'message' => $ok ? 'Kayıt silindi.' : 'Kayıt silinemedi.'
    ]);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false, 'message'=>'Sunucu hatası.']);
}