<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Milli Eğitim Sınıfları";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('meb_siniflari', $sil_id);

        if ($del['status'] == 1) {
            $db->log('meb_siniflari', $sil_id, 'SİLME', 'MEB sınıf kaydı silindi.');
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
    header("Location: meb-sinif.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM meb_siniflari WHERE id = '{$edit_id}'");
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

    $baslama_tarihi = '';
    if (!empty($_POST['baslama_tarihi'])) {
        $dt = DateTime::createFromFormat('d.m.Y', $_POST['baslama_tarihi']);
        if ($dt)
            $baslama_tarihi = $dt->format('Y-m-d');
    }

    $bitis_tarihi = '';
    if (!empty($_POST['bitis_tarihi'])) {
        $dt = DateTime::createFromFormat('d.m.Y', $_POST['bitis_tarihi']);
        if ($dt)
            $bitis_tarihi = $dt->format('Y-m-d');
    }

    $sinif_adi = trim($_POST['sinif_adi'] ?? '');
    $subesi = trim($_POST['subesi'] ?? '');
    $ogretmen_adi_soyadi = trim($_POST['ogretmen_adi_soyadi'] ?? '');
    $konular = trim($_POST['konular'] ?? '');
    $ogrenci_sayisi = (int) ($_POST['ogrenci_sayisi'] ?? 0);
    $durum = $_POST['durum'] ?? 'Aktif';

    // Sistem Kullanıcısı Session'dan
    $sistem_kullanicisi_id = $_SESSION['personel_id'] ?? 0;

    $form_id = (int) ($_POST['form_id'] ?? 0);

    // Basit Validasyon
    if (empty($sinif_adi)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Sınıf Adı alanını doldurunuz.'
        ];
    } else {
        $columns = [
            'tarih',
            'sinif_adi',
            'subesi',
            'ogretmen_adi_soyadi',
            'konular',
            'ogrenci_sayisi',
            'baslama_tarihi',
            'bitis_tarihi',
            'durum',
            'sistem_kullanicisi_id'
        ];
        $values = [
            $tarih,
            $sinif_adi,
            $subesi,
            $ogretmen_adi_soyadi,
            $konular,
            $ogrenci_sayisi,
            $baslama_tarihi,
            $bitis_tarihi,
            $durum,
            $sistem_kullanicisi_id
        ];

        if ($form_id > 0) {
            // --- GÜNCELLEME ---
            $upd = $db->update('meb_siniflari', $columns, $values, 'id', $form_id);

            if ($upd['status'] == 1) {
                $db->log('meb_siniflari', $form_id, 'GÜNCELLEME', "$sinif_adi sınıfı güncellendi.");
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

            $ins = $db->insert('meb_siniflari', $columns, $values);

            if ($ins['status'] == 1) {
                $new_id = $ins['id'] ?? 0;
                $db->log('meb_siniflari', $new_id, 'EKLEME', "$sinif_adi yeni sınıf eklendi.");
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
    }

    header("Location: meb-sinif.php");
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
$sql_list = "SELECT m.*, p.personel_adi, p.personel_soyadi 
             FROM meb_siniflari m 
             LEFT JOIN personel1 p ON m.sistem_kullanicisi_id = p.personel_id
             WHERE m.sube_id = '{$sube_id}'
             ORDER BY m.id DESC";
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
                <h3 class="mb-1">Milli Eğitim Sınıfları</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Milli Eğitim Sınıfları</li>
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
                                <i class="ti ti-school fs-16"></i>
                            </span>
                            <h4 class="text-dark">
                                <?= $edit_mode ? 'Kayıt Düzenle' : 'Yeni Sınıf Oluştur' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="meb-sinif.php" class="btn btn-sm btn-warning ms-auto">
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
                                <!-- Sınıf Adı -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputSinifAdi">Sınıf Adı</label>
                                    <input class="form-control" id="inputSinifAdi" name="sinif_adi" type="text"
                                        placeholder="Örn: 9-A, 11-TB" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['sinif_adi']) : '' ?>" />
                                </div>
                                <!-- Şubesi -->
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="inputSube">Şubesi</label>
                                    <input class="form-control" id="inputSube" name="subesi" type="text"
                                        placeholder="Aktif Sınıf Şubesi"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['subesi']) : '' ?>" />
                                </div>
                                <!-- Öğretmen -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputOgretmen">Öğretmen Adı Soyadı</label>
                                    <input class="form-control" id="inputOgretmen" name="ogretmen_adi_soyadi"
                                        type="text" placeholder="Dersi Veren Öğretmen"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['ogretmen_adi_soyadi']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Konular -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="textKonular">Konular</label>
                                    <textarea class="form-control" id="textKonular" name="konular" rows="3"
                                        placeholder="İşlenen Konular / Müfredat"><?= $edit_mode ? htmlspecialchars($edit_data['konular']) : '' ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <!-- Öğrenci Sayısı -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="inputOgrSay">Öğrenci Sayısı</label>
                                            <input class="form-control" id="inputOgrSay" name="ogrenci_sayisi"
                                                type="number"
                                                value="<?= $edit_mode ? (int) $edit_data['ogrenci_sayisi'] : '' ?>" />
                                        </div>
                                        <!-- Durum -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label" for="selectDurum">Durum</label>
                                            <select class="form-select select" id="selectDurum" name="durum">
                                                <?php
                                                $durum_opts = ['Aktif', 'Pasif', 'Tamamlandı'];
                                                foreach ($durum_opts as $opt) {
                                                    $sel = ($edit_mode && $edit_data['durum'] == $opt) ? 'selected' : '';
                                                    echo "<option value='$opt' $sel>$opt</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <!-- Başlama Tarihi -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="inputBaslama">Başlama Tarihi</label>
                                            <?php
                                            $val_bas = '';
                                            if ($edit_mode && !empty($edit_data['baslama_tarihi']))
                                                $val_bas = date('d.m.Y', strtotime($edit_data['baslama_tarihi']));
                                            ?>
                                            <input class="form-control date-picker" id="inputBaslama"
                                                name="baslama_tarihi" type="text" value="<?= $val_bas ?>" />
                                        </div>
                                        <!-- Bitiş Tarihi -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="inputBitis">Bitiş Tarihi</label>
                                            <?php
                                            $val_bit = '';
                                            if ($edit_mode && !empty($edit_data['bitis_tarihi']))
                                                $val_bit = date('d.m.Y', strtotime($edit_data['bitis_tarihi']));
                                            ?>
                                            <input class="form-control date-picker" id="inputBitis" name="bitis_tarihi"
                                                type="text" value="<?= $val_bit ?>" />
                                        </div>
                                    </div>
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
                            <h4 class="text-dark">Sınıf Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered fs-14" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Sınıf / Şube</th>
                                        <th>Öğretmen / Öğr. Sayısı</th>
                                        <th>Konular</th>
                                        <th>Süre</th>
                                        <th>Durum</th>
                                        <th>Kullanıcı</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($liste): ?>
                                        <?php foreach ($liste as $row):
                                            $tarih_goster = date('d.m.Y', strtotime($row['tarih']));

                                            $sure = "";
                                            if (!empty($row['baslama_tarihi']))
                                                $sure .= date('d.m.Y', strtotime($row['baslama_tarihi']));
                                            if (!empty($row['bitis_tarihi']))
                                                $sure .= " - " . date('d.m.Y', strtotime($row['bitis_tarihi']));

                                            // Personel Adı
                                            $personel_tam_ad = trim(($row['personel_adi'] ?? '') . ' ' . ($row['personel_soyadi'] ?? ''));
                                            if (empty($personel_tam_ad))
                                                $personel_tam_ad = "-";

                                            // Renklendirme
                                            $badgeClass = "bg-secondary";
                                            if ($row['durum'] == 'Aktif')
                                                $badgeClass = "bg-success";
                                            if ($row['durum'] == 'Pasif')
                                                $badgeClass = "bg-warning";
                                            if ($row['durum'] == 'Tamamlandı')
                                                $badgeClass = "bg-info";
                                            ?>
                                            <tr>
                                                <td><?= $tarih_goster ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row['sinif_adi']) ?></strong><br>
                                                    <small>Şube: <?= htmlspecialchars($row['subesi']) ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($row['ogretmen_adi_soyadi']) ?><br>
                                                    <small>(<?= $row['ogrenci_sayisi'] ?> Öğrenci)</small>
                                                </td>
                                                <td><?= nl2br(htmlspecialchars($row['konular'])) ?></td>
                                                <td><?= $sure ?></td>
                                                <td><span
                                                        class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['durum']) ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($personel_tam_ad) ?></td>
                                                <td class="text-center">
                                                    <a href="meb-sinif.php?edit_id=<?= $row['id'] ?>"
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
                window.location.href = 'meb-sinif.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>