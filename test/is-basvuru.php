<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "İş Başvuruları";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('is_basvurulari', $sil_id);

        if ($del['status'] == 1) {
            $_SESSION['flash_swal'] = [
                'icon' => 'success',
                'title' => 'Silindi',
                'text' => 'Başvuru başarıyla silindi.'
            ];
        } else {
            $_SESSION['flash_swal'] = [
                'icon' => 'error',
                'title' => 'Hata',
                'text' => 'Silme işlemi başarısız oldu.'
            ];
        }
    }
    header("Location: is-basvuru.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM is_basvurulari WHERE id = '{$edit_id}'");
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
    $brans = trim($_POST['brans'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $egitim_sekli = trim($_POST['egitim_sekli'] ?? '');
    $teklif_edilen = trim($_POST['teklif_edilen'] ?? '');
    $talep_edilen = trim($_POST['talep_edilen'] ?? '');
    $adres = trim($_POST['adres'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');

    $form_id = (int) ($_POST['form_id'] ?? 0);

    if (empty($ad_soyad)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Ad Soyad alanını doldurunuz.'
        ];
    } else {
        $columns = ['tarih', 'ad_soyad', 'brans', 'telefon', 'egitim_sekli', 'teklif_edilen', 'talep_edilen', 'adres', 'aciklama'];
        $values = [$tarih, $ad_soyad, $brans, $telefon, $egitim_sekli, $teklif_edilen, $talep_edilen, $adres, $aciklama];

        if ($form_id > 0) {
            // --- GÜNCELLEME ---
            $upd = $db->update('is_basvurulari', $columns, $values, 'id', $form_id);

            if ($upd['status'] == 1) {
                $_SESSION['flash_swal'] = [
                    'icon' => 'success',
                    'title' => 'Güncellendi',
                    'text' => 'Başvuru başarıyla güncellendi.'
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

            $ins = $db->insert('is_basvurulari', $columns, $values);

            if ($ins['status'] == 1) {
                $_SESSION['flash_swal'] = [
                    'icon' => 'success',
                    'title' => 'Başarılı',
                    'text' => 'İş başvurusu başarıyla kaydedildi.'
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

    header("Location: is-basvuru.php");
    exit;
}

// Listeyi Çek (Sadece ilgili şube)
$sql_list = "SELECT * FROM is_basvurulari WHERE sube_id = '{$sube_id}' ORDER BY id DESC";
$basvuru_listesi = $db->get($sql_list);

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
                <h3 class="mb-1">İş Başvuruları</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">İş Başvuruları</li>
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
                                <i class="ti ti-briefcase fs-16"></i>
                            </span>
                            <h4 class="text-dark">
                                <?= $edit_mode ? 'Başvuru Düzenle' : 'Yeni İş Başvurusu' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="is-basvuru.php" class="btn btn-sm btn-warning ms-auto">
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
                                    <label class="form-label" for="inputAdSoyad">Ad Soyad</label>
                                    <input class="form-control" id="inputAdSoyad" name="ad_soyad" type="text"
                                        placeholder="Ad Soyad" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['ad_soyad']) : '' ?>" />
                                </div>
                                <!-- Branş -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputBrans">Branşı</label>
                                    <input class="form-control" id="inputBrans" name="brans" type="text"
                                        placeholder="Örn: İngilizce Öğretmeni"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['brans']) : '' ?>" />
                                </div>
                                <!-- Telefon -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputTelefon">Telefon Numarası</label>
                                    <input class="form-control" id="inputTelefon" name="telefon" type="text"
                                        placeholder="05XXXXXXXXX"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['telefon']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Eğitim Şekli -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputEgitimSekli">Eğitim Şekli</label>
                                    <input class="form-control" id="inputEgitimSekli" name="egitim_sekli" type="text"
                                        placeholder="Örn: Yüz yüze / Online"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['egitim_sekli']) : '' ?>" />
                                </div>
                                <!-- Teklif Edilen -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputTeklif">Teklif Edilen</label>
                                    <input class="form-control" id="inputTeklif" name="teklif_edilen" type="text"
                                        placeholder="Örn: 120tl"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['teklif_edilen']) : '' ?>" />
                                </div>
                                <!-- Talep Edilen -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputTalep">Talep Edilen</label>
                                    <input class="form-control" id="inputTalep" name="talep_edilen" type="text"
                                        placeholder="Örn: 150tl"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['talep_edilen']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Adres -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputAdres">Adres</label>
                                    <textarea class="form-control" id="inputAdres" name="adres" rows="2"
                                        placeholder="Adres bilgisi..."><?= $edit_mode ? htmlspecialchars($edit_data['adres']) : '' ?></textarea>
                                </div>
                                <!-- Açıklama -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="inputAciklama">Açıklama</label>
                                    <textarea class="form-control" id="inputAciklama" name="aciklama" rows="2"
                                        placeholder="Ek açıklamalar..."><?= $edit_mode ? htmlspecialchars($edit_data['aciklama']) : '' ?></textarea>
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
                            <h4 class="text-dark">Başvuru Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>TARİH</th>
                                        <th>AD SOYAD</th>
                                        <th>BRANŞ</th>
                                        <th>TELEFON</th>
                                        <th>EĞİTİM ŞEKLİ</th>
                                        <th>TEKLİF</th>
                                        <th>TALEP</th>
                                        <th>ADRES</th>
                                        <th>AÇIKLAMA</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($basvuru_listesi): ?>
                                        <?php foreach ($basvuru_listesi as $row):
                                            $tarih_goster = $row['tarih'];
                                            if ($row['tarih']) {
                                                $tarih_goster = date('d.m.Y', strtotime($row['tarih']));
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $tarih_goster ?></td>
                                                <td><?= htmlspecialchars($row['ad_soyad']) ?></td>
                                                <td><?= htmlspecialchars($row['brans']) ?></td>
                                                <td><?= htmlspecialchars($row['telefon']) ?></td>
                                                <td><?= htmlspecialchars($row['egitim_sekli']) ?></td>
                                                <td><?= htmlspecialchars($row['teklif_edilen']) ?></td>
                                                <td><?= htmlspecialchars($row['talep_edilen']) ?></td>
                                                <td><?= htmlspecialchars($row['adres']) ?></td>
                                                <td><?= htmlspecialchars($row['aciklama']) ?></td>
                                                <td class="text-center">
                                                    <a href="is-basvuru.php?edit_id=<?= $row['id'] ?>"
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
                                            <td colspan="10" class="text-center">Henüz başvuru kaydı bulunmamaktadır.</td>
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
            text: "Bu başvuruyu silmek istediğinize emin misiniz? Bu işlem geri alınamaz!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'is-basvuru.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>