<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
include "c/fonk.php";
include "c/config.php";
include "c/user.php";
 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ogrid = (int)($_POST['ogrenciid'] ?? 0);
if ($ogrid <= 0) { die('Öğrenci seçilmedi'); }




// Öğrenci & veli
$ogrData = $Ydil->getone("
  SELECT o.*, v.*
  FROM ogrenci o
  INNER JOIN veli v ON v.ogrenci_id = o.ogrenci_id
  WHERE o.ogrenci_id = $ogrid
");

// --- kurs_satislari INSERT ---
$colSatis = ['ogrenci_id','kurs_adi','birim_id','miktar','birim_fiyat','toplam_tutar','pesinat_tutari','kalan_tutar','taksit_sayisi','satis_tarihi'];
$valSatis = [ $_POST['ogrenciid'] ?? null, $_POST['aciklama'] ?? null, $_POST['birim_id'] ?? null, $_POST['miktar'] ?? null, $_POST['birim_fiyat'] ?? null, $_POST['toplam_tutar'] ?? null, $_POST['pesinatTutari'] ?? null, $_POST['kalanTutar'] ?? null, $_POST['taksitSayisi'] ?? null, date('Y-m-d H:i:s') ];

$response  = $Ydil->newInsert("kurs_satislari", $colSatis, $valSatis);
$satis_id  = (int)($response['id'] ?? 0);


$birim_id   = (int)($_POST['birim_id'] ?? 0);
$miktar     = (int)($_POST['miktar'] ?? 0);
$ogrenci_id = (int)$ogrid;                  // mevcut değişkenin
$aylikkredi = (int)($_POST['birimsure'] ?? 0);
$kredi_aciklama = 'Kredi girişi';

$kredi_toplam = $miktar * $aylikkredi;
if ($kredi_toplam <= 0) { die('Geçersiz kredi değeri'); }

// satis_id daha önce oluşturduğun satış kaydından gelmeli:
$colKredi = ['ogrenci_id','satis_id','kredi_toplam','kredi_kalan','kredi_aciklama','created_at'];
$valKredi = [$ogrenci_id, $satis_id, $kredi_toplam, $kredi_toplam, $kredi_aciklama, date('Y-m-d H:i:s')];

$Ydil->newInsert('ogrenci_kredi', $colKredi, $valKredi);

if ($satis_id > 0) {
    $tarihArr   = $_POST['taksit_tarih'] ?? [];
    $tutarArr   = $_POST['taksit_tutar'] ?? [];
    $odemeArr   = $_POST['odeme_turu']   ?? []; // !! name="odeme_turu[]" olmalı

    foreach ($tarihArr as $key => $tarih) {
        $taksit_tutar = $tutarArr[$key] ?? null;
        $odeme_turu   = $odemeArr[$key] ?? null;

        // Örn: 'NAKİT', 'KREDİ KARTI', 'BANKA HAVALESİ', 'ÇEK-SENET'

        if ($odeme_turu === 'NAKİT') {
            $odeme_turu = 3;
        } elseif ($odeme_turu === 'KREDİ KARTI') {
            $odeme_turu = 4;
        } elseif ($odeme_turu === 'BANKA HAVALESİ') {
            $odeme_turu = 1;
        } elseif ($odeme_turu === 'ÇEK-SENET') {
            $odeme_turu = 2;
        } else {
            $odeme_turu = 0; // Tanımsızsa 0 yap
        }



        $colTaksit = ['ogrenci_id','satis_id','taksit_tutari','taksit_tarihi','odeme_tur_id','odendi'];
        $valTaksit = [$ogrid, $satis_id, $taksit_tutar, $tarih, $odeme_turu, 0];

        
        $Ydil->newInsert("taksitler", $colTaksit, $valTaksit);
    }

    // sözleşme verisini topla
    $kurs_satis = $Ydil->getone(" SELECT ks.*, o.*, v.* FROM kurs_satislari ks INNER JOIN ogrenci o ON o.ogrenci_id = ks.ogrenci_id INNER JOIN veli v    ON v.ogrenci_id = ks.ogrenci_id WHERE ks.id = $satis_id");
    $taksitler  = $Ydil->get("SELECT * FROM taksitler WHERE satis_id = $satis_id ORDER BY taksit_tarihi");

    $_SESSION['sozlesme_data'] = [
      'kurs_satis' => $kurs_satis,
      'taksitler'  => $taksitler,
    ];



    echo '<script>
window.addEventListener("load", function(){
  var modal=document.createElement("div");
  modal.style.cssText="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;justify-content:center;align-items:center;color:#fff;font:bold 18px/1.4 sans-serif";
  modal.innerHTML = \'<div style="background:#000;padding:20px;border-radius:10px">Sözleşme oluşturuluyor, lütfen bekleyiniz...</div>\';
  document.body.appendChild(modal);
  setTimeout(function(){ modal.remove(); window.open("sozlesme-cikti.php","_blank"); window.location.href = "ogrenci-listesi.php";  }, 1200);
});
</script>';
    exit;

} else {
    echo '<script>
      let modal=document.createElement("div");
      modal.style.cssText="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;justify-content:center;align-items:center;color:#fff;font:bold 18px/1.4 sans-serif";
      modal.innerHTML=`<div style="background:#c00;padding:20px;border-radius:10px">Hata oluştu! Ana sayfaya yönlendiriliyorsunuz...</div>`;
      document.body.appendChild(modal);
      setTimeout(()=>{ window.location.href="index.php"; }, 1500);
    </script>';
} ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Sqooler</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="nav-fixed">
        <?php include 'ekler/sidebar.php'; ?>
        <div id="layoutSidenav">
            <?php include 'ekler/menu.php'; ?>
            <div id="layoutSidenav_content">
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
