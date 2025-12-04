<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

// Dilleri (Alanları) Çek
$alanlar = "SELECT * FROM `alan` ORDER BY `alan`.`alan_id` ASC";
$alans = $Ydil->get($alanlar);

// Oturum açan kullanıcı bilgisini çek (Görüşen için)
// Basitçe session'dan id alıp ismini bulabiliriz, şimdilik placeholder veya session id kullanacağız.
// Eğer kullanıcı adı session'da yoksa, veritabanından çekilebilir.
$gorusen_adi = "Sistem Kullanıcısı"; // Varsayılan
if (isset($_SESSION['user_id'])) {
    // Kullanıcı adını çekmek için sorgu eklenebilir.
    // Şimdilik statik bırakıyorum veya basitçe ID yazdırıyorum.
    // $gorusen_adi = $_SESSION['user_id']; 
}

?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Görüşme Listesi - Sqooler</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer
        src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js"
        crossorigin="anonymous"></script>
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
                                        <div class="page-header-icon"><i data-feather="users"></i></div>
                                        Görüşme Listesi
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <!-- Main page content-->
                <div class="container-xl px-4 mt-4">
                    <!-- Form Alanı -->
                    <div class="card mb-4">
                        <div class="card-header">Görüşme Bilgileri</div>
                        <div class="card-body">
                            <form>
                                <div class="row gx-3 mb-3">
                                    <!-- Tarih -->
                                    <div class="col-md-2">
                                        <label class="small mb-1" for="inputTarih">Tarih (Otomatik)</label>
                                        <input class="form-control" id="inputTarih" type="text"
                                            value="<?php echo date('d.m.Y'); ?>" readonly />
                                    </div>
                                    <!-- Adı -->
                                    <div class="col-md-3">
                                        <label class="small mb-1" for="inputAd">Adi</label>
                                        <input class="form-control" id="inputAd" type="text" placeholder="Adı" />
                                    </div>
                                    <!-- Soyadı -->
                                    <div class="col-md-3">
                                        <label class="small mb-1" for="inputSoyad">Soyadı</label>
                                        <input class="form-control" id="inputSoyad" type="text" placeholder="Soyadı" />
                                    </div>
                                    <!-- Dil (Alan) -->
                                    <div class="col-md-2">
                                        <label class="small mb-1" for="selectDil">Dil</label>
                                        <select class="form-select" id="selectDil">
                                            <option selected disabled>Seçiniz</option>
                                            <?php foreach ($alans as $alan) { ?>
                                                <option value="<?= $alan['alan_id']; ?>"><?= $alan['alan_adi']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <!-- Referans -->
                                    <div class="col-md-2">
                                        <label class="small mb-1" for="inputReferans">Referans</label>
                                        <input class="form-control" id="inputReferans" type="text"
                                            placeholder="Referans" />
                                    </div>
                                </div>

                                <div class="row gx-3 mb-3">
                                    <!-- Açıklama -->
                                    <div class="col-md-6">
                                        <label class="small mb-1" for="inputAciklama">Açıklama</label>
                                        <input class="form-control" id="inputAciklama" type="text"
                                            placeholder="Açıklama" />
                                    </div>
                                    <!-- Sonuç -->
                                    <div class="col-md-3">
                                        <label class="small mb-1" for="inputSonuc">Sonuç</label>
                                        <input class="form-control" id="inputSonuc" type="text" placeholder="Sonuç" />
                                    </div>
                                    <!-- Görüşen -->
                                    <div class="col-md-3">
                                        <label class="small mb-1" for="inputGorusen">Görüşen</label>
                                        <input class="form-control" id="inputGorusen" type="text"
                                            value="<?= $gorusen_adi ?>" readonly />
                                    </div>
                                </div>

                                <!-- Butonlar -->
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary me-2" type="button">Kaydet</button>
                                    <button class="btn btn-success me-2" type="button">GÜNCELLE</button>
                                    <button class="btn btn-danger" type="button">SİL</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Liste Alanı -->
                    <div class="card mb-4">
                        <div class="card-header">Görüşme Listesi</div>
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Adı</th>
                                        <th>Soyadı</th>
                                        <th>Dil</th>
                                        <th>Referans</th>
                                        <th>Açıklama</th>
                                        <th>Sonuç</th>
                                        <th>Görüşen</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Adı</th>
                                        <th>Soyadı</th>
                                        <th>Dil</th>
                                        <th>Referans</th>
                                        <th>Açıklama</th>
                                        <th>Sonuç</th>
                                        <th>Görüşen</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <!-- Örnek Veri -->
                                    <tr>
                                        <td><?php echo date('d.m.Y'); ?></td>
                                        <td>Ahmet</td>
                                        <td>Yılmaz</td>
                                        <td>İngilizce</td>
                                        <td>Google</td>
                                        <td>Bilgi aldı</td>
                                        <td>Olumlu</td>
                                        <td>Sistem Yöneticisi</td>
                                    </tr>
                                    <!-- Veritabanından veri çekme işlemi daha sonra eklenecek -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">Copyright &copy; Sqooler 2025</div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="js/datatables/datatables-simple-demo.js"></script>
</body>

</html>