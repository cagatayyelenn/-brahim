<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Şirketler ve İşbirlikleri";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('sirketler_isbirlikleri', $sil_id);

        if ($del['status'] == 1) {
            $db->log('sirketler_isbirlikleri', $sil_id, 'SİLME', 'Şirket/İşbirliği kaydı silindi.');
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
    header("Location: sirketler.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM sirketler_isbirlikleri WHERE id = '{$edit_id}'");
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

    $firma_adi = trim($_POST['firma_adi'] ?? '');
    $dil_secenekleri = trim($_POST['dil_secenekleri'] ?? '');
    $yetkili_ad_soyad = trim($_POST['yetkili_ad_soyad'] ?? '');
    $muhasebe_yetkilisi = trim($_POST['muhasebe_yetkilisi'] ?? '');
    $referans = trim($_POST['referans'] ?? '');
    $egitim_sekli = trim($_POST['egitim_sekli'] ?? '');
    $anlasma_detay = trim($_POST['anlasma_detay'] ?? '');
    $sonuc = $_POST['sonuc'] ?? 'Beklemede';

    // Görüşen kişi Session'dan
    $gorusen_id = $_SESSION['personel_id'] ?? 0;

    $form_id = (int) ($_POST['form_id'] ?? 0);

    if (empty($firma_adi)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Firma Adı alanını doldurunuz.'
        ];
    } else {
        $columns = [
            'tarih',
            'firma_adi',
            'dil_secenekleri',
            'yetkili_ad_soyad',
            'muhasebe_yetkilisi',
            'referans',
            'egitim_sekli',
            'anlasma_detay',
            'sonuc',
            'gorusen_id'
        ];
        $values = [
            $tarih,
            $firma_adi,
            $dil_secenekleri,
            $yetkili_ad_soyad,
            $muhasebe_yetkilisi,
            $referans,
            $egitim_sekli,
            $anlasma_detay,
            $sonuc,
            $gorusen_id
        ];

        if ($form_id > 0) {
            // --- GÜNCELLEME ---
            $upd = $db->update('sirketler_isbirlikleri', $columns, $values, 'id', $form_id);

            if ($upd['status'] == 1) {
                $db->log('sirketler_isbirlikleri', $form_id, 'GÜNCELLEME', "$firma_adi işbirliği kaydı güncellendi.");
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

            $ins = $db->insert('sirketler_isbirlikleri', $columns, $values);

            if ($ins['status'] == 1) {
                $new_id = $ins['id'] ?? 0;
                $db->log('sirketler_isbirlikleri', $new_id, 'EKLEME', "$firma_adi yeni işbirliği eklendi.");
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

    header("Location: sirketler.php");
    exit;
}

// Oturum açan kullanıcı (Görsel amaçlı formda göstermek için)
$gorusen_adi = "Sistem Kullanıcısı";
if (isset($_SESSION['ad']) && isset($_SESSION['soyad'])) {
    $gorusen_adi = $_SESSION['ad'] . " " . $_SESSION['soyad'];
} elseif (isset($_SESSION['kullanici_adi'])) {
    $gorusen_adi = $_SESSION['kullanici_adi'];
}

// Listeyi Çek (Sadece ilgili şube ve personel ismiyle join)
$sql_list = "SELECT s.*, p.personel_adi, p.personel_soyadi 
             FROM sirketler_isbirlikleri s 
             LEFT JOIN personel1 p ON s.gorusen_id = p.personel_id
             WHERE s.sube_id = '{$sube_id}'
             ORDER BY s.id DESC";
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
                <h3 class="mb-1">Şirketler ve İşbirlikleri</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Şirketler ve İşbirlikleri</li>
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
                                <i class="ti ti-building-skyscraper fs-16"></i>
                            </span>
                            <h4 class="text-dark">
                                <?= $edit_mode ? 'Kayıt Düzenle' : 'Yeni Kayıt Oluştur' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="sirketler.php" class="btn btn-sm btn-warning ms-auto">
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
                                <!-- Firma Adı -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputFirma">Firma Adı</label>
                                    <input class="form-control" id="inputFirma" name="firma_adi" type="text"
                                        placeholder="Firma Adı" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['firma_adi']) : '' ?>" />
                                </div>
                                <!-- Dil Seçeneği -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputDil">Dil Seçeneği</label>
                                    <input class="form-control" id="inputDil" name="dil_secenekleri" type="text"
                                        placeholder="Örn: İngilizce, Almanca"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['dil_secenekleri']) : '' ?>" />
                                </div>
                                <!-- Yetkili -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputYetkili">Görüşülen Yetkili</label>
                                    <input class="form-control" id="inputYetkili" name="yetkili_ad_soyad" type="text"
                                        placeholder="Ad Soyad"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['yetkili_ad_soyad']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Muhasebe -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputMuhasebe">Görüşülen Muhasebe</label>
                                    <input class="form-control" id="inputMuhasebe" name="muhasebe_yetkilisi" type="text"
                                        placeholder="Ad Soyad"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['muhasebe_yetkilisi']) : '' ?>" />
                                </div>
                                <!-- Referans -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputReferans">Referans</label>
                                    <input class="form-control" id="inputReferans" name="referans" type="text"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['referans']) : '' ?>" />
                                </div>
                                <!-- Eğitim Şekli -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputEgitimSekli">Eğitim Şekli</label>
                                    <input class="form-control" id="inputEgitimSekli" name="egitim_sekli" type="text"
                                        placeholder="Örn: Online, Yüz Yüze"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['egitim_sekli']) : '' ?>" />
                                </div>
                                <!-- Sonuç -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="selectSonuc">Sonuç</label>
                                    <select class="form-select select" id="selectSonuc" name="sonuc">
                                        <?php
                                        $sonuc_opts = ['Anlaşma Sağlandı', 'Beklemede', 'Olumsuz'];
                                        foreach ($sonuc_opts as $opt) {
                                            $sel = ($edit_mode && $edit_data['sonuc'] == $opt) ? 'selected' : '';
                                            echo "<option value='$opt' $sel>$opt</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Açıklama -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" for="textAciklama">Anlaşma Detay / Açıklama</label>
                                    <textarea class="form-control" id="textAciklama" name="anlasma_detay"
                                        rows="3"><?= $edit_mode ? htmlspecialchars($edit_data['anlasma_detay']) : '' ?></textarea>
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
                                        <th>Firma Adı</th>
                                        <th>Dil</th>
                                        <th>Yetkili</th>
                                        <th>Eğitim Şekli</th>
                                        <th>Sonuç</th>
                                        <th>Görüşen</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($liste): ?>
                                        <?php foreach ($liste as $row):
                                            $tarih_goster = $row['tarih'];
                                            if ($row['tarih']) {
                                                $tarih_goster = date('d.m.Y', strtotime($row['tarih']));
                                            }

                                            // Renklendirme
                                            $rowStyle = "";
                                            $textClass = "";
                                            $sonuc = trim($row['sonuc']);

                                            if ($sonuc == 'Anlaşma Sağlandı') {
                                                $rowStyle = "background-color: #198754 !important; color: white !important;";
                                                $textClass = "text-white";
                                            } elseif ($sonuc == 'Beklemede') {
                                                $rowStyle = "background-color: #ffc107 !important; color: black !important;";
                                                $textClass = "text-dark";
                                            } elseif ($sonuc == 'Olumsuz') {
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
                                                    <?= htmlspecialchars($row['firma_adi']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['dil_secenekleri']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['yetkili_ad_soyad']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['egitim_sekli']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($row['sonuc']) ?></td>
                                                <td class="<?= $textClass ?>" style="<?= $rowStyle ?>">
                                                    <?= htmlspecialchars($personel_tam_ad) ?></td>
                                                <td class="text-center" style="<?= $rowStyle ?>">
                                                    <a href="sirketler.php?edit_id=<?= $row['id'] ?>"
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
                window.location.href = 'sirketler.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>