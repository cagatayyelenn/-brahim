<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";


$gelendeger = $_GET['ogrid'];

if (empty($gelendeger)) {
  header("Location: anasayfa.php");
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {



    $birim_adi = $_POST['birimadi'] ?? null;
    $birim_id = $_POST['birim_id'] ?? null;
    $birim_fiyat = $_POST['birimTutar'] ?? null;
    $miktar = $_POST['miktar'] ?? null;
    $toplam_tutar = $birim_fiyat * $miktar;
    $taksitSayisi = $_POST['taksitSayisi'];
    $dateInput = $_POST['dateInput'];
    $toplamTutar1 = $_POST['toplamTutar1'];
    $pesinatTutari = $_POST['pesinatTutari'];
    $odeme_turu = $_POST['odeme_turu'];
    $aciklama = $_POST['aciklama'];
    $kasa_id = $_POST['kasa_id'];


    // Kalan tutarı hesapla
    $kalanTutar = $toplamTutar1 - $pesinatTutari;
    $taksitTutari = $kalanTutar / $taksitSayisi;

    // İlk taksit tarihini oluştur
    $currentDate = new DateTime($dateInput);



} else {
    echo "Bu sayfaya doğrudan erişemezsiniz!";
}

$ogrbilgisi = $_SESSION['student_id'];
$birimler="SELECT * FROM `birim` WHERE `birim_id` = $birim_id;";
$birims=$Ydil->get($birimler);

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

                                                    <input type="hidden" id="stdnt_id" value="<?= $ogrbilgisi;?>"/>
                                                    <input type="hidden" id="stdnt_alan_id" value="<?= $alan_id;?>"/>
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
                              <form method="post" action="b.php">
                                <input type="hidden" name="birimsure" value="<?= $birimss['ders_saat']; ?>"/>
                                <input type="hidden" name="birimadi" value="<?= $birim_adi; ?>"/>
                                <input type="hidden" name="birim_id" value="<?= $birim_id; ?>"/>
                                <input type="hidden" name="birim_fiyat" value="<?= $birim_fiyat; ?>"/>
                                <input type="hidden" name="miktar" value="<?= $miktar; ?>"/>
                                <input type="hidden" name="toplam_tutar" value="<?= $toplam_tutar; ?>"/>
                                <input type="hidden" name="pesinatTutari" value="<?= $pesinatTutari; ?>"/>
                                <input type="hidden" name="kalanTutar" value="<?= $kalanTutar; ?>"/>
                                <input type="hidden" name="ogrenciid" value="<?= $gelendeger; ?>"/>
                                <input type="hidden" name="aciklama" value="<?= $aciklama; ?>"/>
                                <input type="hidden" name="taksitSayisi" value="<?= $taksitSayisi; ?>"/>
                                <input type="hidden" name="kasa_id" value="<?= $kasa_id ; ?>"/>

                                <div class="col-lg-12 mb-8 " >
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white fw-bold">Taksit Sayısı</div>
                                        <div class="card border-start-lg border-start-primary h-100">
                                            <div class="card-body">
                                                <div class="table-responsive table-billing-history">
                                                    <table class="table mb-0" id="taksitTable">
                                                        <thead>
                                                            <tr>
                                                                <th class="border-gray-200" scope="col">Taksit No</th>
                                                                <th class="border-gray-200" scope="col">Tarih</th>
                                                                <th class="border-gray-200" scope="col">Tutar</th>
                                                                <th class="border-gray-200" scope="col">Ödeme Türü</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                          <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
                                                              <?php for ($i = 1; $i <= $taksitSayisi; $i++): ?>
                                                                  <tr>
                                                                      <td class="align-center" style="width: 10%;"><?= $i ?></td>
                                                                      <td style="width: 40%; border-right: 2px solid rgb(97, 96, 96);">
                                                                          <div class="d-flex justify-content-between align-items-center">
                                                                              <input type="date" class="form-control" name="taksit_tarih[]" value="<?= $currentDate->format('Y-m-d') ?>" style="width: 140px;" />
                                                                          </div>
                                                                      </td>
                                                                      <td style="width: 40%; border-right: 2px solid rgb(97, 96, 96);">
                                                                          <div class="d-flex justify-content-between align-items-center">
                                                                              <input type="text" class="form-control text-end" name="taksit_tutar[]" value="<?= number_format($taksitTutari, 2, '.', '') ?>" style="width: 140px; text-align: right;" />
                                                                          </div>
                                                                      </td>
                                                                      <td style="width: 40%;">
                                                                          <select class="form-control" name="odeme_turu[]" style="width: 182px;">
                                                                            <option value="NAKİT" <?php echo ($odeme_turu == "NAKİT") ? 'selected' : ''; ?>>NAKİT</option>
                                                                            <option value="KREDİ KARTI" <?php echo ($odeme_turu == "KREDİ KARTI") ? 'selected' : ''; ?>>KREDİ KARTI</option>
                                                                            <option value="BANKA HAVALESİ" <?php echo ($odeme_turu == "BANKA HAVALESİ") ? 'selected' : ''; ?>>BANKA HAVALESİ</option>
                                                                            <option value="ÇEK-SENET" <?php echo ($odeme_turu == "ÇEK-SENET") ? 'selected' : ''; ?>>ÇEK-SENET</option>
                                                                          </select>
                                                                      </td>
                                                                  </tr>
                                                                  <?php $currentDate->modify('+1 month'); // Bir sonraki aya geç ?>
                                                              <?php endfor; ?>
                                                          <?php endif; ?>
                                                      </tbody>
                                                    </table>
                                                    <hr>
                                                    <div class="row mb-12">
                                                        <div class="col-md-12 text-end">
                                                             <button class="btn btn-primary" type="submit">Sözleşme Oluştur</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                              </form>
                                <div class="card shadow-sm border-0 rounded-3 mb-4">
                                    <div class="card-header bg-primary text-white fw-bold">Sözleşme Tutarları</div>
                                    <div class="card-body">
                                        <div class="row mb-8 mt-4">
                                            <div class="col-md-4">
                                                <label class="fw-bold text-secondary">Birim Tutar</label>
                                                <input type="text" class="form-control text-end" id="birimTutar"  value="<?= $birim_fiyat; ?>" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold text-secondary">Tutar</label>
                                                <input type="text" class="form-control text-end" id="tutar" value="<?= $birim_fiyat; ?>" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold text-success">Toplam Tutar</label>
                                                <input type="text" class="form-control text-end text-success" id="toplamTutar1" value="<?= $toplam_tutar; ?>" disabled>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="text-primary fw-bold">Peşinat Bilgileri</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Peşinat Tutarı</label>
                                                <input type="text" class="form-control text-end" id="pesinatTutari" value="<?= $birim_fiyat; ?>" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Ödeme Türü</label>
                                                <select class="form-select" disabled>
                                                    <option selected disabled value="" >Ödeme Türü Seçiniz</option>
                                                    <option value="nakit">NAKİT</option>
                                                    <option value="kredikarti">KREDİ KARTI</option>
                                                    <option value="bankahavalesi">BANKA HAVALESİ</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Kasa</label>
                                                <select class="form-select" disabled>
                                                    <option selected disabled value="">Kasa Türü Seçiniz</option>
                                                    <option value="banka">BANKA KASA</option>
                                                    <option value="cek-senet">ÇEK-SENET KASA</option>
                                                    <option value="nakit">NAKİT KASA</option>
                                                    <option value="pos">POS KASA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="text-primary fw-bold">Taksit Bilgileri</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Taksit Toplamı</label>
                                                <input type="text" class="form-control text-end" id="taksitToplami" value="<?= $kalanTutar; ?>" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Taksit Sayısı</label>
                                                <input type="text" class="form-control text-end" id="taksitSayisi" value="<?= $taksitSayisi; ?>" disabled>
                                                <span class="text-danger">Taksit sayısı girmezse 1 olacaktır</span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Başlama Tarihi</label>
                                                <input type="date" class="form-control text-end" id="dateInput" value="<?= $dateInput; ?>" disabled>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row mb-12">
                                            <div class="col-md-12 text-end">
                                                <button class="btn btn-primary" type="button" id="btntaksit" disabled>Taksit Oluştur</button>
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
