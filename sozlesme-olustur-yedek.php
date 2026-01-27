<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

if (!isset($_GET['id']) || $_GET['id'] === '') {
    header("Location: ogrenciler.php"); // listeye dön
    exit;
}

$sel_donem = $sel_sinif = $sel_grup = $sel_alan = $sel_birim = null;

$donemler = $db->finds('donem', null, null, ['donem_id', 'donem_adi']);
$siniflar = $db->finds('sinif', null, null, ['sinif_id', 'sinif_adi']);
$gruplar = $db->finds('grup', null, null, ['grup_id', 'grup_adi']);
$alanlar = $db->finds('alan', null, null, ['alan_id', 'alan_adi']);
$birimler = $db->finds('birim', null, null, ['birim_id', 'birim_adi']);

$odemeYontemleri = $db->finds('odeme_yontem1', 'durum', 1, ['yontem_id', 'yontem_adi', 'sira']);
usort($odemeYontemleri, fn($a, $b) => ($a['sira'] <=> $b['sira']));

// Kasaları Çek
$aktifSubeId = (int) ($_SESSION['sube_id'] ?? 0);

if ($aktifSubeId) {
    $kasalar = $db->get(
        "SELECT kasa_id, kasa_adi, kasa_tipi FROM kasa1
       WHERE durum=1 AND (sube_id=:sid OR sube_id IS NULL)
       ORDER BY sira ASC, kasa_adi ASC",
        [':sid' => $aktifSubeId]
    );
} else {
    $kasalar = $db->finds('kasa1', 'durum', 1, ['kasa_id', 'kasa_adi', 'kasa_tipi']);
    usort($kasalar, fn($a, $b) => ($a['sira'] <=> $b['sira']) ?: strcmp($a['kasa_adi'], $b['kasa_adi']));
}

$ogr_no = $_GET['id'];                         // ogrenci_numara
$quoted = $db->conn->quote($ogr_no);           // gets/get param bağlamıyor; quote ile güvene al

$sql = "
    SELECT 
        o.*,
        il.il_adi,
        ilce.ilce_adi
    FROM ogrenci1 o
    LEFT JOIN il ON il.il_id = o.il_id
    LEFT JOIN ilce ON ilce.ilce_id = o.ilce_id
    WHERE o.ogrenci_numara = :numara
    LIMIT 1
";

$ogrenci = $db->gets($sql, ['numara' => $ogr_no]);



$pageTitle = 'Sözleşme Oluştur ';
$page_styles[] = ['href' => 'assets/plugins/summernote/summernote-lite.min.css'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';

?>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
                    <div class="my-auto mb-2">
                        <h3 class="page-title mb-1">Öğrenci Detay Bilgileri</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="index.php">Anasayfa</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="ogrenci-listesi.php">Öğrenci Listesi</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Sözleşme Oluştur</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xxl-3 col-xl-4  ">
                <div class="card border-white">
                    <div class="card-header">
                        <div class="d-flex align-items-center flex-wrap row-gap-3">
                            <div
                                class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                <img src="https://ui-avatars.com/api/?name=<?= $ogrenci['ogrenci_adi'] ?>+<?= $ogrenci['ogrenci_soyadi'] ?>"
                                    class="img-fluid" alt="img">
                            </div>
                            <div class="overflow-hidden">
                                <?php
                                if ($ogrenci['aktif'] == 1) {
                                    echo '<span class="badge badge-soft-success"><i class="ti ti-circle-filled"></i> Aktif</span>';
                                } else {
                                    echo '<span class="badge badge-soft-secondary"><i class="ti ti-circle-filled"></i> Pasif</span>';
                                }

                                ?>
                                <h5 class="mb-1 text-truncate">
                                    <?= $ogrenci['ogrenci_adi'] . " " . $ogrenci['ogrenci_soyadi'] ?></h5>
                                <p class="text-primary"><?= $ogrenci['ogrenci_numara'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <h5 class="mb-3">Öğrenci bilgileri</h5>
                        <?php
                        $adSoyad = trim(($ogrenci['ogrenci_adi'] ?? '') . ' ' . ($ogrenci['ogrenci_soyadi'] ?? ''));
                        $cinsiyetT = ($ogrenci['ogrenci_cinsiyet'] === '1' || $ogrenci['ogrenci_cinsiyet'] === 1) ? 'Erkek'
                            : (($ogrenci['ogrenci_cinsiyet'] === '0' || $ogrenci['ogrenci_cinsiyet'] === 0) ? 'Kız' : '-');
                        ?>
                        <dl class="row mb-0">
                            <dt class="col-6 fw-medium text-dark mb-3">T.C. Kimlik</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($ogrenci['ogrenci_tc'] ?? '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Ad Soyad</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($adSoyad ?: '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Cinsiyet</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($cinsiyetT) ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Doğum Tarihi</dt>
                            <dd class="col-6 mb-3"><?= formatDateTR($ogrenci['ogrenci_dogumtar']); ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Telefon</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($ogrenci['ogrenci_tel'] ?? '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">E-posta</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($ogrenci['ogrenci_mail'] ?? '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">İl / İlçe</dt>
                            <dd class="col-6 mb-3">
                                <?= htmlspecialchars($ogrenci['il_adi'] ?? '-') ?> /
                                <?= htmlspecialchars($ogrenci['ilce_adi'] ?? '-') ?>
                            </dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Adres</dt>
                            <dd class="col-6 mb-3"><?= nl2br(htmlspecialchars($ogrenci['ogrenci_adres'] ?? '-')) ?></dd>
                        </dl>
                        <a href="ogrenci-detay.php?id=<?= $ogr_no ?>" class="btn btn-primary btn-sm w-100 mt-3">
                            <i class="ti ti-file-plus me-2"></i>Öğrenci Sayfasına Git
                        </a>
                    </div>
                </div>


                <div class="card border-white">
                    <div class="card-body">
                        <h5 class="mb-3">Sözleşme Bilgileri</h5>
                        <dl class="row mb-0">
                            <!-- SÖZLEŞME ÖZETİ -->
                            <dt class="col-6 fw-medium text-dark mb-3">Sözleşme</dt>
                            <dd class="col-6 mb-3">
                                <?php if ($sozlesmeSay > 0): ?>
                                    <span class="badge bg-success">Var (<?= $sozlesmeSay ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Yok</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Toplam Taksit</dt>
                            <dd class="col-6 mb-3"><?= $taksitSay ?></dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Ödenen Taksit</dt>
                            <dd class="col-6 mb-3"><?= $odenenSay ?></dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Gecikmiş Taksit</dt>
                            <dd class="col-6 mb-3">
                                <?= $gecikenSay ?>
                                <?php if ($gecikenSay > 0): ?>
                                    <a href="taksit-ode.php?ogrenci_id=<?= $ogrenci_id ?>&durum=geciken"
                                        class="btn btn-sm btn-outline-danger ms-2">
                                        Öde
                                    </a>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Kalan Taksit</dt>
                            <dd class="col-6 mb-3"><?= $kalanSay ?></dd>
                        </dl>
                    </div>
                </div>
            </div>


            <div class="col-xxl-9 col-xl-8">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body pb-0">
                                <!-- kurs bilgisi  -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <div class="d-flex align-items-center">
                                            <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                                                <i class="ti ti-user-check fs-16"></i>
                                            </span>
                                            <h4 class="text-dark">Öğrenci Kurs Bilgisi</h4>
                                        </div>
                                    </div>

                                    <div class="card-body pb-0">
                                        <div class="info-section kurs-section">
                                            <div class="row">
                                                <!-- Dönem -->
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Dönem</label>
                                                        <select class="form-control" name="donem_id" id="donem_id">
                                                            <option value="">Seçiniz</option>
                                                            <?php foreach ($donemler as $d): ?>
                                                                <option value="<?= (int) $d['donem_id'] ?>">
                                                                    <?= htmlspecialchars($d['donem_adi']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Sınıf -->
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Sınıf</label>
                                                        <select class="form-control" name="sinif_id" id="sinif_id">
                                                            <option value="">Seçiniz</option>
                                                            <?php foreach ($siniflar as $s): ?>
                                                                <option value="<?= (int) $s['sinif_id'] ?>">
                                                                    <?= htmlspecialchars($s['sinif_adi']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Grup -->
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Grup</label>
                                                        <select class="form-control" name="grup_id" id="grup_id">
                                                            <option value="">Seçiniz</option>
                                                            <?php foreach ($gruplar as $g): ?>
                                                                <option value="<?= (int) $g['grup_id'] ?>">
                                                                    <?= htmlspecialchars($g['grup_adi']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Alan -->
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Alan</label>
                                                        <select class="form-control" name="alan_id" id="alan_id">
                                                            <option value="">Seçiniz</option>
                                                            <?php foreach ($alanlar as $a): ?>
                                                                <option value="<?= (int) $a['alan_id'] ?>">
                                                                    <?= htmlspecialchars($a['alan_adi']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-2 pb-2 bg-light-transparent">
                                        <div class="d-flex justify-content-end" style="padding-right:18px;">
                                            <button type="button" data-section="kurs_section" id="btnKursDuzenle"
                                                class="btn btn-outline-secondary me-2" style="display:none;">
                                                Düzenle
                                            </button>
                                            <button type="button" data-section="kurs_section" id="btnKursDevam"
                                                class="btn btn-outline-info">
                                                Devam Et
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- /kurs bilgisi -->

                                <!-- KURS SÖZLEŞME BİLGİSİ -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <div class="d-flex align-items-center">
                                            <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                                                <i class="ti ti-user-check fs-16"></i>
                                            </span>
                                            <h4 class="text-dark">Kurs Sözleşme Bilgisi</h4>
                                        </div>
                                    </div>

                                    <div class="card-body pb-0">
                                        <div class="info-section">
                                            <div class="row">
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Birim Seçiniz</label>
                                                        <select class="select2" name="birim_id" id="birimId">
                                                            <option value="">Seçiniz</option>
                                                            <?php foreach ($birimler as $b): ?>
                                                                <option value="<?= (int) $b['birim_id'] ?>"
                                                                    <?= ($sel_birim == $b['birim_adi']) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($b['birim_adi']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Birim Fiyatı</label>
                                                        <input type="text" name="birimFiyat"
                                                            class="form-control text-end" id="birimFiyat"
                                                            placeholder="Örn: 5.000,00">
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Miktar</label>
                                                        <input type="number" class="form-control text-end" name="miktar"
                                                            id="miktar" min="1" step="1" placeholder="Örn: 10">
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Toplam Tutar</label>
                                                        <input type="text" name="toplamTutar"
                                                            class="form-control text-end" id="toplamTutar" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-2 pb-2 bg-light-transparent">
                                        <div class="text-end" style="padding-right: 18px;">
                                            <button type="button" id="btnHesapla"
                                                class="btn btn-outline-primary">Ödemeyi Hesapla</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- ÖDEME BİLGİSİ (ilk başta görünür; alanlar JS ile doldurulacak) -->
                                <div class="card" id="odemeCard">
                                    <div class="card-header bg-light">
                                        <div class="d-flex align-items-center">
                                            <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                                                <i class="ti ti-user-check fs-16"></i>
                                            </span>
                                            <h4 class="text-dark">Ödeme Bilgisi</h4>
                                        </div>
                                    </div>
                                    <div class="card-body pb-0">
                                        <div class="info-section">
                                            <div class="row">
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="mb-4">
                                                        <label class="form-label">Birim Tutar</label>
                                                        <input type="text" class="form-control text-end text-success"
                                                            id="odmBirimTutar" name="odmBirimTutar" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="mb-4">
                                                        <label class="form-label">Tutar</label>
                                                        <input type="text" class="form-control text-end text-success"
                                                            id="odmTutar" name="odmTutar" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="mb-4">
                                                        <label class="form-label">Toplam Tutar</label>
                                                        <input type="text" class="form-control text-end text-success"
                                                            id="odmToplam" name="odmToplam" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-6">
                                                    <div class="mb-4">
                                                        <label class="form-label">Peşinat Tutarı</label>
                                                        <input type="text" class="form-control text-end" id="pesinat"
                                                            name="pesinat" placeholder="Örn: 2.000,00">
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-6">
                                                    <div class="mb-4">
                                                        <label class="form-label">Ödeme Seçeneği</label>
                                                        <select class="select" id="odemeSecenegi" name="odemeSecenegi">
                                                            <option value="">Seçiniz</option>
                                                            <?php foreach ($odemeYontemleri as $y): ?>
                                                                <option value="<?= (int) $y['yontem_id'] ?>">
                                                                    <?= htmlspecialchars($y['yontem_adi']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-6">
                                                    <div class="mb-4">
                                                        <label class="form-label">Kasa</label>
                                                        <select class="select" id="kasaId" name="kasaId">
                                                            <option value="">Seçiniz</option>
                                                            <?php foreach ($kasalar as $k): ?>
                                                                <option value="<?= (int) $k['kasa_id'] ?>">
                                                                    <?= htmlspecialchars($k['kasa_adi']) ?>    <?php
                                                                          if (!empty($k['kasa_tipi']))
                                                                              echo ' - ' . htmlspecialchars($k['kasa_tipi']);
                                                                          ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-2 pb-2 bg-light-transparent">
                                        <div class="text-end" style="padding-right: 18px;">
                                            <button type="button" id="btnOdemeDevam"
                                                class="btn btn-outline-primary">Devam</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Ödeme bilgisi -->

                                <!-- Taksit bilgisi -->
                                <!-- Taksit Bilgisi -->
                                <div class="card" id="taksitCard">
                                    <div class="card-header bg-light">
                                        <div class="d-flex align-items-center">
                                            <span class="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                                                <i class="ti ti-shopping-cart-copy fs-16"></i>
                                            </span>
                                            <h4 class="text-dark">Taksit Bilgisi</h4>
                                        </div>
                                    </div>
                                    <div class="card-body pb-0">
                                        <div class="info-section">
                                            <div class="row align-items-end">
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="mb-4">
                                                        <label class="form-label">Kalan Tutar</label>
                                                        <input type="text" class="form-control text-end text-success"
                                                            name="kalan_tutar" id="kalanTutar">
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Taksit Başlangıcı</label>
                                                        <div class="input-icon position-relative">
                                                            <input name="taksitBaslangic" type="text"
                                                                class="form-control datetimepicker" id="taksitBaslangic"
                                                                required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Taksit Sayısı</label>
                                                        <input type="number" min="1" max="36" class="form-control"
                                                            id="taksitSayisi" name="taksitSayisi"
                                                            placeholder="Max. 36 taksit">
                                                    </div>
                                                </div>

                                                <div class="col-lg-2">
                                                    <div class="mb-3">
                                                        <button type="button" class="btn btn-primary"
                                                            id="btnTaksitOlustur">
                                                            <i class="ti ti-plus me-2"></i>Taksit Oluştur
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="invoice-product-table">
                                                        <div class="table-responsive pb-4 ">
                                                            <table class="table text-nowrap table-striped table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Taksit Tutarı</th>
                                                                        <th scope="col">Ödeme Tarihi</th>
                                                                        <th scope="col" style="width: 50px;"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="taksitTbody">
                                                                    <!-- JS ile doldurulacak -->
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="3">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-secondary"
                                                                                id="btnAddManualRow">
                                                                                <i class="ti ti-plus"></i> Satır Ekle
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Özet / invoice-info -->
                                <div class="invoice-info" id="invoiceInfo" style="display:none;">
                                    <div class="row">
                                        <div class="col-xxl-9 col-lg-8">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Sözleşme Bilgisi</label>
                                                        <textarea rows="5" cols="5" class="form-control"
                                                            placeholder="Enter text here"></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Şartlar Ve Koşullar </label>
                                                        <textarea rows="5" cols="5" class="form-control"
                                                            placeholder="Enter text here"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-3 col-lg-4">
                                            <div class="card invoice-amount-details">
                                                <ul>
                                                    <li>
                                                        <span>Birim Fiyat</span>
                                                        <h6 id="invBirimFiyat">₺0,00</h6>
                                                    </li>
                                                    <li>
                                                        <span>Tutar</span>
                                                        <h6 id="invTutar">₺0,00</h6>
                                                    </li>
                                                    <li>
                                                        <h5>Toplam Tutar</h5>
                                                        <h5 id="invToplam">₺0,00</h5>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="pt-2 pb-2 bg-light-transparent sozlesme-submit-wrapper" style="display:none;">
                                <div class="text-end" style="padding-right: 18px;">
                                    <button type="button" id="btnSozlesmeOlustur" class="btn btn-outline-success">
                                        Sözleşme Oluştur
                                    </button>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Kurs alanı -->
<script>
    async function apiCall(payload) {
        const res = await fetch('sozlesme-ajax/sozlesme-kurs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams(payload)
        });
        return await res.json();
    }
    function setLockedUI(sections = null) {
        // values varsa set et
        if (sections) {
            Object.keys(sections).forEach(key => {
                const valueSections = sections[key].values;
                const sectionLocked = sections[key].locked;
                console.log(valueSections)
                Object.keys(valueSections).forEach(key => {
                    console.log(key)
                    const inp = document.getElementById(key)
                    inp.value = valueSections[key]
                    inp.disabled = !!sectionLocked;
                    // select2 varsa:

                    try { if (window.$) $(inp).trigger('change.select2'); } catch (e) { }
                    try {
                        if (window.$ && $(inp).data('select2')) {
                            $(inp).prop('disabled', !!sectionLocked);
                        }
                    } catch (e) { }
                    console.log(inp)
                });

                /*/if(key === "kurs_section"){
                    if(selDonem && valueSections.donem_id) selDonem.value = String(valueSections.donem_id);
                    if(selSinif && valueSections.sinif_id) selSinif.value = String(valueSections.sinif_id);
                    if(selGrup  && valueSections.grup_id)  selGrup.value  = String(valueSections.grup_id);
                    if(selAlan  && valueSections.alan_id)  selAlan.value  = String(valueSections.alan_id);

                    // select2 kullanıyorsan: $(...).trigger('change') gerekebilir
                    try { if(window.$ && selDonem) $(selDonem).trigger('change.select2'); } catch(e){}

                    // kilitle/aç
                    [selDonem, selSinif, selGrup, selAlan].forEach(function(el){
                        if(!el) return;
                        el.disabled = !!sectionLocked;
                        // select2 varsa:
                        try {
                            if(window.$ && $(el).data('select2')){
                                $(el).prop('disabled', !!sectionLocked);
                            }
                        } catch(e){}
                    });

                    // butonlar
                    if(sectionLocked){
                        btnDevam.style.display = 'none';
                        btnDuzenle.style.display = '';
                    }else{
                        btnDevam.style.display = '';
                        btnDuzenle.style.display = 'none';
                    }
                }
                else if(key=="kurs_sozlesme_section"){
                    Object.keys(valueSections).forEach(key=>{
                        const inp=document.getElementById('#'+key)
                        inp.value =valueSections[key]
                        inp.disabled = !!sectionLocked;
                        // select2 varsa:
                        try {
                            if(window.$ && $(inp).data('select2')){
                                $(inp).prop('disabled', !!sectionLocked);
                            }
                        } catch(e){}
                        console.log(inp)
                    });


                }/*/
                console.log()
            });
        }

    }

    (function () {

        // Bu sayfadaki ogrenci_id'yi bir yerde elinizde tutuyorsunuz:
        // Örn: PHP'den basın:
        const OGR_ID = <?= (int) $ogrenci['ogrenci_id'] ?>;

        const selDonem = document.getElementById('donem_id');
        const selSinif = document.getElementById('sinif_id');
        const selGrup = document.getElementById('grup_id');
        const selAlan = document.getElementById('alan_id');


        const birimId = document.getElementById('birimId');
        const birimFiyat = document.getElementById('birimFiyat');
        const miktar = document.getElementById('miktar');
        const toplamTutar = document.getElementById('toplamTutar');




        const btnDevam = document.getElementById('btnKursDevam');
        const btnDuzenle = document.getElementById('btnKursDuzenle');

        const section = btnDevam.getAttribute('data-section');
        const sectionDuzenle = btnDuzenle.getAttribute('data-section');





        // İlk yüklemede state’i getir
        document.addEventListener('DOMContentLoaded', async function () {
            try {
                const json = await apiCall({ action: 'get', ogrenci_id: OGR_ID });
                if (json.ok) {
                    setLockedUI(json.state);
                } else {
                    setLockedUI(false, null);
                }
            } catch (e) {
                setLockedUI(null);
            }
        });

        // Devam Et → Lock
        btnDevam?.addEventListener('click', async function () {
            const donem_id = selDonem.value || 0;
            const sinif_id = selSinif.value || 0;
            const grup_id = selGrup.value || 0;
            const alan_id = selAlan.value || 0;
            const section_id = "kurs_section"

            console.log(donem_id)
            console.log(sinif_id)
            console.log(grup_id)
            console.log(alan_id)


            // basit doğrulama
            if (!Number(donem_id) || !Number(alan_id)) {
                alert('Lütfen en az Dönem ve Alan seçiniz.');
                return;
            }

            const json = await apiCall({
                action: 'lock',
                ogrenci_id: OGR_ID,
                donem_id, sinif_id, grup_id, alan_id, section_id
            });

            if (json.ok) {
                setLockedUI(json.state);
                // burada aşağıdaki “Ücret / Peşinat / Taksit” kartını aktif edebilirsin
                // document.getElementById('odemeKart').classList.remove('disabled')
            } else {
                alert(json.msg || 'Kilitlenemedi.');
            }
        });

        // Düzenle → Unlock
        btnDuzenle?.addEventListener('click', async function () {
            const json = await apiCall({ action: 'unlock', ogrenci_id: OGR_ID, section_id: sectionDuzenle });
            if (json.ok) {
                setLockedUI(json.state);
            } else {
                alert(json.msg || 'Kilidi kaldıramadım.');
            }
        });

    })();


    // ₺ formatlayıcı
    function tl(n) {
        const num = Number(n || 0);
        return num.toLocaleString('tr-TR', { style: 'currency', currency: 'TRY' });
    }
    // Türkçe girdiyi sayıya çevir (5.000,25 -> 5000.25)
    function parseTl(s) {
        if (typeof s === 'number') return s;
        if (!s) return 0;
        s = ('' + s).trim()
            .replace(/\s/g, '')
            .replace(/\./g, '')
            .replace(/,/g, '.')
            .replace(/[^\d.-]/g, '');
        const v = parseFloat(s);
        return isNaN(v) ? 0 : v;
    }

    // DOM elemanları
    const elBirim = document.getElementById('birimId');
    const elFiyat = document.getElementById('birimFiyat');
    const elMiktar = document.getElementById('miktar');
    const elToplam = document.getElementById('toplamTutar');
    const btnHesap = document.getElementById('btnHesapla');

    const elOdmBirim = document.getElementById('odmBirimTutar');
    const elOdmTutar = document.getElementById('odmTutar');
    const elOdmTop = document.getElementById('odmToplam');

    // Anlık hesap
    function recalc() {
        const f = parseTl(elFiyat.value);
        const m = parseInt(elMiktar.value || '0', 10);
        const tt = (f * (isNaN(m) ? 0 : m));
        elToplam.value = tt ? tl(tt) : '';
    }
    elFiyat.addEventListener('input', recalc);
    elMiktar.addEventListener('input', recalc);



    // Hesapla (kilitle + ödeme kartını doldur)
    btnHesap.addEventListener('click', async function () {
        const OGR_ID = <?= (int) $ogrenci['ogrenci_id'] ?>;
        const birimId = elBirim.value;

        const f = parseTl(elFiyat.value);
        const m = parseInt(elMiktar.value || '0', 10);
        const tt = f * (isNaN(m) ? 0 : m);

        // basit doğrulama
        if (!birimId) { alert('Lütfen birim seçiniz.'); return; }
        if (f <= 0) { alert('Lütfen geçerli bir birim fiyatı giriniz.'); return; }
        if (!m || m <= 0) { alert('Lütfen miktar giriniz.'); return; }

        // Üst alanları kilitle
        elBirim.setAttribute('disabled', 'disabled');
        elFiyat.setAttribute('readonly', 'readonly');
        elMiktar.setAttribute('readonly', 'readonly');

        // Ödeme kartını doldur
        elOdmBirim.value = tl(f);
        elOdmTutar.value = tl(tt);
        elOdmTop.value = tl(tt);



        const json = await apiCall({
            action: 'lock',
            ogrenci_id: OGR_ID,
            birimId, birimFiyat: f, miktar: m, toplamTutar: tt, section_id: "kurs_sozlesme_section"
        });

        if (json.ok) {

            const json2 = await apiCall({
                action: 'get',
                ogrenci_id: OGR_ID,
            });
            setLockedUI(json2.state);
        }
        // İstersen ödeme alanına kaydır
        document.getElementById('odemeCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>

<script>
    function parseMoney(str) {
        if (!str) return 0;
        return Number(
            str.toString()
                .replace(/[^\d,.-]/g, '')   // sadece rakam , . - kalsın
                .replace(/\./g, '')         // binlik ayırıcı . kaldır
                .replace(',', '.')          // , -> .
        ) || 0;
    }



    // Sayıyı 1.000,00 formatına çevir (görüntü için)
    function formatMoney(num) {
        return num.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.getElementById('btnOdemeDevam').addEventListener('click', async function () {
        const toplam = parseMoney(document.getElementById('odmToplam').value);
        const pesinat = parseMoney(document.getElementById('pesinat').value);

        if (isNaN(pesinat) || pesinat < 0) {
            alert('Lütfen geçerli bir peşinat tutarı girin.');
            return;
        }

        const kalan = toplam - pesinat;

        if (kalan < 0) {
            alert('Peşinat toplam tutardan fazla olamaz!');
            return;
        }

        // Taksit alanındaki inputu bul ve doldur
        const kalanInput = document.querySelector('#taksitCard input[name="kalan_tutar"]');
        kalanInput.value = formatMoney(kalan);
        kalanInput.setAttribute('readonly', true);

        const odemeSecenegi = document.getElementById('odemeSecenegi')?.value
        const kasaId = document.getElementById('kasaId')?.value

        const OGR_ID = <?= (int) $ogrenci['ogrenci_id'] ?>;
        const json = await apiCall({
            action: 'lock',
            ogrenci_id: OGR_ID,
            pesinat, odemeSecenegi, kasaId, section_id: "odeme_section"
        });
        if (json.ok) {
            const json2 = await apiCall({ action: 'get', ogrenci_id: OGR_ID });
            setLockedUI(json2.state);
        } else {
            setLockedUI(false, null);
        }


        // Ödeme alanını kilitle (isteğe bağlı)
        document.querySelectorAll('#odemeCard input, #odemeCard select, #odemeCard button').forEach(el => {
            el.setAttribute('disabled', true);
        });
    });
</script>


<script>
    // TL string -> number
    function parseMoney(str) {
        if (!str) return 0;
        return Number(
            str.toString()
                .replace(/[^\d,.-]/g, '') // TL sembolü vs temizle
                .replace(/\./g, '')       // binlik . sil
                .replace(',', '.')        // , -> .
        ) || 0;
    }

    // number -> "1.234,56"
    function fmtTRY(n) {
        return n.toLocaleString('tr-TR', { style: 'currency', currency: 'TRY' });
    }

    // "dd.mm.yyyy" (veya datepicker’in döndürdüğü formatı da destekle)
    function parseDateFlexible(str) {
        if (!str) return null;

        // dd.mm.yyyy
        const m = str.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
        if (m) {
            const d = Number(m[1]), M = Number(m[2]) - 1, y = Number(m[3]);
            return new Date(y, M, d);
        }

        // yyyy-mm-dd
        const m2 = str.match(/^(\d{4})\-(\d{1,2})\-(\d{1,2})$/);
        if (m2) {
            const y = Number(m2[1]), M = Number(m2[2]) - 1, d = Number(m2[3]);
            return new Date(y, M, d);
        }

        // dd-mm-yyyy → YENİ EKLEDİK 🚀
        const m3 = str.match(/^(\d{1,2})\-(\d{1,2})\-(\d{4})$/);
        if (m3) {
            const d = Number(m3[1]), M = Number(m3[2]) - 1, y = Number(m3[3]);
            return new Date(y, M, d);
        }

        // Tarayıcı fallback
        const t = new Date(str);
        return isNaN(t.getTime()) ? null : t;
    }

    // Aya ekle (ayın sonu taşmaları için güvenli)
    function addMonthsSafe(date, months) {
        const d = new Date(date.getTime());
        const day = d.getDate();
        d.setMonth(d.getMonth() + months);
        // Örn 31 + 1 ay -> sonraki ayda 31 yoksa son güne yasla
        while (d.getMonth() % 12 === (date.getMonth() + months) % 12 && d.getDate() < day) {
            d.setDate(d.getDate() - 1);
        }
        return d;
    }

    // Date -> "dd.mm.yyyy"
    function toTRDate(d) {
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yy = d.getFullYear();
        return `${dd}.${mm}.${yy}`;
    }

    // Ödeme kartındaki özet değerlerini invoice-info alanına geçir
    function fillInvoiceInfo() {
        const birim = document.getElementById('odmBirimTutar')?.value || '';
        const tutar = document.getElementById('odmTutar')?.value || '';
        const toplam = document.getElementById('odmToplam')?.value || '';

        if (document.getElementById('invBirimFiyat')) document.getElementById('invBirimFiyat').textContent = birim || '₺0,00';
        if (document.getElementById('invTutar')) document.getElementById('invTutar').textContent = tutar || '₺0,00';
        if (document.getElementById('invToplam')) document.getElementById('invToplam').textContent = toplam || '₺0,00';
    }

    // Taksit oluştur
    document.getElementById('btnTaksitOlustur').addEventListener('click', function () {
        const kalanStr = document.getElementById('kalanTutar').value;     // "₺30.000,00"
        const basStr = document.getElementById('taksitBaslangic').value;// "01.11.2025" gibi
        const sayiStr = document.getElementById('taksitSayisi').value;

        const kalan = parseMoney(kalanStr);
        const sayi = parseInt(sayiStr, 10);
        const bas = parseDateFlexible(basStr);

        if (!kalan || kalan <= 0) {
            alert('Kalan tutar geçerli değil.');
            return;
        }
        if (!sayi || sayi <= 0) {
            alert('Taksit sayısı geçerli değil.');
            return;
        }
        if (!bas) {
            alert('Taksit başlangıç tarihi geçerli değil.');
            return;
        }

        // Taksit tutarını eşit böl — kuruş sapmalarını ilk taksitlere dağıt
        const ham = kalan / sayi;             // örn 30000 / 3 => 10000
        // Kuruşu 2 haneli yap
        const taksitYuvar = Math.floor(ham * 100) / 100;
        let kalanKurus = Math.round(kalan * 100) - (taksitYuvar * 100) * sayi; // kuruş farkı

        const rows = [];
        for (let i = 1; i <= sayi; i++) {
            let tutar = taksitYuvar;
            if (kalanKurus > 0) {
                tutar = Math.round((tutar * 100 + 1)) / 100; // 0.01 ekle
                kalanKurus -= 1;
            }

            // İlk vade: başlangıç + 1 ay, sonra +2, +3...
            const vade = addMonthsSafe(bas, i); // i=1 => +1 ay

            rows.push({
                tutar,
                tarih: vade
            });
        }

        // Tabloyu doldur
        const tbody = document.getElementById('taksitTbody');
        tbody.innerHTML = '';
        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td >${fmtTRY(r.tutar)}</td>
        <td>${toTRDate(r.tarih)}</td>
      `;
            tbody.appendChild(tr);
        });

        // Özet blok değerlerini doldur ve göster
        fillInvoiceInfo();
        document.getElementById('invoiceInfo').style.display = 'block';

        document.querySelector('.sozlesme-submit-wrapper').style.display = 'block';
    });
</script>

<script>
    // Yardımcılar
    function parseMoney(str) {
        if (!str) return 0;
        return Number(
            String(str).replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.')
        ) || 0;
    }
    function parseDateFlexibleToYMD(str) {
        if (!str) return '';
        // dd.mm.yyyy
        let m = str.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
        if (m) { return `${m[3]}-${m[2].padStart(2, '0')}-${m[1].padStart(2, '0')}`; }
        // dd-mm-yyyy
        m = str.match(/^(\d{1,2})\-(\d{1,2})\-(\d{4})$/);
        if (m) { return `${m[3]}-${m[2].padStart(2, '0')}-${m[1].padStart(2, '0')}`; }
        // yyyy-mm-dd zaten
        m = str.match(/^(\d{4})\-(\d{1,2})\-(\d{1,2})$/);
        if (m) { return `${m[1]}-${m[2].padStart(2, '0')}-${m[3].padStart(2, '0')}`; }
        // Fallback
        const d = new Date(str); if (!isNaN(d)) {
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${d.getFullYear()}-${mm}-${dd}`;
        }
        return '';
    }

    document.getElementById('btnSozlesmeOlustur')?.addEventListener('click', async function () {
        // Zorunlu referanslar
        const OGR_ID = <?= (int) $ogrenci['ogrenci_id'] ?>;

        // Kurs alanları
        const donem_id = document.getElementById('donem_id')?.value || '';
        const sinif_id = document.getElementById('sinif_id')?.value || '';
        const grup_id = document.getElementById('grup_id')?.value || '';
        const alan_id = document.getElementById('alan_id')?.value || '';

        // Sözleşme hesap alanları
        const birim_id = document.getElementById('birimId')?.value || '';
        const birim_fiyat = parseMoney(document.getElementById('birimFiyat')?.value || 0);
        const miktar = parseInt(document.getElementById('miktar')?.value || '0', 10);
        const toplam_ucret = parseMoney(document.getElementById('odmToplam')?.value || 0);

        // Ödeme / peşinat
        const pesinat = parseMoney(document.getElementById('pesinat')?.value || 0);
        const yontem_id = document.getElementById('odemeSecenegi')?.value || '';
        const kasa_id = document.getElementById('kasaId')?.value || '';

        // Taksit listesi tablodan toplanır
        const rows = Array.from(document.querySelectorAll('#taksitTbody tr'));
        const taksitler = rows.map(tr => {
            const td = tr.querySelectorAll('td');
            const tutarTxt = td[0]?.textContent?.trim() || '';
            const tarihTxt = td[1]?.textContent?.trim() || '';
            return {
                tutar: parseMoney(tutarTxt),
                tarih: parseDateFlexibleToYMD(tarihTxt)
            };
        }).filter(x => x.tutar > 0 && x.tarih);

        // Basit kontroller
        if (!donem_id || !alan_id) { alert('Dönem ve Alan seçiniz.'); return; }
        if (!birim_id || birim_fiyat <= 0 || !miktar || toplam_ucret <= 0) { alert('Sözleşme tutar bilgileri eksik.'); return; }

        // Peşinat varsa yöntem+kasa zorunlu
        if (pesinat > 0 && (!yontem_id || !kasa_id)) {
            alert('Peşinat için ödeme yöntemi ve kasa seçiniz.');
            return;
        }

        // Peşinat + taksit toplamı = toplam kontrolü (küçük tolerans)
        const taksitSum = taksitler.reduce((a, b) => a + (Number(b.tutar) || 0), 0);
        const diff = Math.abs((pesinat + taksitSum) - toplam_ucret);
        if (diff > 0.01) {
            alert('Peşinat + taksit toplamı, Toplam Tutar ile eşit değil.');
            return;
        }

        // Payload hazırla
        const payload = new URLSearchParams({
            ogrenci_id: String(OGR_ID),
            donem_id, sinif_id, grup_id, alan_id,
            birim_id: String(birim_id),
            birim_fiyat: String(birim_fiyat.toFixed(2)),
            miktar: String(miktar),
            toplam_ucret: String(toplam_ucret.toFixed(2)),
            pesinat: String(pesinat.toFixed(2)),
            yontem_id: String(yontem_id || ''),
            kasa_id: String(kasa_id || ''),
            taksitler: JSON.stringify(taksitler)
        });

        // Butonu kilitle
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = 'Kaydediliyor...';

        try {
            const res = await fetch('sozlesme-ajax/sozlesme-kaydet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: payload
            });
            const json = await res.json();
            if (json.ok) {
                // Başarılı → belge sayfasına
                window.open(json.redirect || ('sozlesme-belge.php?id=' + json.sozlesme_id), "_blank")
                window.location.href = "ogrenci-listesi.php";
            } else {
                alert(json.msg || 'Kayıt sırasında hata oluştu.');
                btn.disabled = false;
                btn.innerHTML = 'Sözleşme Oluştur';
            }
        } catch (e) {
            alert('İstek hatası: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = 'Sözleşme Oluştur';
        }
    });
</script>

</div>
<!-- /Main Wrapper -->

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

</body>

</html>