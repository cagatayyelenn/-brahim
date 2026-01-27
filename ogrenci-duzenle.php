<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

$pageTitle = "Öğrenci Düzenle";

// -------------------
// 1) Giriş ve mevcut kayıt
// -------------------
if (!isset($_GET['id']) || $_GET['id'] === '') {
    header("Location: ogrenci-listesi.php");
    exit;
}
$ogr_no = trim($_GET['id']); // ogrenci_numara

// İl/İlçe seçenekleri
$iller   = $db->finds('il',   null, null, ['il_id','il_adi']);
$ilceler = $db->finds('ilce', null, null, ['ilce_id','ilce_adi','il_id']);

// Öğrenciyi çek
$sql = "
  SELECT o.*
  FROM ogrenci1 o
  WHERE o.ogrenci_numara = :num
  LIMIT 1
";
$ogr = $db->gets($sql, [':num'=>$ogr_no]);

if (!$ogr) {
    $_SESSION['flash_swal'] = [
        'icon'  => 'warning',
        'title' => 'Bulunamadı',
        'text'  => 'Öğrenci kaydı bulunamadı.'
    ];
    header("Location: ogrenci-listesi.php");
    exit;
}
$ogrenci_id = (int)$ogr['ogrenci_id'];

// Mevcut veli (varsa)
$veli = $db->gets("SELECT * FROM veli1 WHERE ogrenci_id = :oid LIMIT 1", [':oid'=>$ogrenci_id]);

// -------------------
// 2) POST (GÜNCELLEME)
// -------------------

$defaultBack = 'ogrenci-listesi.php';
$backUrl = $defaultBack;

if (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    // Yalnızca aynı hosttan gelmişse kabul et (açık yönlendirmeyi önlemek için)
    $refHost = parse_url($ref, PHP_URL_HOST);
    if ($refHost === null || $refHost === $_SERVER['HTTP_HOST']) {
        $backUrl = $ref;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student_update'])) {

    // Temel alanlar
    $ogrenci_tc       = trim($_POST['ogrenci_tc'] ?? '');
    $ogrenci_adi      = trim($_POST['ogrenci_adi'] ?? '');
    $ogrenci_soyadi   = trim($_POST['ogrenci_soyadi'] ?? '');
    $ogrenci_tel      = trim($_POST['ogrenci_tel'] ?? '');
    $ogrenci_mail     = trim($_POST['ogrenci_mail'] ?? '');
    $ogrenci_cinsiyet = $_POST['ogrenci_cinsiyet'] ?? '';
    $ogrenci_dogumtar = trim($_POST['ogrenci_dogumtar'] ?? '');
    $il_id            = (int)($_POST['il_id']   ?? 0);
    $ilce_id          = (int)($_POST['ilce_id'] ?? 0);
    $ogrenci_adres    = trim($_POST['ogrenci_adres'] ?? '');
    $aktif            = isset($_POST['aktif']) ? 1 : 0;

    // Veli bölümü
    $veli_durumu = $_POST['veli_durumu'] ?? '1'; // 1: Kendisi, 0: Farklı
    $veli_tc     = trim($_POST['veli_tc']     ?? '');
    $veli_adi    = trim($_POST['veli_adi']    ?? '');
    $veli_soyadi = trim($_POST['veli_soyadi'] ?? '');
    $veli_tel    = trim($_POST['veli_tel']    ?? '');
    $veli_mail   = trim($_POST['veli_mail']   ?? '');
    $veli_adres  = trim($_POST['veli_adres']  ?? '');

    // Validasyon
    $hata = '';
    if ($ogrenci_adi === '' || $ogrenci_soyadi === '') {
        $hata = 'Ad ve soyad zorunludur.';
    } elseif ($ogrenci_mail !== '' && !filter_var($ogrenci_mail, FILTER_VALIDATE_EMAIL)) {
        $hata = 'E-posta formatı geçersiz.';
    }
    if ($hata === '' && $veli_durumu === '0') {
        if ($veli_adi === '' || $veli_soyadi === '') {
            $hata = 'Veli adı ve soyadı zorunludur.';
        } elseif ($veli_mail !== '' && !filter_var($veli_mail, FILTER_VALIDATE_EMAIL)) {
            $hata = 'Veli e-posta formatı geçersiz.';
        }
    }

    if ($hata !== '') {
        $_SESSION['flash_swal'] = [
            'icon'  => 'warning',
            'title' => 'Doğrulama',
            'text'  => $hata
        ];
        header("Location: ogrenci-duzenle.php?id=".urlencode($ogr_no));
        exit;
    }

    // GÜNCELLE (ogrenci_numara ile)
    $cols = [
        'ogrenci_tc','ogrenci_adi','ogrenci_soyadi',
        'ogrenci_tel','ogrenci_mail','ogrenci_cinsiyet','ogrenci_dogumtar',
        'il_id','ilce_id','ogrenci_adres','aktif'
    ];
    $vals = [
        $ogrenci_tc,$ogrenci_adi,$ogrenci_soyadi,
        $ogrenci_tel,$ogrenci_mail,$ogrenci_cinsiyet,$ogrenci_dogumtar,
        $il_id,$ilce_id,$ogrenci_adres,$aktif
    ];
    $upd = $db->update('ogrenci1', $cols, $vals, 'ogrenci_numara', $ogr_no);

    // Veli işlemleri
    if ($veli_durumu === '1') {
        // Kendisi: varsa veli kaydını sil
        $db->delete('veli1', $ogrenci_id, 'ogrenci_id');
    } else {
        if ($veli) {
            // Update
            $vCols = ['veli_adi','veli_soyadi','veli_tc','veli_tel','veli_mail','veli_adres'];
            $vVals = [$veli_adi,  $veli_soyadi,  $veli_tc,  $veli_tel,  $veli_mail,  $veli_adres];
            $db->update('veli1', $vCols, $vVals, 'ogrenci_id', $ogrenci_id);
        } else {
            // Insert
            $vCols = ['ogrenci_id','veli_adi','veli_soyadi','veli_tc','veli_tel','veli_mail','veli_adres'];
            $vVals = [$ogrenci_id,  $veli_adi,  $veli_soyadi,  $veli_tc,  $veli_tel,  $veli_mail,  $veli_adres];
            $db->insert('veli1', $vCols, $vVals);
        }
    }

    if (!empty($upd['status']) && $upd['status'] == 1) {
        $_SESSION['flash_swal'] = [
            'icon'  => 'success',
            'title' => 'Başarılı',
            'text'  => 'Öğrenci kaydı başarıyla güncellendi.'
        ];
        header("Location: ogrenci-detay.php?id=".urlencode($ogr_no));
        exit;
    } else {
        $_SESSION['flash_swal'] = [
            'icon'  => 'error',
            'title' => 'Hata',
            'text'  => 'Güncelleme sırasında bir hata oluştu.'
        ];
        header("Location: ogrenci-duzenle.php?id=".urlencode($ogr_no));
        exit;
    }
}

// -------------------
// 3) SAYFA (FORM)
// -------------------
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';

// Yardımcı kısa değişkenler (formu doldurmak için)
function h($v){ return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<div class="page-wrapper">
    <div class="content content-two">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="mb-1">Öğrenci Düzenle</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                        <li class="breadcrumb-item"><a href="ogrenci-listesi.php">Öğrenciler</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Düzenle</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="badge bg-primary">Öğrenci No: <?= h($ogr_no) ?></span>

                <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Geri Dön
                </a>
            </div>
        </div>

        <form action="ogrenci-duzenle.php?id=<?= urlencode($ogr_no) ?>" method="POST" id="ogrenci-duzenle-form">

            <!-- Kişisel Bilgiler -->
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                        <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                            <i class="ti ti-info-square-rounded fs-16"></i>
                        </span>
                        <h4 class="text-dark">Kişisel Bilgiler</h4>
                    </div>
                </div>
                <div class="card-body pb-1">

                    <div class="row row-cols-xxl-5 row-cols-md-6">

                        <div class="col-xxl col-xl-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="ogrenci_tc">T.C. / Kimlik No</label>
                                <input type="text" class="form-control" name="ogrenci_tc" id="ogrenci_tc"
                                       value="<?= h($ogr['ogrenci_tc']) ?>" maxlength="11">
                            </div>
                        </div>

                        <div class="col-xxl col-xl-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="ogrenci_adi">Adı</label>
                                <input type="text" class="form-control" name="ogrenci_adi" id="ogrenci_adi"
                                       value="<?= h($ogr['ogrenci_adi']) ?>" required>
                            </div>
                        </div>

                        <div class="col-xxl col-xl-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="ogrenci_soyadi">Soyadı</label>
                                <input type="text" class="form-control" name="ogrenci_soyadi" id="ogrenci_soyadi"
                                       value="<?= h($ogr['ogrenci_soyadi']) ?>" required>
                            </div>
                        </div>

                        <div class="col-xxl col-xl-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="ogrenci_cinsiyet">Cinsiyet</label>
                                <select class="form-control" name="ogrenci_cinsiyet" id="ogrenci_cinsiyet" required>
                                    <option value="">Seçin</option>
                                    <option value="1" <?= ($ogr['ogrenci_cinsiyet'] == '1' ? 'selected' : '') ?>>Erkek</option>
                                    <option value="0" <?= ($ogr['ogrenci_cinsiyet'] == '0' ? 'selected' : '') ?>>Kız</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xxl col-xl-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="ogrenci_dogumtar">Doğum Tarihi</label>
                                <div class="input-icon position-relative">
                                    <input type="text" class="form-control datetimepicker" name="ogrenci_dogumtar" id="ogrenci_dogumtar"
                                           value="<?= h($ogr['ogrenci_dogumtar']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl col-xl-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="ogrenci_tel">Telefon Numarası</label>
                                <input type="tel" class="form-control" name="ogrenci_tel" id="ogrenci_tel"
                                       value="<?= h($ogr['ogrenci_tel']) ?>"
                                       pattern="^(\+?90)?\s?0?\d{10}$" placeholder="05XXXXXXXXX veya +90 5XXXXXXXXX" required>
                            </div>
                        </div>

                        <div class="col-xxl col-xl-3 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="ogrenci_mail">E-posta Adresi</label>
                                <input type="email" class="form-control" name="ogrenci_mail" id="ogrenci_mail"
                                       value="<?= h($ogr['ogrenci_mail']) ?>" required>
                            </div>
                        </div>

                        <div class="col-xxl col-xl-3 col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="aktif" id="aktif" value="1" <?= ($ogr['aktif'] ? 'checked' : '') ?>>
                                <label class="form-check-label" for="aktif">Aktif Öğrenci</label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Adres Bilgileri -->
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                        <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                            <i class="ti ti-map fs-16"></i>
                        </span>
                        <h4 class="text-dark">Adres Bilgileri</h4>
                    </div>
                </div>
                <div class="card-body pb-1">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">İl</label>
                                <select class="select" name="il_id">
                                    <option value="">Seçin</option>
                                    <?php foreach ($iller as $i): ?>
                                        <option value="<?= (int)$i['il_id'] ?>" <?= ((int)$ogr['il_id'] === (int)$i['il_id'] ? 'selected' : '') ?>>
                                            <?= h($i['il_adi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">İlçe</label>
                                <select class="select" name="ilce_id">
                                    <option value="">Seçin</option>
                                    <?php foreach ($ilceler as $i): ?>
                                        <option value="<?= (int)$i['ilce_id'] ?>" <?= ((int)$ogr['ilce_id'] === (int)$i['ilce_id'] ? 'selected' : '') ?>>
                                            <?= h($i['ilce_adi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Adres Detayı</label>
                                <textarea class="form-control" name="ogrenci_adres" rows="3" required><?= h($ogr['ogrenci_adres']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Veli Bilgileri -->
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                        <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                            <i class="ti ti-user-shield fs-16"></i>
                        </span>
                        <h4 class="text-dark">Veli Bilgileri</h4>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="mb-3">
                        <div class="d-flex align-items-center flex-wrap">
                            <label class="form-label text-dark fw-normal me-2">Veli / Vasi Durumu</label>
                            <div class="form-check me-3 mb-2">
                                <input class="form-check-input" type="radio" name="veli_durumu" id="kendisi" value="1"
                                    <?= $veli ? '' : 'checked' ?>>
                                <label class="form-check-label" for="kendisi">Öğrencinin Kendisi</label>
                            </div>
                            <div class="form-check me-3 mb-2">
                                <input class="form-check-input" type="radio" name="veli_durumu" id="veli" value="0"
                                    <?= $veli ? 'checked' : '' ?>>
                                <label class="form-check-label" for="veli">Farklı Veli / Vasi</label>
                            </div>
                        </div>
                    </div>

                    <div id="guardian-details-section" style="<?= $veli ? '' : 'display:none;' ?>">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Veli Tc</label>
                                    <input type="text" class="form-control" name="veli_tc" value="<?= h($veli['veli_tc'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Veli Adı</label>
                                    <input type="text" class="form-control" name="veli_adi" value="<?= h($veli['veli_adi'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Veli Soyadı</label>
                                    <input type="text" class="form-control" name="veli_soyadi" value="<?= h($veli['veli_soyadi'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Veli Telefon</label>
                                    <input type="tel" class="form-control" name="veli_tel"
                                           pattern="^(\+?90)?\s?0?\d{10}$"
                                           placeholder="05XXXXXXXXX veya +90 5XXXXXXXXX"
                                           value="<?= h($veli['veli_tel'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Veli E-posta</label>
                                    <input type="email" class="form-control" name="veli_mail" value="<?= h($veli['veli_mail'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-lg-9 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Veli Adresi</label>
                                    <input type="text" class="form-control" name="veli_adres" value="<?= h($veli['veli_adres'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="text-end">
                <a href="ogrenci-detay.php?id=<?= urlencode($ogr_no) ?>" class="btn btn-light me-3">Vazgeç</a>
                <button type="submit" name="submit_student_update" class="btn btn-primary">
                    Kaydı Güncelle
                </button>
            </div>

        </form>
    </div>
</div>

<?php if (!empty($_SESSION['flash_swal'])):
    $sw = $_SESSION['flash_swal']; unset($_SESSION['flash_swal']); ?>
    <script src="<?= SWEET_ALERT_CDN ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            Swal.fire({
                icon: '<?= $sw['icon'] ?? 'info' ?>',
                title:'<?= addslashes($sw['title'] ?? '') ?>',
                text: '<?= addslashes($sw['text']  ?? '') ?>'
            });
        });
    </script>
<?php endif; ?>

<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/select2/js/select2.min.js"></script>
<script src="assets/js/moment.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/tr.min.js"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
<script src="assets/js/feather.min.js"></script>

<script>
    (function () {
        if (window.jQuery) {
            jQuery(function ($) {
                if ($.fn.select2) $('.select').select2({ width: '100%' });
                if ($.fn.datetimepicker) $('.datetimepicker').datetimepicker({
                    format: 'YYYY-MM-DD',
                    icons: { time: 'ti ti-clock' }
                });
                if (window.feather && feather.replace) feather.replace();
            });
        }

        // Veli alanı görünürlük kontrolü
        function toggleGuardian() {
            var isSelf = document.querySelector('input[name="veli_durumu"]:checked').value === '1';
            var sec = document.getElementById('guardian-details-section');
            if (!sec) return;
            sec.style.display = isSelf ? 'none' : '';
            // alanları duruma göre required yap
            ['veli_adi','veli_soyadi','veli_tel','veli_mail','veli_adres'].forEach(function(name){
                var el = sec.querySelector('[name="'+name+'"]');
                if (el) el.required = !isSelf;
            });
            // Kendisi ise değerleri temizlemek istersen:
            if (isSelf) {
                sec.querySelectorAll('input,textarea').forEach(function(i){
                    i.value = '';
                });
            }
        }
        document.addEventListener('change', function(e){
            if (e.target && e.target.name === 'veli_durumu') toggleGuardian();
        });
        document.addEventListener('DOMContentLoaded', toggleGuardian);
    })();
</script>
</body>
</html>