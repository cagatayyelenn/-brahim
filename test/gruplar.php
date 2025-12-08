<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Tüm Gruplar";

// Şube ID'sini al
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// --- Silme İşlemi ---
if (isset($_GET['sil_id'])) {
    $sil_id = (int) $_GET['sil_id'];
    if ($sil_id > 0) {
        $del = $db->delete('gruplar', $sil_id);

        if ($del['status'] == 1) {
            $db->log('gruplar', $sil_id, 'SİLME', 'Grup kaydı silindi.');
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
    header("Location: gruplar.php");
    exit;
}

// --- Düzenleme Modu Kontrolü ---
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $edit_id = (int) $_GET['edit_id'];
    if ($edit_id > 0) {
        $edit_data = $db->gets("SELECT * FROM gruplar WHERE id = '{$edit_id}'");
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

    $grup_adi = trim($_POST['grup_adi'] ?? '');
    $secilen_dil = trim($_POST['secilen_dil'] ?? '');
    $ders_gun_saatleri = trim($_POST['ders_gun_saatleri'] ?? '');
    $katilan_kisiler = trim($_POST['katilan_kisiler'] ?? '');
    $katilim_durumu = $_POST['katilim_durumu'] ?? 'Devam Ediyor';
    $referans = trim($_POST['referans'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');

    // Sistem Kullanıcısı Session'dan
    $sistem_kullanicisi_id = $_SESSION['personel_id'] ?? 0;

    $form_id = (int) ($_POST['form_id'] ?? 0);

    // Basit Validasyon
    if (empty($grup_adi)) {
        $_SESSION['flash_swal'] = [
            'icon' => 'warning',
            'title' => 'Eksik Bilgi',
            'text' => 'Lütfen Grup Adı alanını doldurunuz.'
        ];
    } else {
        $columns = [
            'tarih',
            'grup_adi',
            'secilen_dil',
            'ders_gun_saatleri',
            'katilan_kisiler',
            'katilim_durumu',
            'referans',
            'aciklama',
            'baslama_tarihi',
            'bitis_tarihi',
            'sistem_kullanicisi_id'
        ];
        $values = [
            $tarih,
            $grup_adi,
            $secilen_dil,
            $ders_gun_saatleri,
            $katilan_kisiler,
            $katilim_durumu,
            $referans,
            $aciklama,
            $baslama_tarihi,
            $bitis_tarihi,
            $sistem_kullanicisi_id
        ];

        if ($form_id > 0) {
            // --- GÜNCELLEME ---
            $upd = $db->update('gruplar', $columns, $values, 'id', $form_id);

            if ($upd['status'] == 1) {
                $db->log('gruplar', $form_id, 'GÜNCELLEME', "$grup_adi grup kaydı güncellendi.");
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

            $ins = $db->insert('gruplar', $columns, $values);

            if ($ins['status'] == 1) {
                $new_id = $ins['id'] ?? 0;
                $db->log('gruplar', $new_id, 'EKLEME', "$grup_adi yeni grup eklendi.");
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

    header("Location: gruplar.php");
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
$sql_list = "SELECT g.*, p.personel_adi, p.personel_soyadi 
             FROM gruplar g 
             LEFT JOIN personel1 p ON g.sistem_kullanicisi_id = p.personel_id
             WHERE g.sube_id = '{$sube_id}'
             ORDER BY g.id DESC";
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
                <h3 class="mb-1">Tüm Gruplar</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Tüm Gruplar</li>
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
                                <?= $edit_mode ? 'Kayıt Düzenle' : 'Yeni Grup Oluştur' ?>
                            </h4>
                            <?php if ($edit_mode): ?>
                                <a href="gruplar.php" class="btn btn-sm btn-warning ms-auto">
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
                                <!-- Grup Adı -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="inputGrupAdi">Grup Adı</label>
                                    <input class="form-control" id="inputGrupAdi" name="grup_adi" type="text"
                                        placeholder="Grup Adı" required
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['grup_adi']) : '' ?>" />
                                </div>
                                <!-- Seçilen Dil -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputDil">Seçilen Dil</label>
                                    <input class="form-control" id="inputDil" name="secilen_dil" type="text"
                                        placeholder="İngilizce, Almanca vb."
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['secilen_dil']) : '' ?>" />
                                </div>
                                <!-- Ders Gün ve Saatleri -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="inputGunSaat">Ders Gün ve Saatleri</label>
                                    <input class="form-control" id="inputGunSaat" name="ders_gun_saatleri" type="text"
                                        placeholder="Pzt-Çar 19:00"
                                        value="<?= $edit_mode ? htmlspecialchars($edit_data['ders_gun_saatleri']) : '' ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <!-- Gruba Katılan Kişiler -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="textKisiler">Gruba Katılan Kişi Bilgileri</label>
                                    <textarea class="form-control" id="textKisiler" name="katilan_kisiler" rows="3"
                                        placeholder="Ad Soyad, Telefon vb."><?= $edit_mode ? htmlspecialchars($edit_data['katilan_kisiler']) : '' ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <!-- Durum -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="selectDurum">Katılım Durumu</label>
                                            <select class="form-select select" id="selectDurum" name="katilim_durumu">
                                                <?php
                                                $durum_opts = ['Katıldı', 'Katılmadı', 'Devam Ediyor'];
                                                foreach ($durum_opts as $opt) {
                                                    $sel = ($edit_mode && $edit_data['katilim_durumu'] == $opt) ? 'selected' : '';
                                                    echo "<option value='$opt' $sel>$opt</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <!-- Referans -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="inputReferans">Referans</label>
                                            <input class="form-control" id="inputReferans" name="referans" type="text"
                                                value="<?= $edit_mode ? htmlspecialchars($edit_data['referans']) : '' ?>" />
                                        </div>
                                        <!-- Başlama Tarihi -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="inputBaslama">Grup Başlama</label>
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
                                            <label class="form-label" for="inputBitis">Grup Bitiş</label>
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

                            <div class="row">
                                <!-- Açıklama -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" for="textAciklama">Açıklama</label>
                                    <textarea class="form-control" id="textAciklama" name="aciklama"
                                        rows="2"><?= $edit_mode ? htmlspecialchars($edit_data['aciklama']) : '' ?></textarea>
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
                            <h4 class="text-dark">Grup Listesi</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered fs-14" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Grup Adı</th>
                                        <th>Dil / Gün Saat</th>
                                        <th>Kişi Bilgileri</th>
                                        <th>Durum</th>
                                        <th>Başlama/Bitiş</th>
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

                                            // Renklendirme (Opsiyonel)
                                            $badgeClass = "bg-secondary";
                                            if ($row['katilim_durumu'] == 'Katıldı')
                                                $badgeClass = "bg-success";
                                            if ($row['katilim_durumu'] == 'Katılmadı')
                                                $badgeClass = "bg-danger";
                                            if ($row['katilim_durumu'] == 'Devam Ediyor')
                                                $badgeClass = "bg-primary";
                                            ?>
                                            <tr>
                                                <td><?= $tarih_goster ?></td>
                                                <td><?= htmlspecialchars($row['grup_adi']) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row['secilen_dil']) ?></strong><br>
                                                    <small><?= htmlspecialchars($row['ders_gun_saatleri']) ?></small>
                                                </td>
                                                <td><?= nl2br(htmlspecialchars($row['katilan_kisiler'])) ?></td>
                                                <td><span
                                                        class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['katilim_durumu']) ?></span>
                                                </td>
                                                <td><?= $sure ?></td>
                                                <td><?= htmlspecialchars($personel_tam_ad) ?></td>
                                                <td class="text-center">
                                                    <a href="gruplar.php?edit_id=<?= $row['id'] ?>"
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
                window.location.href = 'gruplar.php?sil_id=' + id;
            }
        })
    }
</script>
</body>

</html>