<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Görüşme Listesi";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('gorusmeler', $sil_id);

        if ($del['status'] == 1) {
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
    header("Location: gorusme.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM gorusmeler WHERE id = '{$edit_id}'");
        if ($edit_data) {
            $edit_mode = true;
        }
    }
}

// --- Kaydet / Güncelle İşlemi ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kaydet'])) {

    $tarih_raw = $_POST['tarih'] ?? '';
    $tarih = '';
    if ($tarih_raw) {
        $dt = DateTime::createFromFormat('d.m.Y', $tarih_raw);
        if ($dt)
            $tarih = $dt->format('Y-m-d');
    }
    if (empty($tarih))
        $tarih = date('Y-m-d');

    $ad = trim($_POST['ad'] ?? '');
    $soyad = trim($_POST['soyad'] ?? '');
    $alan_id = (int) ($_POST['alan_id'] ?? 0);
    $referans = trim($_POST['referans'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $sonuc = trim($_POST['sonuc'] ?? '');
    $gorusen_id = (int) ($_POST['gorusen_id'] ?? 0);

    $form_id = (int) ($_POST['form_id'] ?? 0);

    if (empty($ad) || empty($soyad)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Ad ve Soyad alanlarını doldurunuz.'
        ];
    } else {
        $columns = ['tarih', 'ad', 'soyad', 'alan_id', 'referans', 'aciklama', 'sonuc', 'gorusen_id'];
        $values = [$tarih, $ad, $soyad, $alan_id, $referans, $aciklama, $sonuc, $gorusen_id];

        if ($form_id > 0) {
            // --- GÜNCELLEME ---
            $upd = $db->update('gorusmeler', $columns, $values, 'id', $form_id);

            if ($upd['status'] == 1) {
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
                    'text' => 'Kayıt hatası: ' . ($ins['error'] ?? '')
                ];
            }
        }
    }

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
    $gorusen_id = $_SESSION['user_id'] ?? 0;
} elseif (isset($_SESSION['kullanici_adi'])) {
    $gorusen_adi = $_SESSION['kullanici_adi'];
}
if (isset($_SESSION['kisi_id']))
    $gorusen_id = $_SESSION['kisi_id'];
if (isset($_SESSION['personel_id']))
    $gorusen_id = $_SESSION['personel_id'];


// Listeyi Çek (Sadece ilgili şube)
// Personel tablosu join ediliyor
$sql_list = "SELECT g.*, a.alan_adi, p.personel_adi, p.personel_soyadi 
             FROM gorusmeler g 
             LEFT JOIN alan a ON g.alan_id = a.alan_id 
             LEFT JOIN personel1 p ON g.gorusen_id = p.personel_id
             WHERE g.sube_id = '{$sube_id}'
             ORDER BY g.id DESC";
$gorusme_listesi = $db->get($sql_list);

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
                            <h4 class="text-dark">
                                <?= $edit_mode ? 'Görüşme Düzenle' : 'Yeni Görüşme Ekle' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="gorusme.php" class="btn btn-sm btn-warning ms-auto">
                                    <i class="ti ti-plus"></i> Yeni Ekleme Moduna Dön
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="form_id" value="<?= $edit_mode ? $edit_data['id'] : 0 ?>">
                            <!-- Görüşen ID gizli input -->
                            <input type="hidden" name="gorusen_id" value="<?= $gorusen_id ?>" />

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
                                        value="<?= $val_tarih ?>" readonly />
                                </div>
                                <!-- Adı -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputAd">Adı</label>
                                    <input class="form-control" id="inputAd" name="ad" type="text" placeholder="Adı"
                                        required value="<?= $edit_mode ? htmlspecialchars($edit_data['ad']) : '' ?>" />
                                </div>
                                <!-- Soyadı -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputSoyad">Soyadı</label>
                                    <input class="form-control" id="inputSoyad" name="soyad" type="text"
                                        placeholder="Soyadı" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['soyad']) : '' ?>" />
                                </div>
                                <!-- Dil (Alan) -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="selectDil">Dil / Alan</label>
                                    <select class="form-select select" id="selectDil" name="alan_id">
                                        <option selected disabled value="">Seçiniz</option>
                                        <?php if ($alanlar): ?>
                                            <?php foreach ($alanlar as $alan) {
                                                $selected = ($edit_mode && $edit_data['alan_id'] == $alan['alan_id']) ? 'selected' : '';
                                                ?>
                                                <option value="<?= $alan['alan_id']; ?>" <?= $selected ?>>
                                                    <?= $alan['alan_adi']; ?>
                                                </option>
                                            <?php } ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Referans -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputReferans">Referans</label>
                                    <input class="form-control" id="inputReferans" name="referans" type="text"
                                        placeholder="Referans"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['referans']) : '' ?>" />
                                </div>
                                <!-- Açıklama -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputAciklama">Açıklama</label>
                                    <input class="form-control" id="inputAciklama" name="aciklama" type="text"
                                        placeholder="Görüşme notları..."
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['aciklama']) : '' ?>" />
                                </div>
                                <!-- Sonuç -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="selectSonuc">Sonuç</label>
                                    <select class="form-select select" id="selectSonuc" name="sonuc">
                                        <?php
                                        $sonuc_opts = ['Kayıt yapıldı', 'Beklemede', 'Randevu verildi', 'Olumsuz'];
                                        foreach ($sonuc_opts as $opt) {
                                            $sel = ($edit_mode && $edit_data['sonuc'] == $opt) ? 'selected' : '';
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
                            <h4 class="text-dark">Görüşme Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered fs-14" id="datatablesSimple">
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
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($gorusme_listesi): ?>
                                        <?php foreach ($gorusme_listesi as $row):
                                            $tarih_goster = $row['tarih'];
                                            if ($row['tarih']) {
                                                $tarih_goster = date('d.m.Y', strtotime($row['tarih']));
                                            }

                                            // Renklendirme - Inline Style ile Garanti Altına Alma
                                            $rowStyle = "";
                                            $textClass = "";

                                            // Normalleştirme (boşluk temizleme vs.)
                                            $sonuc = trim($row['sonuc']);

                                            if ($sonuc == 'Kayıt yapıldı') {
                                                // Mavi
                                                $rowStyle = "background-color: #0d6efd !important; color: white !important;";
                                                $textClass = "text-white";
                                            } elseif ($sonuc == 'Beklemede') {
                                                // Yeşil
                                                $rowStyle = "background-color: #198754 !important; color: white !important;";
                                                $textClass = "text-white";
                                            } elseif ($sonuc == 'Randevu verildi') {
                                                // Gri
                                                $rowStyle = "background-color: #6c757d !important; color: white !important;";
                                                $textClass = "text-white";
                                            } elseif ($sonuc == 'Olumsuz') {
                                                // Kırmızı
                                                $rowStyle = "background-color: #dc3545 !important; color: white !important;";
                                                $textClass = "text-white";
                                            }

                                            // Personel Adı
                                            $personel_tam_ad = trim(($row['personel_adi'] ?? '') . ' ' . ($row['personel_soyadi'] ?? ''));
                                            if (empty($personel_tam_ad))
                                                $personel_tam_ad = "-";
                                            ?>
                                            <tr style="<?= $rowStyle ?>">
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>"><?= $tarih_goster ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['ad']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['soyad']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['alan_adi'] ?? '') ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['referans']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['aciklama']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['sonuc']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($personel_tam_ad) ?></td>
                                                <td class="text-center" style="<?= $rowStyle ?>">
                                                    <a href="gorusme.php?edit_id=<?= $row['id'] ?>"
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
                                            <td colspan="9" class="text-center">Henüz kayıt bulunmamaktadır.</td>
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
                window.location.href = 'gorusme.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>