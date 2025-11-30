<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$gelendeger = $_GET['ogrid'];

if (empty($gelendeger)) {
  header("Location: anasayfa.php");
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $birim_id = $_POST['birimadi'] ?? null;
    $birim_fiyat = $_POST['birim_fiyat'] ?? null;
    $miktar = $_POST['miktar'] ?? null;
    $toplam_tutar = $birim_fiyat * $miktar;

} else {
    echo "Bu sayfaya doğrudan erişemezsiniz!";
}

 

$birimler="SELECT * FROM `birim` WHERE `birim_id` = $birim_id;";
$birims=$Ydil->get($birimler);

$kasalar="SELECT * FROM `kasa` ORDER BY `kasa`.`id` ASC";
$kasas=$Ydil->get($kasalar);

$ogrler="SELECT * FROM `ogrenci` WHERE `ogrenci_id` = $gelendeger; ";
$ogrs=$Ydil->getone($ogrler);
$alan_id=$ogrs['alan_id'];

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Yeni Sözleşme Oluşturma</title>
        <link href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    </head>
    <body class="nav-fixed">
      <?php include 'ekler/sidebar.php'; ?>
        <div id="layoutSidenav">
            <?php include 'ekler/menu.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                     <div class="container-xl px-4 mt-5">
                        <div class="row">
                            <div class="col-xl-6 col-md-6 mb-4">
                                 <div class="card border-start-lg border-start-primary h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="small fw-bold text-primary mb-1">Öğrenci Bilgisi</div>
                                                <div class="h5"><b><?= $ogrs['ogrenci_adi'] ?> <?= $ogrs['ogrenci_soyadi'] ?></b> için yeni sözleşme 
                                                </div>
                                            </div>
                                            <div class="ms-2"><i class="fas fa-user-plus fa-2x text-gray-200"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-md-6 mb-4">
                                 <div class="card border-start-lg border-start-info h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="small fw-bold text-info mb-1">Sözleşme Bilgisi</div>
                                                <div class="h5"><b><?= $ogrs['ogrenci_adi'] ?></b> adlı öğrencinin daha önceden sözleşmesi bulunmamaktadır.</div>
                                            </div>
                                            <div class="ms-2"><i class="fas fa-save fa-2x text-gray-200"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                          <div class="row">
                            <div class="col-lg-3 mb-4">
                                <div class="card mb-4">
                                    <div class="card-header">Sözleşme Oluşturma</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="small mb-1">Birim</label>
                                            <select class="form-select" name="birimadi"  id="birimadi" aria-label="Default select example" disabled>
                                                 <?php foreach ($birims as $birimss ) {  ?>
                                                <option value="<?= $birimss['birim_id']; ?>"><?= $birimss['birim_adi']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="small mb-1">Birim Fiyatı</label>
                                            <input class="form-control" value="<?= $birim_fiyat; ?>" id="birimFiyat" type="text" placeholder="Lütfen Birim Fiyatı Giriniz" disabled/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="small mb-1">Miktar</label>
                                            <input class="form-control" value="<?= $miktar; ?>" id="miktar" type="text" placeholder="Lütfen Miktar Giriniz" disabled/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fw-bold text-success">Toplam Tutar</label>
                                            <input class="form-control text-end text-success" value="<?= $toplam_tutar; ?>" id="toplamTutar" type="text" disabled>
                                        </div>
                                        <button class="btn btn-primary" type="button" disabled>Sözleşme Oluştur</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9 mb-8" >
                              <form method="post" action="yenisozlesmetaksitbilgisi.php?ogrid=<?= $gelendeger; ?>">
                                <div class="card shadow-sm border-0 rounded-3 mb-4">
                                    <div class="card-header bg-primary text-white fw-bold">Sözleşme Tutarları</div>
                                    <div class="card-body">
                                        <div class="row mb-8 mt-4">
                                            <div class="col-md-4">
                                                <label class="fw-bold text-secondary">Birim Tutar</label>
                                                <input type="text" class="form-control text-end" name="birimTutar" id="birimTutar" value="<?= $birim_fiyat; ?>" readonly>
                                                <input type="hidden" name="birim_id" value="<?= $birim_id; ?>">
                                                <input type="hidden" name="miktar" value="<?= $miktar; ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold text-secondary">Tutar</label>
                                                <input type="text" class="form-control text-end" name="tutar" id="tutar" value="<?= $toplam_tutar; ?>" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold text-success">Toplam Tutar</label>
                                                <input type="text" class="form-control text-end text-success" name="toplamTutar1" id="toplamTutar1" value="<?= $toplam_tutar; ?>" readonly>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="text-primary fw-bold">Peşinat Bilgileri</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Peşinat Tutarı</label>
                                                <input type="text" class="form-control text-end" name="pesinatTutari" id="pesinatTutari">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Ödeme Türü</label>
                                                <select class="form-select" name="odeme_turu">
                                                    <option selected disabled value="">Ödeme Türü Seçiniz</option>
                                                    <option value="NAKİT">NAKİT</option>
                                                    <option value="KREDİ KARTI">KREDİ KARTI</option>
                                                    <option value="BANKA HAVALESİ">BANKA HAVALESİ</option>
                                                    <option value="ÇEK-SENET">ÇEK-SENET</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Kasa</label>
                                                <select class="form-select" name="kasa_id" required>
                                                    <option selected disabled value="">Kasa Türü Seçiniz</option>
                                                    <?php foreach($kasas as $kasa): ?>
                                                        <option value="<?= (int)$kasa['id']; ?>">
                                                            <?= htmlspecialchars($kasa['ad']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="text-primary fw-bold">Taksit Bilgileri</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Taksit Toplamı</label>
                                                <input type="text" class="form-control text-end" name="taksitToplami" id="taksitToplami" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Taksit Sayısı</label>
                                                <input type="text" class="form-control text-end" name="taksitSayisi" id="taksitSayisi">
                                                <span class="text-danger">Taksit sayısı girmezse 1 olacaktır</span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Başlama Tarihi</label>
                                                <input type="date" class="form-control text-end"  name="dateInput" id="dateInput">
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="text-primary fw-bold">Kurs Bilgisi</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="fw-bold">Açıklama</label>
                                                <input type="text" class="form-control" name="aciklama" id="aciklama" >
                                                <small class="text-danger">Sözleşme detayı hakkında bilgi giriniz. (Ör. Almanca Kursu)</small>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row mb-12">
                                            <div class="col-md-12 text-end">
                                                <button class="btn btn-primary" type="submit" >Taksit Oluştur</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                          </div>
                    </div>
                </main>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {

          // Sayı biçimlendirme fonksiyonu
          function formatNumber(value) {
              if (value) {
                  return value.toLocaleString('tr-TR');
              }
              return '';
          }

          // Peşinat tutarı düzenleme
          document.getElementById('pesinatTutari').addEventListener('input', function () {
              let pesinatTutari = this.value.replace(/[^\d]/g, ''); // Sadece sayıları al
              this.value = formatNumber(pesinatTutari);
          });

          // Taksit toplamı hesaplama
          document.getElementById('pesinatTutari').addEventListener('blur', function () {
              let toplamTutar1 = parseFloat(document.getElementById('toplamTutar1').value.replace(/[^\d]/g, '')) || 0;
              let pesinatTutari = parseFloat(this.value.replace(/[^\d]/g, '')) || 0;

              // Taksit toplamı
              let taksitToplami = toplamTutar1 - pesinatTutari;
              document.getElementById('taksitToplami').value = formatNumber(taksitToplami);
          });


          });

        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>-->
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="assets/demo/chart-pie-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
        <script src="js/litepicker.js"></script>
    </body>
</html>
