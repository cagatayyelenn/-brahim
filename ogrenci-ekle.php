<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Öğrenci Ekleme";

// --- Seçenekler ---
$iller    = $db->finds('il',   null, null, ['il_id','il_adi']);
$ilceler  = $db->finds('ilce', null, null, ['ilce_id','ilce_adi','il_id']);

// Öğrenci no üretici
function generateOgrenciNumara(PDO $pdo): string {
    $yil = date('Y');
    $stmt = $pdo->prepare("SELECT MAX(ogrenci_numara) AS m FROM ogrenci1 WHERE ogrenci_numara LIKE :pfx");
    $stmt->execute([':pfx' => $yil.'%']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($row['m'])) {
        $son = (int)substr($row['m'], 4);
        return $yil . str_pad((string)($son + 1), 6, '0', STR_PAD_LEFT);
    }
    return $yil . '000001';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student'])) {

    // Zorunlu / temel alanlar
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

    // Oturum
    $per_id  = (int)($_SESSION['kisi_id'] ?? 0);
    $sube_id = (int)($_SESSION['sube_id'] ?? 0);

    // Basit validasyon
    $hata = '';
    if ($ogrenci_adi === '' || $ogrenci_soyadi === '') {
        $hata = 'Ad ve soyad zorunludur.';
    } elseif ($ogrenci_mail !== '' && !filter_var($ogrenci_mail, FILTER_VALIDATE_EMAIL)) {
        $hata = 'E-posta formatı geçersiz.';
    }

    // Veli alanları
    $veli_durumu = $_POST['veli_durumu'] ?? '1'; // "1": Kendisi, "0": Farklı
    $veli_tc     = trim($_POST['veli_tc']     ?? '');
    $veli_adi    = trim($_POST['veli_adi']    ?? '');
    $veli_soyadi = trim($_POST['veli_soyadi'] ?? '');
    $veli_tel    = trim($_POST['veli_tel']    ?? '');
    $veli_mail   = trim($_POST['veli_mail']   ?? '');
    $veli_adres  = trim($_POST['veli_adres']  ?? '');

    // Eğer "Farklı" seçilmişse veli için temel doğrulama (istersen gevşetebilirsin)
    if ($hata === '' && $veli_durumu === '0') {
        if ($veli_adi === '' || $veli_soyadi === '') {
            $hata = 'Veli adı ve soyadı zorunludur.';
        } elseif ($veli_mail !== '' && !filter_var($veli_mail, FILTER_VALIDATE_EMAIL)) {
            $hata = 'Veli e-posta formatı geçersiz.';
        }
    }

    if ($hata === '') {
        // Öğrenci numarası
        $ogrenci_numara = generateOgrenciNumara($db->conn);

        // Öğrenci ekle
        $columns = [
            'ogrenci_numara','ogrenci_tc','ogrenci_adi','ogrenci_soyadi',
            'ogrenci_tel','ogrenci_mail','ogrenci_cinsiyet','ogrenci_dogumtar',
            'il_id','ilce_id','ogrenci_adres',
            'per_id','sube_id','aktif','kayit_tarihi'
        ];
        $values = [
            $ogrenci_numara,$ogrenci_tc,$ogrenci_adi,$ogrenci_soyadi,
            $ogrenci_tel,$ogrenci_mail,$ogrenci_cinsiyet,$ogrenci_dogumtar,
            $il_id,$ilce_id,$ogrenci_adres,
            $per_id,$sube_id,1,date('Y-m-d H:i:s')
        ];
        $ins = $db->insert('ogrenci1', $columns, $values);

        if (!empty($ins['status']) && $ins['status'] == 1) {
            $ogrenci_id = (int)$ins['id'];

            // Veli farklı ise kaydet
            if ($veli_durumu === '0') {
                $vCols = ['ogrenci_id','veli_adi','veli_soyadi','veli_tc','veli_tel','veli_mail','veli_adres'];
                $vVals = [$ogrenci_id,  $veli_adi,  $veli_soyadi,  $veli_tc,  $veli_tel,  $veli_mail,  $veli_adres];

                $velIns = $db->insert('veli1', $vCols, $vVals);
                // İstersen burada $velIns kontrolü yapıp loglayabilirsin
            }

            // Başarı – modal tetikle
            $_SESSION['ogr_kayit_basarili'] = [
                'ogrenci_numara' => $ogrenci_numara,
                'mesaj'          => 'Öğrenci kaydı başarıyla tamamlandı.'
            ];
            header("Location: ogrenci-ekle.php");
            exit;

        } else {
            // Insert başarısız
            $_SESSION['flash_swal'] = [
                'icon'  => 'error',
                'title' => 'Hata',
                'text'  => 'Kayıt sırasında bir hata oluştu.'
            ];
            header("Location: ogrenci-ekle.php");
            exit;
        }

    } else {
        // Validasyon hatası
        $_SESSION['flash_swal'] = [
            'icon'  => 'warning',
            'title' => 'Doğrulama',
            'text'  => $hata
        ];
        header("Location: ogrenci-ekle.php");
        exit;
    }
} ?>


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
                <h3 class="mb-1">Öğrenci Ekleme Sayfası</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="students.html">Öğrenciler</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Öğrenci Ekleme</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">

                <form action="ogrenci-ekle.php" method="POST"  id="ogrenci-ekle-form"   >

                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex align-items-center">
                        <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                            <i class="ti ti-info-square-rounded fs-16"></i>
                        </span>
                                <h4 class="text-dark">Öğrenci Kişisel Bilgileri</h4>
                            </div>
                        </div>
                        <div class="card-body pb-1">
                            <div class="row">
                                <div class="col-md-12">
                                </div>
                            </div>
                            <div class="row row-cols-xxl-5 row-cols-md-6">

                                <!-- T.C. / Kimlik No + Yabancı -->
                                <div class="col-xxl col-xl-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="ogrenci_tc">T.C. / Kimlik No</label>
                                        <div class="d-flex align-items-center">
                                            <input type="text" class="form-control me-2" name="ogrenci_tc" id="ogrenci_tc" inputmode="numeric" maxlength="11" pattern="^\d{11}$" required >
                                            <div class="form-check ms-2">
                                                <input class="form-check-input" type="checkbox" id="yabanci_ogrenci">
                                                <label class="form-check-label small" for="yabanci_ogrenci">Yabancı</label>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback">Lütfen geçerli bir T.C. Kimlik No girin.</div>
                                    </div>
                                </div>

                                <!-- Adı -->
                                <div class="col-xxl col-xl-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="ogrenci_adi">Adı</label>
                                        <input type="text" class="form-control" name="ogrenci_adi" id="ogrenci_adi" required>
                                    </div>
                                </div>

                                <!-- Soyadı -->
                                <div class="col-xxl col-xl-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="ogrenci_soyadi">Soyadı</label>
                                        <input type="text" class="form-control" name="ogrenci_soyadi" id="ogrenci_soyadi" required>
                                    </div>
                                </div>


                                <!-- Cinsiyet -->
                                <div class="col-xxl col-xl-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="ogrenci_cinsiyet">Cinsiyet</label>
                                        <select class="form-control" name="ogrenci_cinsiyet" id="ogrenci_cinsiyet" required>
                                            <option value="">Seçin</option>
                                            <option value="1">Erkek</option>
                                            <option value="0">Kız</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Doğum Tarihi -->
                                <div class="col-xxl col-xl-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="ogrenci_dogumtar">Doğum Tarihi</label>
                                        <div class="input-icon position-relative">
                                            <input type="text" class="form-control datetimepicker" name="ogrenci_dogumtar" id="ogrenci_dogumtar" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Telefon -->
                                <div class="col-xxl col-xl-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="ogrenci_tel">Telefon Numarası</label>
                                        <input type="tel" class="form-control"  id="ogrenci_tel"
                                               pattern="^(\+?90)?\s?0?\d{10}$" placeholder="05XXXXXXXXX veya +90 5XXXXXXXXX" required>
                                    </div>
                                </div>
                                 

                                <!-- E-posta -->
                                <div class="col-xxl col-xl-3 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="ogrenci_mail">E-posta Adresi</label>
                                        <input type="email" class="form-control" name="ogrenci_mail" id="ogrenci_mail" required>
                                        <div class="invalid-feedback">Geçerli bir e-posta adresi giriniz.</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
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
                                                <option value="<?= (int)$i['il_id'] ?>"><?= htmlspecialchars($i['il_adi']) ?></option>
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
                                                <option value="<?= (int)$i['ilce_id'] ?>">
                                                    <?= htmlspecialchars($i['ilce_adi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Adres Detayı</label>
                                        <textarea class="form-control" name="ogrenci_adres" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                        <input class="form-check-input" type="radio" name="veli_durumu" id="kendisi" value="1"   checked>
                                        <label class="form-check-label" for="kendisi">
                                            Öğrencinin Kendisi
                                        </label>
                                    </div>
                                    <div class="form-check me-3 mb-2">
                                        <input class="form-check-input" type="radio" name="veli_durumu" id="veli" value="0" >
                                        <label class="form-check-label" for="veli">
                                            Farklı Veli / Vasi
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="guardian-details-section" style="display:none;">
                                <input type="hidden" name="veli_tc"> <div class="row">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Veli Tc</label>
                                            <input type="text" class="form-control" name="veli_tc">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Veli Adı</label>
                                            <input type="text" class="form-control" name="veli_adi">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Veli Soyadı</label>
                                            <input type="text" class="form-control" name="veli_soyadi">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Veli Telefon</label>
                                            <input type="tel" class="form-control" name="veli_tel" pattern="^(\+?90)?\s?0?\d{10}$" placeholder="05XXXXXXXXX veya +90 5XXXXXXXXX">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Veli E-posta</label>
                                            <input type="email" class="form-control" name="veli_mail">
                                        </div>
                                    </div>
                                    <div class="col-lg-9 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Veli Adresi</label>
                                            <input type="text" class="form-control" name="veli_adres">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-light me-3">İptal</button>
                        <button type="submit" name="submit_student" class="btn btn-primary">Öğrenciyi Kaydet</button>
                          
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>



</div>

<?php if (!empty($_SESSION['ogr_kayit_basarili'])):
    $ogrNo = $_SESSION['ogr_kayit_basarili']['ogrenci_numara'];
    $msg   = $_SESSION['ogr_kayit_basarili']['mesaj'] ?? 'İşlem başarılı.';
    // tek seferlik gösterim
    unset($_SESSION['ogr_kayit_basarili']);
    ?>
    <!-- Başarı Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 shadow-lg">
                <div class="modal-body text-center py-5">
                    <div class="mb-3">
          <span class="avatar avatar-xl bg-success text-white rounded-circle">
            <i class="ti ti-check fs-32"></i>
          </span>
                    </div>
                    <h4 class="text-success fw-bold mb-1">Kayıt Başarılı!</h4>
                    <p class="text-muted mb-0"><?= htmlspecialchars($msg) ?></p>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <div class="d-flex w-100 gap-2 flex-nowrap">
                        <a href="ogrenci-listesi.php" class="btn btn-outline-primary flex-fill d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-users fs-18"></i> Öğrenci Listesine Dön
                        </a>
                        <a href="sozlesme-olustur.php?id=<?= urlencode($ogrNo) ?>" class="btn btn-success flex-fill d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-file-text fs-18"></i> Sözleşme Oluştur
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sayfa yüklenince modal'ı göster
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('staticBackdrop');
            if (el) {
                var m = new bootstrap.Modal(el, {backdrop:'static', keyboard:false});
                m.show();
            }
        });
    </script>
<?php endif; ?>

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

<script data-cfasync="false" src="assets/js/jquery-3.7.1.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap.bundle.min.js"></script>
<script data-cfasync="false" src="assets/js/form-validation.js" type="text/javascript"></script>
<script data-cfasync="false" src="assets/js/moment.js"></script>
<script data-cfasync="false" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/tr.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap-datetimepicker.min.js"></script>
 <script data-cfasync="false" src="assets/plugins/select2/js/select2.min.js"></script>
<script data-cfasync="false" src="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
<script data-cfasync="false" src="assets/js/feather.min.js"></script>
<script data-cfasync="false" src="assets/js/jquery.slimscroll.min.js"></script>
<script data-cfasync="false" src="assets/js/checsum.js" type="text/javascript"></script>
<script data-cfasync="false" src="assets/js/script.js"></script>
<script src="assets/js/jquery.maskedinput.min.js" type="text/javascript"></script>
<script src="assets/js/mask.js" type="text/javascript"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chkYabanci = document.getElementById('yabanci_ogrenci');
        const inputTc = document.getElementById('ogrenci_tc');

        chkYabanci.addEventListener('change', function() {
            if (this.checked) {
                inputTc.value = '10000000146';   // Yabancı öğrenci varsayılan TC
                inputTc.readOnly = true;         // Değiştirilemesin
                inputTc.removeAttribute('required'); // Form validasyonu takılmasın
            } else {
                inputTc.value = '';
                inputTc.readOnly = false;
                inputTc.setAttribute('required', 'required');
            }
        });
    });
</script>
<script>
    (function () {
        if (!window.jQuery) return console.error('jQuery yok');

        jQuery(function ($) {
            if ($.fn.select2) $('.select').each(function(){ $(this).select2({ width: '100%' }); });
            if ($.fn.daterangepicker) $('.daterange').daterangepicker();
            if ($.fn.datetimepicker) $('.datetimepicker').datetimepicker({ icons: { time: 'ti ti-clock' } });
            if ($.fn.tagsinput) $('.input-tags').tagsinput();
            if (window.feather && feather.replace) feather.replace();
        });
    })();
</script>
<script>
    (function () {
        function toggleGuardian() {
            var isSelf = $('input[name="veli_durumu"]:checked').val() === '1';
            var $sec   = $('#guardian-details-section');
            $sec.toggle(!isSelf);

            // Bölüm kapalıyken post'a gitmesin
            $sec.find(':input').prop('disabled', isSelf);

            // Zorunlulukları duruma göre ayarla (sadece görünenken required)
            var requiredWhenVisible = ['veli_adi','veli_soyadi','veli_tel','veli_mail','veli_adres'];
            requiredWhenVisible.forEach(function(name){
                $sec.find('[name="'+name+'"]').prop('required', !isSelf);
            });

            // Kendi seçildiyse alanları temizle (gizli olanlar hariç)
            if (isSelf) {
                $sec.find(':input:not([type="hidden"])').val('');
            }
        }

        // İlk yüklemede ve değişimde çalıştır
        $(document).on('change', 'input[name="veli_durumu"]', toggleGuardian);
        $(function(){ toggleGuardian(); });
    })();
</script>
<script>
    moment.locale('tr');
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD',  // MySQL DATE ile birebir uyumlu
        icons: { time: 'ti ti-clock' }
    });
</script>

</body>
</html>