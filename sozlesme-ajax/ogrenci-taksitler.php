<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); // prod gibi davran
error_reporting(E_ALL);

require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
// oturum dosyan gidip HTML basmasın!
require_once __DIR__.'/../dosyalar/oturum.php';

try {
    $db  = new Ydil();
    $pdo = $db->conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['ok'=>false,'msg'=>'Bad request']); exit;
    }

    $ogr_no = trim($_POST['ogr_no'] ?? '');
    if ($ogr_no === '') {
        echo json_encode(['ok'=>false,'msg'=>'Eksik parametre']); exit;
    }

    // Öğrenci ID
    $stmt = $pdo->prepare("SELECT ogrenci_id FROM ogrenci1 WHERE ogrenci_numara = :no LIMIT 1");
    $stmt->execute([':no'=>$ogr_no]);
    $ogrenci_id = (int)$stmt->fetchColumn();

    if(!$ogrenci_id){
        echo json_encode(['ok'=>false,'msg'=>'Öğrenci bulunamadı']); exit;
    }

    // Taksitler
    $sql = "
      SELECT
        s.sozlesme_id,
        s.sozlesme_no,
        t.taksit_id,
        t.sira_no,
        DATE_FORMAT(t.vade_tarihi, '%d.%m.%Y') AS vade_tarihi_tr,
        t.vade_tarihi,
        t.tutar,
        t.odendi_tutar,
        CASE
          WHEN t.odendi_tutar >= t.tutar THEN 'Odendi'
          WHEN t.vade_tarihi < CURDATE()    THEN 'Gecikmis'
          WHEN t.odendi_tutar > 0           THEN 'Kismi'
          ELSE 'Odenmedi'
        END AS durum
      FROM taksit1 t
      INNER JOIN sozlesme1 s ON s.sozlesme_id = t.sozlesme_id
      WHERE s.ogrenci_id = :oid
      ORDER BY s.sozlesme_id DESC, t.sira_no ASC
    ";
    $st = $pdo->prepare($sql);
    $st->execute([':oid'=>$ogrenci_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok'=>true,'rows'=>$rows]); exit;

} catch(Throwable $e){
    error_log('ogrenci-taksitler.php ERR: '.$e->getMessage());
    echo json_encode(['ok'=>false,'msg'=>'Server error']); exit;
}