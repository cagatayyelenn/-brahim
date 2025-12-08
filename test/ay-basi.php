<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Ay Başı Yapılacaklar";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('ay_basi_yapilacaklar', $sil_id);

        if ($del['status'] == 1) {
            $db->log('ay_basi_yapilacaklar', $sil_id, 'SİLME', 'Ay başı yapılacaklar kaydı silindi.');
            $_SESSION['flash_swal'] = [
                'icon' => 'success',
                'title' => 'Silindi',
                'text' => 'Kayıt başarıyla silindi.'
            ];
        } else {
            $_SESSION['flash_swal'] = [
                'icon' => 'error',
                'title' => 'Hata',
                'text' => 'Silme işlemi başarısız oldu.'
            ];
        }
    }
    header("Location: ay-basi.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM ay_basi_yapilacaklar WHERE id = '{$edit_id}'");
        if ($edit_data) {
            $edit_mode = true;
        }
    }
}

// --- Kaydet / Güncelle İşlemi ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kaydet'])) {

    // Tarih Formatlama
    $tarih_raw = $_POST['tarih'] ?? '';
    $tarih = '';
    if ($tarih_raw) {
        $dt = DateTime::createFromFormat('d.m.Y', $tarih_raw);
        if ($dt)
            $tarih = $dt->format('Y-m-d');
    }
    if (empty($tarih))
        $tarih = date('Y-m-d');

    $ogretmen_odeme = trim($_POST['ogretmen_odeme'] ?? '');
    $sirket_faturalari = trim($_POST['sirket_faturalari'] ?? '');
    $ogrenci_odeme = trim($_POST['ogrenci_odeme'] ?? '');
    $maaslar = trim($_POST['maaslar'] ?? '');
    $kira_odemeleri = trim($_POST['kira_odemeleri'] ?? '');
    $diger_giderler = trim($_POST['diger_giderler'] ?? '');
    $genel_aciklama = trim($_POST['genel_aciklama'] ?? '');
    $durum = $_POST['durum'] ?? 'Yapılmadı';

    // Sistem Kullanıcısı Session'dan
    $sistem_kullanicisi_id = $_SESSION['personel_id'] ?? 0;

    $form_id = (int) ($_POST['form_id'] ?? 0);

    // Basit Validasyon (Sadece Tarih kritik, diğerleri opsiyonel olabilir ama en az bir alan dolu olsa iyi olur)
    // Şimdilik zorunlu alan tutmuyoruz kullanıcı isteğine göre

    $columns = [
        'tarih',
        'ogretmen_odeme',
        'sirket_faturalari',
        'ogrenci_odeme',
        'maaslar',
        'kira_odemeleri',
        'diger_giderler',
        'genel_aciklama',
        'durum',
        'sistem_kullanicisi_id'
    ];
    $values = [
        $tarih,
        $ogretmen_odeme,
        $sirket_faturalari,
        $ogrenci_odeme,
        $maaslar,
        $kira_odemeleri,
        $diger_giderler,
        $genel_aciklama,
        $durum,
        $sistem_kullanicisi_id
    ];

    if ($form_id > 0) {
        // --- GÜNCELLEME ---
        $upd = $db->update('ay_basi_yapilacaklar', $columns, $values, 'id', $form_id);

        if ($upd['status'] == 1) {
            $db->log('ay_basi_yapilacaklar', $form_id, 'GÜNCELLEME', "Ay başı kaydı güncellendi.");
            $_SESSION['flash_swal'] = [
                'icon' => 'success',
                'title' => 'Güncellendi',
                'text' => 'Kayıt başarıyla güncellendi.'
            ];
        } else {
            $_SESSION['flash_swal'] = [
                'icon' => 'error',
                'title' => 'Hata',
                'text' => 'Güncelleme hatası: ' . ($upd['error'] ?? '')
            ];
        }

    } else {
        // --- EKLEME ---
        $columns[] = 'created_at';
        $values[] = date('Y-m-d H:i:s');

        // Şube ID Ekleme
        $columns[] = 'sube_id';
        $values[] = $sube_id;

        $ins = $db->insert('ay_basi_yapilacaklar', $columns, $values);

        if ($ins['status'] == 1) {
            $new_id = $ins['id'] ?? 0;
            $db->log('ay_basi_yapilacaklar', $new_id, 'EKLEME', "Yeni ay başı kaydı eklendi.");
            $_SESSION['flash_swal'] = [
                'icon' => 'success',
                'title' => 'Başarılı',
                'text' => 'Kayıt başarıyla oluşturuldu.'
            ];
        } else {
            $_SESSION['flash_swal'] = [
                'icon' => 'error',
                'title' => 'Hata',
                'text' => 'Kayıt hatası: ' . ($ins['error'] ?? '')
            ];
        }
    }

    header("Location: ay-basi.php");
    exit;
}

// Oturum açan kullanıcı (Görsel amaçlı)
$kullanici_adi = "Sistem Kullanıcısı";
if (isset($_SESSION['ad']) && isset($_SESSION['soyad'])) {
    $kullanici_adi = $_SESSION['ad'] . " " . $_SESSION['soyad'];
} elseif (isset($_SESSION['kullanici_adi'])) {
    $kullanici_adi = $_SESSION['kullanici_adi'];
}

// Listeyi Çek (Sadece ilgili şube ve personel ismiyle join)
$sql_list = "SELECT a.*, p.personel_adi, p.personel_soyadi 
             FROM ay_basi_yapilacaklar a 
             LEFT JOIN personel1 p ON a.sistem_kullanicisi_id = p.personel_id
             WHERE a.sube_id = '{$sube_id}'
             ORDER BY a.id DESC";
$liste = $db->get($sql_list);

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
                <h3 class="mb-1">Ay Başı Yapılacaklar</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Ay Başı Yapılacaklar</li>
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
                                <i class="ti ti-calendar-stats fs-16"></i>
                            </span>
                            <h4 class="text-dark">
                                <?= $edit_mode ? 'Kayıt Düzenle' : 'Yeni Kayıt Oluştur' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="ay-basi.php" class="btn btn-sm btn-warning ms-auto">
                                    <i class="ti ti-plus"></i> Yeni Ekleme Moduna Dön
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="form_id" value="<?= $edit_mode ? $edit_data['id'] : 0 ?>">

                            <div class="row">
                                <!-- Tarih -->
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="inputTarih">Tarih</label>
                                    <?php
                                    $val_tarih = date('d.m.Y');
                                    if ($edit_mode && !empty($edit_data['tarih'])) {
                                        $val_tarih = date('d.m.Y', strtotime($edit_data['tarih']));
                                    }
                                    ?>
                                    <input class="form-control date-picker" id="inputTarih" name="tarih" type="text"
                                        value="<?= $val_tarih ?>" />
                                </div>
                                <!-- Öğretmen Ödeme -->
                                <div class="col-md-5 mb-3">
                                    <label class="form-label" for="inputOgretmen">Öğretmen Ödeme</label>
                                    <input class="form-control" id="inputOgretmen" name="ogretmen_odeme" type="text"
                                        placeholder="Tutar veya Not"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['ogretmen_odeme']) : '' ?>" />
                                </div>
                                <!-- Şirket Faturaları -->
                                <div class="col-md-5 mb-3">
                                    <label class="form-label" for="inputSirket">Şirket Faturaları</label>
                                    <input class="form-control" id="inputSirket" name="sirket_faturalari" type="text"
                                        placeholder="Tutar veya Not"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['sirket_faturalari']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Öğrenci Ödeme -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputOgrenci">Öğrenci Ödeme</label>
                                    <input class="form-control" id="inputOgrenci" name="ogrenci_odeme" type="text"
                                        placeholder="Tutar veya Not"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['ogrenci_odeme']) : '' ?>" />
                                </div>
                                <!-- Maaşlar -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputMaaslar">Maaşlar</label>
                                    <input class="form-control" id="inputMaaslar" name="maaslar" type="text"
                                        placeholder="Tutar veya Not"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['maaslar']) : '' ?>" />
                                </div>
                                <!-- Kira Ödemeleri -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputKira">Kira Ödemeleri</label>
                                    <input class="form-control" id="inputKira" name="kira_odemeleri" type="text"
                                        placeholder="Tutar veya Not"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['kira_odemeleri']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Diğer Giderler -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="textGiderler">Diğer Giderler (Elk, Su, Doğalgaz
                                        vb.)</label>
                                    <textarea class="form-control" id="textGiderler" name="diger_giderler"
                                        rows="2"><?= $edit_mode ? htmlspecialchars($edit_data['diger_giderler']) : '' ?></textarea>
                                </div>
                                <!-- Genel Açıklama -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="textAciklama">Genel Açıklama</label>
                                    <textarea class="form-control" id="textAciklama" name="genel_aciklama"
                                        rows="2"><?= $edit_mode ? htmlspecialchars($edit_data['genel_aciklama']) : '' ?></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Durum -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="selectDurum">Durum</label>
                                    <select class="form-select select" id="selectDurum" name="durum">
                                        <?php
                                        $durum_opts = ['Yapılmadı', 'Yapıldı'];
                                        foreach ($durum_opts as $opt) {
                                            $sel = ($edit_mode && $edit_data['durum'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $sel>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Butonlar -->
                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-primary" type="submit" name="kaydet">
                                    <?= $edit_mode ? 'GÜNCELLE' : 'KAYDET' ?>
                                </button>
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
                            <h4 class="text-dark">Kayıt Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered fs-14" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Öğretmen / Şirket</th>
                                        <th>Öğrenci / Maaş / Kira</th>
                                        <th>Diğer Giderler</th>
                                        <th>Durum</th>
                                        <th>Kullanıcı</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($liste): ?>
                                        <?php foreach ($liste as $row):
                                            $tarih_goster = date('d.m.Y', strtotime($row['tarih']));

                                            // Personel Adı
                                            $personel_tam_ad = trim(($row['personel_adi'] ?? '') . ' ' . ($row['personel_soyadi'] ?? ''));
                                            if (empty($personel_tam_ad))
                                                $personel_tam_ad = "-";

                                            // Renklendirme
                                            $rowStyle = "";
                                            $textClass = "";

                                            if ($row['durum'] == 'Yapıldı') {
                                                $rowStyle = "background-color: #198754 !important; color: white !important;";
                                                $textClass = "text-white";
                                            } elseif ($row['durum'] == 'Yapılmadı') {
                                                $rowStyle = "background-color: #dc3545 !important; color: white !important;";
                                                $textClass = "text-white";
                                            }
                                            ?>
                                            <tr style="<?= $rowStyle ?>">
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>"><?= $tarih_goster ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <strong>Öğrt:</strong> <?= htmlspecialchars($row['ogretmen_odeme']) ?><br>
                                                    <strong>Fatura:</strong> <?= htmlspecialchars($row['sirket_faturalari']) ?>
                                                </td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <strong>Öğr:</strong> <?= htmlspecialchars($row['ogrenci_odeme']) ?><br>
                                                    <strong>Maaş:</strong> <?= htmlspecialchars($row['maaslar']) ?><br>
                                                    <strong>Kira:</strong> <?= htmlspecialchars($row['kira_odemeleri']) ?>
                                                </td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= nl2br(htmlspecialchars($row['diger_giderler'])) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['durum']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($personel_tam_ad) ?></td>
                                                <td class="text-center" style="<?= $rowStyle ?>">
                                                    <a href="ay-basi.php?edit_id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-info me-1" title="Düzenle">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" title="Sil"
                                                        onclick="silmeOnayi(<?= $row['id'] ?>)">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Henüz kayıt bulunmamaktadır.</td>
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
<!-- DatePicker -->
<script data-cfasync="false" src="assets/plugins/moment/moment.min.js"></script>
<script data-cfasync="false" src="assets/plugins/daterangepicker/daterangepicker.js"></script>

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
            if (window.feather && feather.replace) feather.replace();
            if ($.fn.select2) $('.select').each(function () { $(this).select2({ width: '100%' }); });

            // DateRangePicker (Single Date)
            $('.date-picker').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                locale: {
                    format: 'DD.MM.YYYY',
                    applyLabel: "Uygula",
                    cancelLabel: "İptal",
                    daysOfWeek: ["Pz", "Pt", "Sa", "Ça", "Pe", "Cu", "Ct"],
                    monthNames: ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"]
                }
            }, function (start, end, label) {
                $(this.element).val(start.format('DD.MM.YYYY'));
            });

            // Auto-fill input if value exists on init
            $('.date-picker').each(function () {
                if ($(this).val()) {
                    $(this).data('daterangepicker').setStartDate($(this).val());
                    $(this).data('daterangepicker').setEndDate($(this).val());
                }
            });
        });
    })();

    function silmeOnayi(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu kaydı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'ay-basi.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>