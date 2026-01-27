<?php
// odeme_isle.php – Sade sürüm (odeme_turleri sorgusu yok)
// Not: Sizin mevcut PDO bağınız $Ydil->conn varsayımıyla kullanılıyor.

include "c/fonk.php";
include "c/config.php";
include "c/user.php";

 
header('Content-Type: application/json; charset=utf-8');

function jexit($a){ echo json_encode($a, JSON_UNESCAPED_UNICODE); exit; }
function nf($s){
    if($s===null) return 0.0;
    if(is_numeric($s)) return (float)$s;
    $s = trim((string)$s);
    $s = str_replace(['.', ' '], ['', ''], $s);
    $s = str_replace(',', '.', $s);
    return (float)$s;
}

try {
    /* -------- INPUT -------- */
    $ogrenci_id    = (int)($_POST['ogrenci_id']    ?? 0);
    $taksit_id     = (int)($_POST['taksit_id']     ?? 0);
    $kasa_id       = (int)($_POST['kasa_id']       ?? 0);
    $odeme_tur_id  = isset($_POST['odeme_tur_id']) ? (int)$_POST['odeme_tur_id'] : 0;
    if(!$odeme_tur_id && isset($_POST['odeme_turu']) && ctype_digit((string)$_POST['odeme_turu'])){
        $odeme_tur_id = (int)$_POST['odeme_turu'];
    }
    $odeme_tutar   = nf($_POST['odeme_tutar'] ?? 0);
    $farkli_tutar  = (int)($_POST['farkli_tutar']  ?? 0); // 0/1
    $aciklama      = trim($_POST['aciklama'] ?? '');
    $created_by    = (int)($_SESSION['user_id'] ?? 0);

    if(!$ogrenci_id || !$taksit_id || !$kasa_id  || $odeme_tutar <= 0){
        jexit(['status'=>0,'message'=>'Eksik veya geçersiz parametre']);
     }

    /* -------- PDO -------- */
    $pdo = $Ydil->conn;
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

    /* -------- Seçilen taksiti doğrula -------- */
    $q = $pdo->prepare("
        SELECT id, ogrenci_id, satis_id, taksit_tutari, taksit_tarihi, odendi
          FROM taksitler
         WHERE id = :id
           AND ogrenci_id = :oid
           AND (odendi = 0 OR odendi IS NULL)
         LIMIT 1
    ");
    $q->execute([':id'=>$taksit_id, ':oid'=>$ogrenci_id]);
    $first = $q->fetch(PDO::FETCH_ASSOC);
    if(!$first){
        jexit(['status'=>0,'message'=>'Seçilen taksit açık değil (veya bu öğrenciye ait değil)']);
    }
    $satis_id = (int)$first['satis_id'];

    /* -------- Kuyruk --------
       Basit tutmak için her iki modda da kuyruk oluşturuyoruz.
       farkli_tutar=0 -> Önce seçilen taksit; taşarsa sıradakilere otomatik akar.
       farkli_tutar=1 -> Seçilen + aynı sözleşmedeki diğer açık taksitler sırayla.
    */
    $queue = [];
    $queue[] = ['id'=>(int)$first['id']];

    $q = $pdo->prepare("
        SELECT id
          FROM taksitler
         WHERE satis_id = :sid
           AND id <> :id
           AND (odendi = 0 OR odendi IS NULL)
         ORDER BY taksit_tarihi ASC, id ASC
    ");
    $q->execute([':sid'=>$satis_id, ':id'=>$taksit_id]);
    $others = $q->fetchAll(PDO::FETCH_COLUMN);

    if($others && ($farkli_tutar == 1 || $odeme_tutar > (float)$first['taksit_tutari'])){
        // farkli_tutar=1'de tümü, 0'da sadece taşma olursa kullanılacak şekilde dahil ediyoruz
        foreach($others as $oid){
            $queue[] = ['id'=>(int)$oid];
        }
    }

    /* -------- Hazır statement'lar -------- */
    $insOdm = $pdo->prepare("
    INSERT INTO odemeler
        (ogrenci_id, satis_id, taksit_id, kasa_id, odeme_tur_id, tutar, odeme_tarihi, aciklama, per_id, created_at)
    VALUES
        (:ogrenci_id, :satis_id, :taksit_id, :kasa_id, :odeme_tur_id, :tutar, NOW(), :aciklama, NULL, NOW())
");



    $closeTks = $pdo->prepare("
        UPDATE taksitler
           SET odendi = 1,
               odeme_tarihi = CURRENT_DATE(),
               odeme_tur_id = :odeme_tur_id
         WHERE id = :id
    ");

    $partTks = $pdo->prepare("
        UPDATE taksitler
           SET taksit_tutari = :kalan
         WHERE id = :id
    ");

    /* -------- Transaction & Dağıtım -------- */
    $pdo->beginTransaction();

    $remain  = (float)$odeme_tutar;
    $applied = [];

    foreach($queue as $t){
        if($remain <= 0) break;

        $tid = (int)$t['id'];

        // Güncel taksit tutarını oku (eşzamanlı değişikliklere karşı)
        $q = $pdo->prepare("SELECT taksit_tutari FROM taksitler WHERE id = :id LIMIT 1");
        $q->execute([':id'=>$tid]);
        $due = (float)$q->fetchColumn();
         

        if($due <= 0) continue;

        $pay = min($remain, $due);

        // Ödemeyi kaydet
        $insOdm->execute([
            ':ogrenci_id'   => $ogrenci_id,
            ':satis_id'     => $satis_id,
            ':taksit_id'    => $tid,
            ':kasa_id'      => $kasa_id,
            ':odeme_tur_id' => $odeme_tur_id,
            ':tutar'        => $pay,
            ':aciklama'     => $aciklama
        ]);

        $kalan = $due - $pay;


        if($kalan <= 0.000001){
            // Tam kapandı
            $closeTks->execute([':odeme_tur_id'=>$odeme_tur_id, ':id'=>$tid]);
            $applied[] = ['taksit_id'=>$tid, 'paid'=>$pay, 'remaining_after'=>0, 'closed'=>true];
        } else {
            // Kısmi ödeme
            $partTks->execute([':kalan'=>$kalan, ':id'=>$tid]);
            $applied[] = ['taksit_id'=>$tid, 'paid'=>$pay, 'remaining_after'=>$kalan, 'closed'=>false];
        }

        $remain -= $pay;


    }

    $pdo->commit();

    jexit([
        'status'      => 1,
        'message'     => 'Ödeme uygulandı',
        'total_paid'  => (float)$odeme_tutar,
        'applied'     => $applied,
        'leftover'    => max(0, $remain) // kuyruk biterse artan (bilgi amaçlı)
    ]);

} catch (Exception $e){
    if(isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    jexit(['status'=>0,'message'=>'Hata: '.$e->getMessage()]);
}