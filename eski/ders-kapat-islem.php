<?php

include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$atamaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($atamaId <= 0) { http_response_code(400); exit('Geçersiz id'); }

$sql = "
SELECT
  oa.id,
  oa.ogrenci_id,
  oa.ogretmen_id,
  oa.baslangic,
  oa.saat,
  oa.ders_turu,
  CONCAT(o.ogrenci_adi,' ',o.ogrenci_soyadi)   AS ogrenci_adsoyad,
  o.ogrenci_tel,  o.ogrenci_mail,
  CONCAT(og.ogretmen_adi,' ',og.ogretmen_soyadi) AS ogretmen_adsoyad,
  og.ogretmen_tel, og.ogretmen_mail
FROM ogretmen_atama oa
JOIN ogrenci  o  ON o.ogrenci_id   = oa.ogrenci_id
JOIN ogretmen og ON og.ogretmen_id = oa.ogretmen_id
WHERE oa.id = {$atamaId}
LIMIT 1";
$atama = $Ydil->getone($sql);

$ogrenciAdSoyad  = $atama['ogrenci_adsoyad'];
$ogretmenAdSoyad = $atama['ogretmen_adsoyad'];
$dersTarih       = $atama['baslangic'];
$dersSaat        = $atama['saat'];
$dersTuru        = $atama['ders_turu'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title> NGLS Yabancı Dil Dünyası</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="js/sweetalert2.all.min.js"></script>
</head>
<body class="nav-fixed">
<?php include 'ekler/sidebar.php'; ?>
<div id="layoutSidenav">
    <?php include 'ekler/menu.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
                <div class="container-xl px-4">
                    <div class="page-header-content">
                        <div class="row align-items-center justify-content-between pt-3">
                            <div class="col-auto mb-3">
                                <h1 class="page-header-title">
                                    <div class="page-header-icon"><i data-feather="user-plus"></i></div>
                                    Öğrenci Ders Atama
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Main page content-->
            <div class="container-xl px-4 mt-4">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card mb-4">
                                <div class="card-header">Ders İşleme Alanı</div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6"><b>Öğrenci:</b> <?= $ogrenciAdSoyad  ?></div>
                                        <div class="col-md-6"><b>Öğretmen:</b> <?= $ogretmenAdSoyad ?></div>
                                    </div>

                                    <div class="row gx-3 mb-3">
                                        <div class="col-md-12">
                                            <label class="small mb-1" for="ders_konu">Ders Türü / Konu <span class="text-danger">*</span></label>
                                            <input class="form-control" id="ders_konu" name="ders_konu" type="text" placeholder="Örn: İngilizce - Konu: Speaking" required>
                                        </div>
                                    </div>

                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="chkEdit">
                                        <label class="form-check-label" for="chkEdit">Ders detaylarını düzenle (tarih/saat)</label>
                                    </div>
                                    <div class="row gx-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="ders_tarihi">Ders Tarihi</label>
                                            <input class="form-control" id="ders_tarihi" name="ders_tarihi" type="date">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="ders_saati">Ders Saati</label>
                                            <input class="form-control" id="ders_saati" name="ders_saati" type="time">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card mb-4 mb-xl-0">
                                <div class="card-header">İşlem Bilgisi</div>
                                <div class="card-body">
                                    <input id="action_type" type="hidden" name="action_type" value="add"/>
                                    <button class="btn btn-primary w-100" type="submit">Ders Atamasını Kaydet</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>


            </div>
        </main>
        <footer class="footer-admin mt-auto footer-light">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website 2021</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a>
                        &middot;
                        <a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
<?php
if($_POST["action_type"] == "add") {

    // Checkbox kontrolü - ders tarih/saat nereden alınacak?
    $editMode = isset($_POST['chkEdit']) ? true : false;

    if($editMode) {
        // Form'dan tarih/saat al
        $dersTarihi = $_POST['ders_tarihi'];
        $dersSaati = $_POST['ders_saati'];
        $dersTarihSaat = $dersTarihi . ' ' . $dersSaati;
    } else {
        // ogretmen_atama tablosundan tarih/saat al
        $atamaSql = "SELECT baslangic, saat FROM ogretmen_atama WHERE id = $atamaId";
        $atamaData = $Ydil->getone($atamaSql);
        $dersTarihSaat = $atamaData['baslangic']; // baslangic zaten datetime formatında olduğunu varsayıyorum
        $dersSureDakika = $atamaData['saat'] * 60;
    }

    // Ana veri çekme sorgusu
    $sql = "
    SELECT 
        oa.ogrenci_id,
        oa.ogretmen_id,
        ks.id as satis_id,
        oa.saat * 60 as sure_dakika,
        1 as kredi_kullanimi,
        CONCAT('Öğretmen: ', og.ogretmen_adi, ' ', og.ogretmen_soyadi, ' - Öğrenci: ', o.ogrenci_adi, ' ', o.ogrenci_soyadi) as aciklama,
        ks.miktar as kredi_toplam,
        (ks.miktar - 1) as kredi_kalan,
        CONCAT('Kurs: ', ks.kurs_adi, ' - Toplam ', ks.miktar, ' kredi') as kredi_aciklama,
        og.saat_ucreti as tutar_birim,
        (1 * og.saat_ucreti) as tutar_toplam
    FROM ogretmen_atama oa
    INNER JOIN ogrenci o ON oa.ogrenci_id = o.ogrenci_id
    INNER JOIN ogretmen og ON oa.ogretmen_id = og.ogretmen_id
    LEFT JOIN kurs_satislari ks ON oa.ogrenci_id = ks.ogrenci_id
    WHERE oa.id = $atamaId
    ORDER BY ks.satis_tarihi DESC
    LIMIT 1";

    $data = $Ydil->getone($sql);

    // 1. DERS_KAYDI ekle
    $derscoll = ['ogrenci_id', 'ogretmen_id', 'satis_id', 'ders_tarih', 'sure_dakika', 'konu', 'kredi_kullanimi', 'aciklama', 'created_by', 'created_at'];
    $dersval = [
        $data['ogrenci_id'],
        $data['ogretmen_id'],
        $data['satis_id'],
        $dersTarihSaat,
        $editMode ? (60) : $data['sure_dakika'], // Edit mode'da 60 dakika varsayılan
        $_POST['ders_konu'],
        $data['kredi_kullanimi'],
        $data['aciklama'],
        1,
        date('Y-m-d H:i:s')
    ];


    $dersId = $Ydil->newInsert('ders_kaydi', $derscoll, $dersval);

    $ders_id  = (int)($dersId['id'] ?? 0);

    // 2. OGRENCI_KREDI ekle
    $ogrencikreditcoll = ['ogrenci_id', 'satis_id', 'kredi_toplam', 'kredi_kalan', 'kredi_aciklama', 'created_at'];
    $ogrencikreditval = [
        $data['ogrenci_id'],
        $data['satis_id'],
        $data['kredi_toplam'],
        $data['kredi_kalan'],
        $data['kredi_aciklama'],
        date('Y-m-d H:i:s')
    ];

    $kreditId = $Ydil->newInsert('ogrenci_kredi', $ogrencikreditcoll, $ogrencikreditval);

    // 3. OGRETMEN_HESAP ekle
    $ogretmenhesapcoll = ['ogretmen_id', 'ders_id', 'kredi_adet', 'tutar_birim', 'tutar_toplam', 'durum', 'created_at'];
    $ogretmenhesapval = [
        $data['ogretmen_id'],
        $ders_id,
        (int)$data['kredi_kullanimi'],
        (float)$data['tutar_birim'],
        (float)$data['tutar_toplam'],
        'BEKLEMEDE',
        date('Y-m-d H:i:s')
    ];

     
    $hesapId = $Ydil->newInsert('ogretmen_hesap', $ogretmenhesapcoll, $ogretmenhesapval);

    // 4. OGRETMEN_ATAMA güncelle (kapatma)
    $updateSql = "UPDATE ogretmen_atama SET kapandi = 1, kapandi_at = NOW() WHERE id = $atamaId";
    $Ydil->getone($updateSql);

    // Sonuç kontrolü
    if($dersId && $kreditId && $hesapId) {
        echo '<script>
                  Swal.fire({
                      title: "Başarılı",
                      text: "Ders Başarılı olarak İlendi",
                      icon: "success",
                      confirmButtonText: "Tamam"
                  }).then(() => {
                      window.location.href = "/anasayfa.php";
                  });
              </script>';
    } else {
        echo '<script>
                  Swal.fire({
                      title: "Başarısız",
                      text: "Ders kapanırken hata oluştu",
                      icon: "error",
                      confirmButtonText: "Tamam"
                  }).then(() => {
                      window.location.href = "/anasayfa.php";
                  });
              </script>';
    }
}
?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="js/scripts.js"></script>

<!-- Select2 (aramalı select) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    (function () {
        const chk   = document.getElementById('chkEdit');
        const dateI = document.getElementById('ders_tarihi');
        const timeI = document.getElementById('ders_saati');

        function toggleEdit() {
            const on = chk.checked;
            // aktif değilse: dokunulmasın
            dateI.disabled = !on;
            timeI.disabled = !on;
            dateI.required =  on;
            timeI.required =  on;
            // görsel ipucu (isteğe bağlı)
            [dateI, timeI].forEach(el => {
                el.classList.toggle('bg-light', !on);
                el.classList.toggle('border-0', !on);
            });
        }

        // ilk yüklemede kapalı başlat (değiştirme yok)
        chk.checked = false;
        toggleEdit();

        chk.addEventListener('change', toggleEdit);
    })();
</script>
</body>
</html>
