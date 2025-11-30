<?php
// sozlesme-ajax/sozlesme-kaydet.php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';

$db = new Ydil();
$pdo = $db->conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'msg'=>'Geçersiz istek']); exit;
}

// Güvenli al
$ogrenci_id   = (int)($_POST['ogrenci_id'] ?? 0);
$donem_id     = (int)($_POST['donem_id'] ?? 0);
$sinif_id     = (int)($_POST['sinif_id'] ?? 0);
$grup_id      = (int)($_POST['grup_id'] ?? 0);
$alan_id      = (int)($_POST['alan_id'] ?? 0);

$birim_id     = (int)($_POST['birim_id'] ?? 0);
$birim_fiyat  = (float)($_POST['birim_fiyat'] ?? 0);
$miktar       = (int)($_POST['miktar'] ?? 0);
$toplam_ucret = (float)($_POST['toplam_ucret'] ?? 0);

$pesinat      = (float)($_POST['pesinat'] ?? 0);
$yontem_id    = (int)($_POST['yontem_id'] ?? 0);
$kasa_id      = (int)($_POST['kasa_id'] ?? 0);

// Taksit listesi JSON: [{tutar: 10000.00, tarih: "2025-11-01"}, ...]
$taksit_json  = trim($_POST['taksitler'] ?? '[]');
$taksitler    = json_decode($taksit_json, true);

$sube_id      = (int)($_SESSION['sube_id'] ?? 0);
$personel_id  = (int)($_SESSION['personel_id'] ?? 0);

if (!$ogrenci_id || !$donem_id || !$alan_id || !$birim_id || $birim_fiyat<=0 || $miktar<=0 || $toplam_ucret<=0) {
    echo json_encode(['ok'=>false,'msg'=>'Eksik ya da hatalı veri']); exit;
}

if (!is_array($taksitler)) $taksitler = [];

$sum_taksit = 0.0;
$minVade    = null;
$maxVade    = null;

foreach ($taksitler as $i => $t) {
    $tutar = (float)($t['tutar'] ?? 0);
    $tarih = trim($t['tarih'] ?? '');
    if ($tutar <= 0 || !$tarih) {
        echo json_encode(['ok'=>false,'msg'=>'Taksit verisi hatalı']); exit;
    }
    $sum_taksit += $tutar;

    // yyyy-mm-dd bekliyoruz
    $d = date_create_from_format('Y-m-d', $tarih) ?: date_create($tarih);
    if (!$d) { echo json_encode(['ok'=>false,'msg'=>'Taksit tarihi geçersiz']); exit; }
    $ds = $d->format('Y-m-d');
    if ($minVade === null || $ds < $minVade) $minVade = $ds;
    if ($maxVade === null || $ds > $maxVade) $maxVade = $ds;

    // normalize
    $taksitler[$i]['tutar'] = round($tutar, 2);
    $taksitler[$i]['tarih'] = $ds;
}

$roundToplam    = round($toplam_ucret, 2);
$roundPesinat   = round($pesinat, 2);
$roundTaksitSum = round($sum_taksit, 2);

if ( round($roundPesinat + $roundTaksitSum, 2) !== $roundToplam ) {
    echo json_encode([
        'ok'=>false,
        'msg'=>"Peşinat + taksit toplamı ({$roundPesinat} + {$roundTaksitSum}) toplam ücrete ({$roundToplam}) eşit değil."
    ]);
    exit;
}

// Sözleşme no üret
function generateSozlesmeNo(PDO $pdo): string {
    $y = date('Ymd');
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM sozlesme1 WHERE DATE(olusturma_tarihi)=CURDATE()");
    $stmt->execute();
    $c = (int)$stmt->fetchColumn();
    return 'SZ-'.$y.'-'.str_pad((string)($c+1), 4, '0', STR_PAD_LEFT);
}

// Ödeme no üret
function generateOdemeNo(PDO $pdo): string {
    $y = date('Ymd');
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM odeme1 WHERE DATE(created_at)=CURDATE()");
    $stmt->execute();
    $c = (int)$stmt->fetchColumn();
    return 'OD-'.$y.'-'.str_pad((string)($c+1), 4, '0', STR_PAD_LEFT);
}

try {
    $pdo->beginTransaction();

    $sozlesme_no   = generateSozlesmeNo($pdo);
    $sozlesme_tarihi = date('Y-m-d');
    $baslangic_tarihi = $minVade ?: $sozlesme_tarihi;
    $bitis_tarihi     = $maxVade ?: $sozlesme_tarihi;

    // sozlesme1 insert
    $sqlS = "INSERT INTO sozlesme1
      (sozlesme_no, ogrenci_id, sube_id, per_id, donem_id, sinif_id, grup_id, alan_id, birim_id,
       sozlesme_tarihi, baslangic_tarihi, bitis_tarihi,
       toplam_ucret, indirim, net_ucret, taksit_sayisi, odeme_tipi, durum, aciklama,
       olusturan_kullanici_id, olusturma_tarihi)
      VALUES
      (:sozlesme_no, :ogrenci_id, :sube_id, :per_id, :donem_id, :sinif_id, :grup_id, :alan_id, :birim_id,
       :soz_tarih, :bas_tar, :bit_tar,
       :toplam, :indirim, :net, :taksit_say, :odeme_tipi, :durum, :aciklama,
       :olusturan, NOW())";

    $stmtS = $pdo->prepare($sqlS);
    $stmtS->execute([
        ':sozlesme_no' => $sozlesme_no,
        ':ogrenci_id'  => $ogrenci_id,
        ':sube_id'     => $sube_id ?: null,
        ':per_id'      => $personel_id ?: null,
        ':donem_id'    => $donem_id,
        ':sinif_id'    => $sinif_id ?: null,
        ':grup_id'     => $grup_id  ?: null,
        ':alan_id'     => $alan_id,
        ':birim_id'    => $birim_id,
        ':soz_tarih'   => $sozlesme_tarihi,
        ':bas_tar'     => $baslangic_tarihi,
        ':bit_tar'     => $bitis_tarihi,
        ':toplam'      => $roundToplam,
        ':indirim'     => 0.00,
        ':net'         => $roundToplam,
        ':taksit_say'  => count($taksitler),
        ':odeme_tipi'  => (count($taksitler)>0 ? 'TAKSIT' : 'PESIN'),
        ':durum'       => 'Aktif',
        ':aciklama'    => null,
        ':olusturan'   => $personel_id ?: null
    ]);
    $sozlesme_id = (int)$pdo->lastInsertId();

    // taksit1 insertleri
    if (count($taksitler) > 0) {
        $sqlT = "INSERT INTO taksit1
            (sozlesme_id, sira_no, vade_tarihi, tutar, odendi_tutar, durum, olusturma_tarihi)
            VALUES (:sid, :sira, :vade, :tutar, 0.00, 'Odenmedi', NOW())";
        $stmtT = $pdo->prepare($sqlT);

        $sira=1;
        foreach ($taksitler as $t) {
            $stmtT->execute([
                ':sid'  => $sozlesme_id,
                ':sira' => $sira++,
                ':vade' => $t['tarih'],
                ':tutar'=> round((float)$t['tutar'],2)
            ]);
        }
    }

    // Peşinat ödemesi varsa odeme1 + kasa_hareketleri1
    if ($roundPesinat > 0) {
        if (!$yontem_id || !$kasa_id) {
            throw new Exception('Peşinat için ödeme yöntemi ve kasa seçimi zorunludur.');
        }

        $odeme_no = generateOdemeNo($pdo);
        $sqlO = "INSERT INTO odeme1
           (odeme_no, sozlesme_id, kasa_id, yontem_id, tutar, odeme_tarihi, aciklama, personel_id, created_at)
           VALUES
           (:odeme_no, :sid, :kasa_id, :yontem_id, :tutar, NOW(), :aciklama, :pid, NOW())";
        $stmtO = $pdo->prepare($sqlO);
        $stmtO->execute([
            ':odeme_no' => $odeme_no,
            ':sid'      => $sozlesme_id,
            ':kasa_id'  => $kasa_id,
            ':yontem_id'=> $yontem_id,
            ':tutar'    => $roundPesinat,
            ':aciklama' => 'Sözleşme peşinatı',
            ':pid'      => $personel_id ?: null
        ]);
        $odeme_id = (int)$pdo->lastInsertId();

        // kasa_hareketleri1 (GİRİŞ)
        $sqlK = "INSERT INTO kasa_hareketleri1
          (kasa_id, odeme_id, sozlesme_id, ogrenci_id, yon, hareket_tipi, tutar, aciklama, hareket_tarihi, created_at, created_by)
          VALUES
          (:kasa_id, :odeme_id, :sid, :ogr_id, 'GIRIS', 'TAHSILAT', :tutar, :aciklama, NOW(), NOW(), :pid)";
        $stmtK = $pdo->prepare($sqlK);
        $stmtK->execute([
            ':kasa_id'  => $kasa_id,
            ':odeme_id' => $odeme_id,
            ':sid'      => $sozlesme_id,
            ':ogr_id'   => $ogrenci_id,
            ':tutar'    => $roundPesinat,
            ':aciklama' => 'Peşinat',
            ':pid'      => $personel_id
        ]);
    }

    $pdo->commit();

    unset($_SESSION['sozlesme_wizard']);

    echo json_encode([
        'ok' => true,
        'sozlesme_id' => $sozlesme_id,
        'redirect' => 'sozlesme-belge.php?id='.$sozlesme_id
    ]);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('SozlesmeKaydet HATA: '.$e->getMessage());
    echo json_encode(['ok'=>false,'msg'=>'Kayıt sırasında hata: '.$e->getMessage()]);
    exit;
}