<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Kitap Satış Listesi";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('kitap_satis', $sil_id);

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
    header("Location: kitap.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM kitap_satis WHERE id = '{$edit_id}'");
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

    $ad_soyad = trim($_POST['ad_soyad'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $kitap_adi = trim($_POST['kitap_adi'] ?? '');
    $fiyat = (float) ($_POST['fiyat'] ?? 0);
    $durum = $_POST['durum'] ?? 'Ödenmedi';
    $satan_id = (int) ($_POST['satan_id'] ?? 0);

    $form_id = (int) ($_POST['form_id'] ?? 0);

    if (empty($ad_soyad) || empty($kitap_adi)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Ad Soyad ve Kitap Adı alanlarını doldurunuz.'
        ];
    } else {
        $columns = ['tarih', 'ad_soyad', 'telefon', 'kitap_adi', 'fiyat', 'durum', 'satan_id'];
        $values = [$tarih, $ad_soyad, $telefon, $kitap_adi, $fiyat, $durum, $satan_id];

        if ($form_id > 0) {
            // --- GÜNCELLEME ---
            $upd = $db->update('kitap_satis', $columns, $values, 'id', $form_id);

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

            $ins = $db->insert('kitap_satis', $columns, $values);

            if ($ins['status'] == 1) {
                $_SESSION['flash_swal'] = [
                    'icon' => 'success',
                    'title' => 'Başarılı',
                    'text' => 'Kitap satışı başarıyla kaydedildi.'
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

    header("Location: kitap.php");
    exit;
}

// Oturum açan kullanıcı
$satan_adi = "Sistem Kullanıcısı";
$satan_id = 0;
if (isset($_SESSION['ad']) && isset($_SESSION['soyad'])) {
    $satan_adi = $_SESSION['ad'] . " " . $_SESSION['soyad'];
    $satan_id = $_SESSION['user_id'] ?? 0;
} elseif (isset($_SESSION['kullanici_adi'])) {
    $satan_adi = $_SESSION['kullanici_adi'];
}
if (isset($_SESSION['kisi_id']))
    $satan_id = $_SESSION['kisi_id'];

// Listeyi Çek (Sadece ilgili şube)
$sql_list = "SELECT * FROM kitap_satis WHERE sube_id = '{$sube_id}' ORDER BY id DESC";
$kitap_listesi = $db->get($sql_list);

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
                <h3 class="mb-1">Kitap Satış Sayfası</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Kitap Satış</li>
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
                                <i class="ti ti-book fs-16"></i>
                            </span>
                            <h4 class="text-dark">
                                <?= $edit_mode ? 'Satış Düzenle' : 'Yeni Kitap Satışı' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="kitap.php" class="btn btn-sm btn-warning ms-auto">
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
                                <!-- Ad Soyad -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputAdSoyad">Alan Kişi (Ad Soyad)</label>
                                    <input class="form-control" id="inputAdSoyad" name="ad_soyad" type="text"
                                        placeholder="Ad Soyad" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['ad_soyad']) : '' ?>" />
                                </div>
                                <!-- Telefon -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputTelefon">Telefon</label>
                                    <input class="form-control" id="inputTelefon" name="telefon" type="text"
                                        placeholder="05XXXXXXXXX"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['telefon']) : '' ?>" />
                                </div>
                                <!-- Kitap Adı -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputKitapAdi">Kitap Adı</label>
                                    <input class="form-control" id="inputKitapAdi" name="kitap_adi" type="text"
                                        placeholder="Kitap Adı" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['kitap_adi']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Fiyat -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputFiyat">Fiyat</label>
                                    <input class="form-control" id="inputFiyat" name="fiyat" type="number" step="0.01"
                                        placeholder="0.00" value="<?= $edit_mode ? $edit_data['fiyat'] : '' ?>" />
                                </div>
                                <!-- Durum -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="selectDurum">Durum</label>
                                    <select class="form-select select" id="selectDurum" name="durum">
                                        <?php
                                        $opts = ['Ödendi', 'Ödenmedi', 'Kapora Alındı'];
                                        foreach ($opts as $opt) {
                                            $sel = ($edit_mode && $edit_data['durum'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $sel>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <!-- Satan Kişi -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputSatan">Satan Kişi</label>
                                    <input class="form-control" id="inputSatan" type="text" value="<?= $satan_adi ?>"
                                        readonly />
                                    <input type="hidden" name="satan_id" value="<?= $satan_id ?>" />
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
                            <h4 class="text-dark">Satış Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>AD-SOYAD</th>
                                        <th>TELEFON</th>
                                        <th>KİTAP ADI</th>
                                        <th>FİYAT</th>
                                        <th>ALDIĞI TARİH</th>
                                        <th>ÖDEDİ</th>
                                        <th>SATAN</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($kitap_listesi): ?>
                                        <?php foreach ($kitap_listesi as $row):
                                            $tarih_goster = $row['tarih'];
                                            if ($row['tarih']) {
                                                $tarih_goster = date('d.m.Y', strtotime($row['tarih']));
                                            }

                                            // Durum renklendirme
                                            $badgeClass = 'bg-secondary';
                                            if ($row['durum'] == 'Ödendi')
                                                $badgeClass = 'bg-success';
                                            elseif ($row['durum'] == 'Ödenmedi')
                                                $badgeClass = 'bg-danger';
                                            elseif ($row['durum'] == 'Kapora Alındı')
                                                $badgeClass = 'bg-warning text-dark';
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['ad_soyad']) ?></td>
                                                <td><?= htmlspecialchars($row['telefon']) ?></td>
                                                <td><?= htmlspecialchars($row['kitap_adi']) ?></td>
                                                <td><?= number_format($row['fiyat'], 2) ?> ₺</td>
                                                <td><?= $tarih_goster ?></td>
                                                <td><span
                                                        class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['durum']) ?></span>
                                                </td>
                                                <td><?= $row['satan_id'] ?></td>
                                                <!-- İstenirse kullanıcı tablosundan isim çekilebilir -->
                                                <td class="text-center">
                                                    <a href="kitap.php?edit_id=<?= $row['id'] ?>"
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
                window.location.href = 'kitap.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>