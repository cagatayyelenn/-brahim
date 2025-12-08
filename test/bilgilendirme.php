<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Bilgilendirme Formu";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('bilgilendirme_formu', $sil_id);

        if ($del['status'] == 1) {
            $db->log('bilgilendirme_formu', $sil_id, 'SİLME', 'Bilgilendirme formu silindi.');
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
    header("Location: bilgilendirme.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM bilgilendirme_formu WHERE id = '{$edit_id}'");
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

    $kurs_baslangic = '';
    if (!empty($_POST['kurs_baslangic'])) {
        $dt = DateTime::createFromFormat('d.m.Y', $_POST['kurs_baslangic']);
        if ($dt)
            $kurs_baslangic = $dt->format('Y-m-d');
    }

    $kurs_bitis = '';
    if (!empty($_POST['kurs_bitis'])) {
        $dt = DateTime::createFromFormat('d.m.Y', $_POST['kurs_bitis']);
        if ($dt)
            $kurs_bitis = $dt->format('Y-m-d');
    }

    $diller = trim($_POST['diller'] ?? '');
    $paket_tarifesi = trim($_POST['paket_tarifesi'] ?? '');
    $egitim_zamanlari = trim($_POST['egitim_zamanlari'] ?? '');
    $haftalik_planlama = trim($_POST['haftalik_planlama'] ?? '');
    $seviye_ucretlendirme = trim($_POST['seviye_ucretlendirme'] ?? '');

    $form_id = (int) ($_POST['form_id'] ?? 0);

    // Basit Validasyon
    if (empty($diller)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Diller alanını doldurunuz.'
        ];
    } else {
        $columns = [
            'tarih',
            'diller',
            'paket_tarifesi',
            'egitim_zamanlari',
            'haftalik_planlama',
            'kurs_baslangic',
            'kurs_bitis',
            'seviye_ucretlendirme'
        ];
        $values = [
            $tarih,
            $diller,
            $paket_tarifesi,
            $egitim_zamanlari,
            $haftalik_planlama,
            $kurs_baslangic,
            $kurs_bitis,
            $seviye_ucretlendirme
        ];

        if ($form_id > 0) {
            // --- GÜNCELLEME ---
            $upd = $db->update('bilgilendirme_formu', $columns, $values, 'id', $form_id);

            if ($upd['status'] == 1) {
                $db->log('bilgilendirme_formu', $form_id, 'GÜNCELLEME', "Bilgilendirme formu güncellendi.");
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

            $ins = $db->insert('bilgilendirme_formu', $columns, $values);

            if ($ins['status'] == 1) {
                $new_id = $ins['id'] ?? 0;
                $db->log('bilgilendirme_formu', $new_id, 'EKLEME', "Yeni bilgilendirme formu eklendi.");
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

    header("Location: bilgilendirme.php");
    exit;
}

// Listeyi Çek (Sadece ilgili şube)
$sql_list = "SELECT * FROM bilgilendirme_formu 
             WHERE sube_id = '{$sube_id}'
             ORDER BY id DESC";
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
                <h3 class="mb-1">Bilgilendirme Formu</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Bilgilendirme Formu</li>
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
                                <i class="ti ti-info-circle fs-16"></i>
                            </span>
                            <h4 class="text-dark">
                                <?= $edit_mode ? 'Kayıt Düzenle' : 'Yeni Kayıt Oluştur' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="bilgilendirme.php" class="btn btn-sm btn-warning ms-auto">
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
                                    <input class="form-control" id="inputTarih" name="tarih" type="text"
                                        value="<?= $val_tarih ?>" />
                                </div>
                                <!-- Diller -->
                                <div class="col-md-5 mb-3">
                                    <label class="form-label" for="inputDiller">Diller</label>
                                    <input class="form-control" id="inputDiller" name="diller" type="text"
                                        placeholder="Örn: İngilizce, Fransızca" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['diller']) : '' ?>" />
                                </div>
                                <!-- Paket Tarifesi -->
                                <div class="col-md-5 mb-3">
                                    <label class="form-label" for="inputPaket">Paket Tarifesi</label>
                                    <input class="form-control" id="inputPaket" name="paket_tarifesi" type="text"
                                        placeholder="Paket Adı / Fiyatı"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['paket_tarifesi']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Eğitim Zamanları -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputEgitimZaman">Eğitim Zamanları</label>
                                    <input class="form-control" id="inputEgitimZaman" name="egitim_zamanlari"
                                        type="text" placeholder="Örn: Hafta İçi Akşam"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['egitim_zamanlari']) : '' ?>" />
                                </div>
                                <!-- Haftalık Planlama -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputHaftalik">Haftalık Planlama</label>
                                    <input class="form-control" id="inputHaftalik" name="haftalik_planlama" type="text"
                                        placeholder="Örn: Pzt-Çar-Cum"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['haftalik_planlama']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Kurs Başlangıç -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputKursBas">Kurs Başlangıç</label>
                                    <?php
                                    $val_bas = '';
                                    if ($edit_mode && !empty($edit_data['kurs_baslangic'])) {
                                        $val_bas = date('d.m.Y', strtotime($edit_data['kurs_baslangic']));
                                    }
                                    ?>
                                    <input class="form-control" id="inputKursBas" name="kurs_baslangic" type="text"
                                        placeholder="dd.mm.yyyy" value="<?= $val_bas ?>" />
                                </div>
                                <!-- Kurs Bitiş -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputKursBit">Kurs Bitiş</label>
                                    <?php
                                    $val_bit = '';
                                    if ($edit_mode && !empty($edit_data['kurs_bitis'])) {
                                        $val_bit = date('d.m.Y', strtotime($edit_data['kurs_bitis']));
                                    }
                                    ?>
                                    <input class="form-control" id="inputKursBit" name="kurs_bitis" type="text"
                                        placeholder="dd.mm.yyyy" value="<?= $val_bit ?>" />
                                </div>
                                <!-- Seviye / Ücretlendirme -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputSeviye">Seviye / Ücretlendirme</label>
                                    <input class="form-control" id="inputSeviye" name="seviye_ucretlendirme" type="text"
                                        placeholder="A1 - 5000 TL"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['seviye_ucretlendirme']) : '' ?>" />
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
                            <h4 class="text-dark">Bilgilendirme Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered fs-14" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Diller</th>
                                        <th>Paket</th>
                                        <th>Eğitim Zamanı</th>
                                        <th>Planlama</th>
                                        <th>Kurs Süresi</th>
                                        <th>Seviye/Ücret</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($liste): ?>
                                        <?php foreach ($liste as $row):
                                            $tarih_goster = date('d.m.Y', strtotime($row['tarih']));

                                            $kurs_sure = "-";
                                            if (!empty($row['kurs_baslangic'])) {
                                                $s1 = date('d.m.Y', strtotime($row['kurs_baslangic']));
                                                $s2 = !empty($row['kurs_bitis']) ? date('d.m.Y', strtotime($row['kurs_bitis'])) : '?';
                                                $kurs_sure = "$s1 - $s2";
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $tarih_goster ?></td>
                                                <td><?= htmlspecialchars($row['diller']) ?></td>
                                                <td><?= htmlspecialchars($row['paket_tarifesi']) ?></td>
                                                <td><?= htmlspecialchars($row['egitim_zamanlari']) ?></td>
                                                <td><?= htmlspecialchars($row['haftalik_planlama']) ?></td>
                                                <td><?= $kurs_sure ?></td>
                                                <td><?= htmlspecialchars($row['seviye_ucretlendirme']) ?></td>
                                                <td class="text-center">
                                                    <a href="bilgilendirme.php?edit_id=<?= $row['id'] ?>"
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

            // DateRangePicker (Single Date)
            $('#inputTarih, #inputKursBas, #inputKursBit').daterangepicker({
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
            $('#inputTarih, #inputKursBas, #inputKursBit').each(function () {
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
                window.location.href = 'bilgilendirme.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>