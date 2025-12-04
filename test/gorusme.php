<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Görüşme Listesi";

// Dilleri (Alanları) Çek
// test klasöründeki yapıya göre tablo isimleri değişmiş olabilir mi? 
// ogrenci-ekle.php'de 'il', 'ilce' kullanılmış. 'alan' tablosu var mı kontrol etmedim ama varsayıyorum.
// Eğer hata verirse düzeltiriz.
$alanlar = $db->finds('alan', null, null, ['alan_id', 'alan_adi']);

// Oturum açan kullanıcı
$gorusen_adi = "Sistem Kullanıcısı";
if (isset($_SESSION['ad']) && isset($_SESSION['soyad'])) {
    $gorusen_adi = $_SESSION['ad'] . " " . $_SESSION['soyad'];
} elseif (isset($_SESSION['kullanici_adi'])) {
    $gorusen_adi = $_SESSION['kullanici_adi'];
}

?>

<?php
$page_styles[] = ['href' => 'assets/css/animate.css'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
$page_styles[] = ['href' => 'assets/plugins/daterangepicker/daterangepicker.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>

<div class="page-wrapper">
    <div class="content content-two">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="mb-1">Görüşme Listesi</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Görüşme Listesi</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">

                <!-- Form Alanı -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center">
                            <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                                <i class="ti ti-users fs-16"></i>
                            </span>
                            <h4 class="text-dark">Görüşme Bilgileri</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <!-- Tarih -->
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="inputTarih">Tarih (Otomatik)</label>
                                    <input class="form-control" id="inputTarih" type="text"
                                        value="<?php echo date('d.m.Y'); ?>" readonly />
                                </div>
                                <!-- Adı -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputAd">Adi</label>
                                    <input class="form-control" id="inputAd" type="text" placeholder="Adı" />
                                </div>
                                <!-- Soyadı -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputSoyad">Soyadı</label>
                                    <input class="form-control" id="inputSoyad" type="text" placeholder="Soyadı" />
                                </div>
                                <!-- Dil (Alan) -->
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="selectDil">Dil</label>
                                    <select class="form-select select" id="selectDil">
                                        <option selected disabled>Seçiniz</option>
                                        <?php if ($alanlar): ?>
                                            <?php foreach ($alanlar as $alan) { ?>
                                                <option value="<?= $alan['alan_id']; ?>"><?= $alan['alan_adi']; ?></option>
                                            <?php } ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <!-- Referans -->
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="inputReferans">Referans</label>
                                    <input class="form-control" id="inputReferans" type="text" placeholder="Referans" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Açıklama -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputAciklama">Açıklama</label>
                                    <input class="form-control" id="inputAciklama" type="text" placeholder="Açıklama" />
                                </div>
                                <!-- Sonuç -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputSonuc">Sonuç</label>
                                    <input class="form-control" id="inputSonuc" type="text" placeholder="Sonuç" />
                                </div>
                                <!-- Görüşen -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputGorusen">Görüşen</label>
                                    <input class="form-control" id="inputGorusen" type="text"
                                        value="<?= $gorusen_adi ?>" readonly />
                                </div>
                            </div>

                            <!-- Butonlar -->
                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-primary me-2" type="button">Kaydet</button>
                                <button class="btn btn-success me-2" type="button">GÜNCELLE</button>
                                <button class="btn btn-danger" type="button">SİL</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste Alanı -->
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center">
                            <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                                <i class="ti ti-list fs-16"></i>
                            </span>
                            <h4 class="text-dark">Görüşme Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="datatablesSimple">
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script data-cfasync="false" src="assets/js/jquery-3.7.1.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap.bundle.min.js"></script>
<script data-cfasync="false" src="assets/js/feather.min.js"></script>
<script data-cfasync="false" src="assets/js/jquery.slimscroll.min.js"></script>
<script data-cfasync="false" src="assets/plugins/select2/js/select2.min.js"></script>
<script data-cfasync="false" src="assets/js/script.js"></script>
<script>
    (function () {
        if (!window.jQuery) return;
        jQuery(function ($) {
            if ($.fn.select2) $('.select').each(function () { $(this).select2({ width: '100%' }); });
            if (window.feather && feather.replace) feather.replace();
        });
    })();
</script>
</body>

</html>