<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$ogrencilers="SELECT * FROM `ogrenci` ORDER BY `ogrenci`.`ogrenci_id` DESC ";
$ogrenciler=$Ydil->get($ogrencilers);

$ogretmenlers="SELECT * FROM `ogretmen` ORDER BY `ogretmen`.`ogretmen_id` DESC ";
$ogretmenler=$Ydil->get($ogretmenlers);


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
                                <div class="card-header">Öğretmen - Öğrenci Seçimi</div>
                                <div class="card-body">
                                    <!-- 1. satır: Öğrenci ve Öğretmen (yan yana) -->
                                    <div class="row gx-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="ogrenci_id">Öğrenci Seçin</label>
                                            <select class="form-select select2" id="ogrenci_id" name="ogrenci_id" required>
                                                <option value="" disabled selected>Öğrenci seçiniz...</option>
                                                <?php foreach ($ogrenciler as $o): ?>
                                                    <option value="<?= (int)$o['ogrenci_id'] ?>">
                                                        <?= htmlspecialchars($o['ogrenci_adi'].' '.$o['ogrenci_soyadi']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="ogretmen_id">Öğretmen Seçin</label>
                                            <select class="form-select select2" id="ogretmen_id" name="ogretmen_id" required>
                                                <option value="" disabled selected>Öğretmen seçiniz...</option>
                                                <?php foreach ($ogretmenler as $og): ?>
                                                    <option value="<?= (int)$og['ogretmen_id'] ?>">
                                                        <?= htmlspecialchars($og['ogretmen_adi'].' '.$og['ogretmen_soyadi']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- 2. satır: Ders tarihi ve ders saati (yan yana) -->
                                    <div class="row gx-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="ders_tarihi">Ders Tarihi</label>
                                            <input class="form-control" id="ders_tarihi" name="ders_tarihi" type="date" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="ders_saati">Ders Saati</label>
                                            <input class="form-control" id="ders_saati" name="ders_saati" type="time" required>
                                        </div>
                                    </div>

                                    <!-- (Opsiyonel) Ders türü / konu -->
                                    <div class="row gx-3 mb-3">
                                        <div class="col-md-12">
                                            <label class="small mb-1" for="ders_turu">Ders Türü / Konu (opsiyonel)</label>
                                            <input class="form-control" id="ders_turu" name="ders_turu" type="text" placeholder="Örn: İngilizce - Konu: Speaking">
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
if($_POST["action_type"]=="add"){

    $ogrenci_id  = (int)($_POST['ogrenci_id']  ?? 0);
    $ogretmen_id = (int)($_POST['ogretmen_id'] ?? 0);
    $ders_tarihi = trim($_POST['ders_tarihi']  ?? '');   // YYYY-MM-DD
    $ders_saati  = trim($_POST['ders_saati']   ?? '');   // HH:MM
    $ders_turu   = trim($_POST['ders_turu']    ?? '');

    $columns[]='ogrenci_id';
    $columns[]='ogretmen_id';
    $columns[]='baslangic';
    $columns[]='saat';
    $columns[]='ders_turu';

    $values[]=$ogrenci_id;
    $values[]=$ogretmen_id;
    $values[]=$ders_tarihi;
    $values[]=$ders_saati;
    $values[]=$ders_turu;

    $response=$Ydil->newInsert("ogretmen_atama",$columns,$values);

    if ($response['status'] == 1) {
        $message = 'Kayıt başarılı, Anasayfaya sayfasına yönlendiriliyorsunuz.';
        $redirect = 'anasayfa.php'; // Yönlendirme yapılacak sayfa
    } else {
        // Hata durumu
        $message = $response['message']; // Hata mesajı
        $redirect = ''; // Hata durumunda yönlendirme yapılmayacak
    }
    if (isset($message)): ?>
        <script>
            // JavaScript ile mesajı ve yönlendirmeyi göster
            window.onload = function() {
                <?php if ($response['status'] == 1): ?>
                // Kayıt başarılı, yönlendirme yapılacak
                Swal.fire({
                    title: 'Başarılı!',
                    text: '<?php echo $message; ?>',
                    icon: 'success',
                    showConfirmButton: false, // "Tamam" butonunu gizle
                    timer: 3000 // 3 saniye sonra yönlendirme
                }).then(function() {
                    window.location.href = '<?php echo $redirect; ?>'; // Yönlendirme
                });
                <?php else: ?>
                // Hata durumunda gösterilecek
                Swal.fire({
                    title: 'Hata!',
                    text: '<?php echo $message; ?>',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
                <?php endif; ?>
            };
        </script>

    <?php endif; ?>
<?php  } ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="js/scripts.js"></script>

<!-- Select2 (aramalı select) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Bootstrap form-control yüksekliğine daha yakın görünüm
        $('.select2').select2({
            placeholder: 'Seçiniz',
            allowClear: true,
            width: '100%'
        });
    });
</script>
</body>
</html>
