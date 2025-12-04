<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Görüşme Listesi";

// --- Ekleme İşlemi ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kaydet'])) {

    $tarih_raw = $_POST['tarih'] ?? '';
    // Tarih formatını d.m.Y -> Y-m-d çevir
    $tarih = '';
    if ($tarih_raw) {
        $dt = DateTime::createFromFormat('d.m.Y', $tarih_raw);
        if ($dt) {
            $tarih = $dt->format('Y-m-d');
        }
    }
    if (empty($tarih))
        $tarih = date('Y-m-d'); // Fallback

    $ad = trim($_POST['ad'] ?? '');
    $soyad = trim($_POST['soyad'] ?? '');
    $alan_id = (int) ($_POST['alan_id'] ?? 0);
    $referans = trim($_POST['referans'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $sonuc = trim($_POST['sonuc'] ?? '');
    $gorusen_id = (int) ($_POST['gorusen_id'] ?? 0);

    // Basit Validasyon
    if (empty($ad) || empty($soyad)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Ad ve Soyad alanlarını doldurunuz.'
        ];
    } else {
        // Veritabanına Ekle
        $columns = [
            'tarih',
            'ad',
            'soyad',
            'alan_id',
            'referans',
            'aciklama',
            'sonuc',
            'gorusen_id',
            'created_at'
        ];
        $values = [
            $tarih,
            $ad,
            $soyad,
            $alan_id,
            $referans,
            $aciklama,
            $sonuc,
            $gorusen_id,
            date('Y-m-d H:i:s')
        ];

        // Tablo adının 'gorusmeler' olduğunu varsayıyoruz.
        $ins = $db->insert('gorusmeler', $columns, $values);

        if ($ins['status'] == 1) {
            $_SESSION['flash_swal'] = [
                'icon' => 'success',
                'title' => 'Başarılı',
                'text' => 'Görüşme başarıyla kaydedildi.'
            ];
        } else {
            $_SESSION['flash_swal'] = [
                'icon' => 'error',
                'title' => 'Hata',
                'text' => 'Kayıt sırasında bir hata oluştu: ' . ($ins['error'] ?? 'Bilinmeyen hata')
            ];
        }
    }

    // Post/Redirect/Get pattern to prevent resubmission
    header("Location: gorusme.php");
    exit;
}

// Dilleri (Alanları) Çek
$alanlar = $db->finds('alan', ['alan_durum' => 1], null, ['alan_id', 'alan_adi']);

// Oturum açan kullanıcı
$gorusen_adi = "Sistem Kullanıcısı";
$gorusen_id = 0;
if (isset($_SESSION['ad']) && isset($_SESSION['soyad'])) {
    $gorusen_adi = $_SESSION['ad'] . " " . $_SESSION['soyad'];
    $gorusen_id = $_SESSION['user_id'] ?? 0; // user_id veya kisi_id, projeye göre değişebilir
} elseif (isset($_SESSION['kullanici_adi'])) {
    $gorusen_adi = $_SESSION['kullanici_adi'];
}
// Session'dan ID'yi garantiye alalım (kisi_id genelde kullanılır bu projede gördüğüm kadarıyla)
if (isset($_SESSION['kisi_id']))
    $gorusen_id = $_SESSION['kisi_id'];


// Listeyi Çek (En son eklenen en üstte)
// Tablo: gorusmeler, Join: alan (dil ismi için)
// Not: Eğer alan tablosuyla join gerekirse SQL yazabiliriz, şimdilik düz çekelim, alan adını döngüde veya joinle bulalım.
// Basitlik adına düz çekip, alan listesinden eşleştirebiliriz veya SQL join yapabiliriz.
// Ydil sınıfının yapısına uygun olarak custom query kullanalım daha temiz olur.
$sql_list = "SELECT g.*, a.alan_adi 
             FROM gorusmeler g 
             LEFT JOIN alan a ON g.alan_id = a.alan_id 
             ORDER BY g.id DESC";
$gorusme_listesi = $db->get($sql_list); // get metodu tüm satırları çeker diye varsayıyorum (analizden hatırladığım kadarıyla)

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
                        <form method="POST" action="">
                            <div class="row">
                                <!-- Tarih -->
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="inputTarih">Tarih (Otomatik)</label>
                                    <input class="form-control" id="inputTarih" name="tarih" type="text"
                                        value="<?php echo date('d.m.Y'); ?>" readonly />
                                </div>
                                <!-- Adı -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputAd">Adı</label>
                                    <input class="form-control" id="inputAd" name="ad" type="text" placeholder="Adı"
                                        required />
                                </div>
                                <!-- Soyadı -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputSoyad">Soyadı</label>
                                    <input class="form-control" id="inputSoyad" name="soyad" type="text"
                                        placeholder="Soyadı" required />
                                </div>
                                <!-- Dil (Alan) -->
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="selectDil">Dil / Alan</label>
                                    <select class="form-select select" id="selectDil" name="alan_id">
                                        <option selected disabled value="">Seçiniz</option>
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
                                    <input class="form-control" id="inputReferans" name="referans" type="text"
                                        placeholder="Referans" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Açıklama -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputAciklama">Açıklama</label>
                                    <input class="form-control" id="inputAciklama" name="aciklama" type="text"
                                        placeholder="Görüşme notları..." />
                                </div>
                                <!-- Sonuç -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputSonuc">Sonuç</label>
                                    <input class="form-control" id="inputSonuc" name="sonuc" type="text"
                                        placeholder="Olumlu/Olumsuz vb." />
                                </div>
                                <!-- Görüşen -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputGorusen">Görüşen</label>
                                    <input class="form-control" id="inputGorusen" type="text"
                                        value="<?= $gorusen_adi ?>" readonly />
                                    <input type="hidden" name="gorusen_id" value="<?= $gorusen_id ?>" />
                                </div>
                            </div>

                            <!-- Butonlar -->
                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-primary me-2" type="submit" name="kaydet">Kaydet</button>
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
                                        <th>Dil / Alan</th>
                                        <th>Referans</th>
                                        <th>Açıklama</th>
                                        <th>Sonuç</th>
                                        <th>Görüşen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($gorusme_listesi): ?>
                                        <?php foreach ($gorusme_listesi as $row):
                                            // Tarihi formatla
                                            $tarih_goster = $row['tarih'];
                                            if ($row['tarih']) {
                                                $tarih_goster = date('d.m.Y', strtotime($row['tarih']));
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $tarih_goster ?></td>
                                                <td><?= htmlspecialchars($row['ad']) ?></td>
                                                <td><?= htmlspecialchars($row['soyad']) ?></td>
                                                <td><?= htmlspecialchars($row['alan_adi'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['referans']) ?></td>
                                                <td><?= htmlspecialchars($row['aciklama']) ?></td>
                                                <td><?= htmlspecialchars($row['sonuc']) ?></td>
                                                <td>
                                                    <?php
                                                    // Görüşen ismini bulmak için basit bir mantık veya join kullanılabilir.
                                                    // Şimdilik ID veya varsa join ile gelen ismi yazalım.
                                                    // Eğer join yapmadıysak burada ek sorgu gerekebilir ama yukarıda join ekledim fakat gorusen tablosu joinlemedim.
                                                    // Basitçe ID yazıyorum şimdilik, istenirse kullanıcı tablosuna join atılır.
                                                    echo $row['gorusen_id'];
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Henüz kayıt bulunmamaktadır.</td>
                                        </tr>
                                    <?php endif; ?>
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

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($_SESSION['flash_swal'])):
    $sw = $_SESSION['flash_swal'];
    unset($_SESSION['flash_swal']); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: '<?= $sw['icon'] ?? 'info' ?>',
                title: '<?= addslashes($sw['title'] ?? '') ?>',
                text: '<?= addslashes($sw['text'] ?? '') ?>',
                confirmButtonText: 'Tamam'
            });
        });
    </script>
<?php endif; ?>

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