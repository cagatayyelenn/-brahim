<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

$sozlesme_id = (int) ($_GET['id'] ?? 0);
if ($sozlesme_id <= 0)
    die("Hatalı sözleşme ID");

$sozlesme = $db->find('sozlesme1', 'sozlesme_id', $sozlesme_id);
if (!$sozlesme)
    die("Sözleşme bulunamadı");

// Bağlı veriler
$ogrenci = $db->find('ogrenci1', 'ogrenci_id', $sozlesme['ogrenci_id']);
$odemeyontem = $db->find('odeme1', 'sozlesme_id', $sozlesme_id);
$taksitler = $db->get("SELECT * FROM taksit1 WHERE sozlesme_id = :id ORDER BY sira_no ASC", [':id' => $sozlesme_id]);

// Tahsilat Kontrolü
$toplamOdenen = 0;
// Peşinatı ekle
if ($odemeyontem && isset($odemeyontem['tutar'])) {
    $toplamOdenen += (float)$odemeyontem['tutar'];
}
foreach ($taksitler as $t) {
    $toplamOdenen += $t['odendi_tutar'];
}
$odemeVar = ($toplamOdenen > 0);

// Dropdown Verileri
$donemler = $db->finds('donem', null, null, ['donem_id', 'donem_adi']);
$siniflar = $db->finds('sinif', null, null, ['sinif_id', 'sinif_adi']);
$gruplar = $db->finds('grup', null, null, ['grup_id', 'grup_adi']);
$alanlar = $db->finds('alan', null, null, ['alan_id', 'alan_adi']);

$pageTitle = 'Sözleşme Yönetimi';
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>

<div class="page-wrapper">
    <div class="content">

        <!-- Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Sözleşme Yönetimi</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                        <li class="breadcrumb-item"><a href="ogrenci-listesi.php">Öğrenci Listesi</a></li>
                        <li class="breadcrumb-item active">Sözleşme Yönetimi</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <a href="sozlesme-belge.php?id=<?= $sozlesme_id ?>" target="_blank" class="btn btn-warning me-2">
                        <i class="ti ti-printer me-1"></i> Yazdır
                    </a>
                </div>
            </div>
        </div>

        <!-- Öğrenci Kartı -->
        <div class="card bg-light-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg rounded-circle bg-primary text-white me-3">
                        <i class="ti ti-user fs-2"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 text-primary">
                            <?= htmlspecialchars($ogrenci['ogrenci_adi'] . ' ' . $ogrenci['ogrenci_soyadi']) ?>
                        </h4>
                        <p class="mb-0 text-muted">
                            <span class="me-3"><i class="ti ti-id"></i> No:
                                <?= htmlspecialchars($ogrenci['ogrenci_numara']) ?></span>
                            <span><i class="ti ti-file-description"></i> Toplam Sözleşme:
                                <strong><?= number_format($sozlesme['toplam_ucret'], 2, ',', '.') ?> TL</strong></span>
                        </p>
                    </div>
                    <div class="ms-auto text-end">
                        <div class="badge bg-success fs-14 p-2 mb-1">Tahsil Edilen:
                            <?= number_format($toplamOdenen, 2, ',', '.') ?> TL</div>
                        <div class="d-block text-danger fs-14">Kalan Borç:
                            <?= number_format($sozlesme['toplam_ucret'] - $toplamOdenen, 2, ',', '.') ?> TL</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs nav-tabs-bottom card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#tab-bilgiler" data-bs-toggle="tab">
                            <i class="ti ti-edit me-1"></i> Sözleşme Bilgileri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-yapilandir" data-bs-toggle="tab">
                            <i class="ti ti-calculator me-1"></i> Yapılandırma
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="#tab-fesih" data-bs-toggle="tab">
                            <i class="ti ti-circle-x me-1"></i> Fesih İşlemleri
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body tab-content">

                <!-- TAB 1: Temel Bilgiler -->
                <div class="tab-pane fade show active" id="tab-bilgiler">
                    <form id="formBasicUpdate">
                        <input type="hidden" name="action" value="update_basic">
                        <input type="hidden" name="sozlesme_id" value="<?= $sozlesme_id ?>">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Dönem</label>
                                <select class="form-select select" name="donem_id">
                                    <?php foreach ($donemler as $d): ?>
                                        <option value="<?= $d['donem_id'] ?>"
                                            <?= $sozlesme['donem_id'] == $d['donem_id'] ? 'selected' : '' ?>>
                                            <?= $d['donem_adi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Sınıf</label>
                                <select class="form-select select" name="sinif_id">
                                    <?php foreach ($siniflar as $s): ?>
                                        <option value="<?= $s['sinif_id'] ?>"
                                            <?= $sozlesme['sinif_id'] == $s['sinif_id'] ? 'selected' : '' ?>>
                                            <?= $s['sinif_adi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Grup</label>
                                <select class="form-select select" name="grup_id">
                                    <?php foreach ($gruplar as $g): ?>
                                        <option value="<?= $g['grup_id'] ?>"
                                            <?= $sozlesme['grup_id'] == $g['grup_id'] ? 'selected' : '' ?>>
                                            <?= $g['grup_adi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Alan</label>
                                <select class="form-select select" name="alan_id">
                                    <?php foreach ($alanlar as $a): ?>
                                        <option value="<?= $a['alan_id'] ?>"
                                            <?= $sozlesme['alan_id'] == $a['alan_id'] ? 'selected' : '' ?>>
                                            <?= $a['alan_adi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="ti ti-info-circle"></i> Not: Finansal bilgiler (Fiyat, Taksitler) "Yapılandırma"
                            sekmesinden değiştirilebilir. Burada sadece sınıf/grup gibi eğitim bilgileri güncellenir.
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Değişiklikleri Kaydet
                            </button>
                        </div>
                    </form>

                    <hr>
                    <h5 class="mb-3">Mevcut Ödeme Planı (Salt Okunur)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Vade</th>
                                    <th>Tutar</th>
                                    <th>Ödenen</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($taksitler as $idx => $t): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><?= $t['vade_tarihi'] ?></td>
                                        <td><?= number_format($t['tutar'], 2, ',', '.') ?></td>
                                        <td><?= number_format($t['odendi_tutar'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php if ($t['odendi_tutar'] >= $t['tutar']): ?>
                                                <span class="badge bg-success">Ödendi</span>
                                            <?php elseif ($t['odendi_tutar'] > 0): ?>
                                                <span class="badge bg-warning">Kısmi</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Ödenmedi</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Yapılandırma -->
                <div class="tab-pane fade" id="tab-yapilandir">
                    <div class="row">
                        <div class="col-md-4 border-end">
                            <h5 class="header-title mb-3">Hesaplama Araçları</h5>
                            <form id="formRestructureCalc">
                                <div class="mb-3">
                                    <label class="form-label text-success">Bugüne Kadar Ödenen (Sabit)</label>
                                    <input type="text" class="form-control" readonly value="<?= number_format($toplamOdenen, 2, ',', '.') ?> TL">
                                </div>
                                
                                <?php $mevcutKalan = max(0, $sozlesme['toplam_ucret'] - $toplamOdenen); ?>
                                <div class="mb-3">
                                    <label class="form-label text-danger">Şu Anki Kalan Borç</label>
                                    <input type="text" class="form-control" readonly value="<?= number_format($mevcutKalan, 2, ',', '.') ?> TL">
                                </div>

                                <div class="mb-3 p-2 bg-light border rounded">
                                    <label class="form-label text-primary fw-bold">1. Yöntem: Yeni Kalan Borcu Giriniz</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control money-input" id="inpYeniKalan" 
                                               value="<?= number_format($mevcutKalan, 2, ',', '.') ?>" placeholder="Örn: 5.000,00">
                                        <button class="btn btn-outline-primary" type="button" onclick="toplamHesapla()">Hesapla</button>
                                    </div>
                                    <small class="text-muted d-block mt-1">Sistemin "Toplam Tutar"ı bulması için buraya kalan borcu yazıp hesaplaya basınız.</small>
                                </div>

                                <div class="mb-3" style="display:none;">
                                    <label class="form-label">Hesaplanan Yeni Toplam</label>
                                    <input type="text" class="form-control money-input fw-bold" id="resYeniToplam"
                                        value="<?= number_format($sozlesme['toplam_ucret'], 2, ',', '.') ?>" readonly>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Taksit Başlangıcı</label>
                                    <input type="date" class="form-control" id="resBaslangic"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kalan Bakiye İçin Taksit Sayısı</label>
                                    <input type="number" class="form-control" id="resTaksitSayisi" value="3" min="1"
                                        max="24">
                                </div>
                                <button type="button" class="btn btn-info w-100" onclick="hesaplaYapilandirma()">
                                    <i class="ti ti-calculator"></i> Planı Hesapla
                                </button>
                            </form>
                        </div>

                        <div class="col-md-8">
                            <h5 class="header-title mb-3">Yeni Ödeme Planı Önizleme</h5>
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle"></i>
                                Dikkat: Yapılandırma işlemi onaylandığında, <strong>ödenmemiş</strong> tüm eski
                                taksitler silinecek ve aşağıdaki plan devreye girecektir.
                                Ödenmiş taksitler korunacaktır.
                            </div>

                            <table class="table table-bordered table-sm" id="previewTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Durum</th>
                                        <th>Vade</th>
                                        <th>Tutar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Hesaplama bekleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-success" id="btnSaveRestructure" disabled
                                    onclick="kaydetYapilandirma()">
                                    <i class="ti ti-check"></i> Sözleşmeyi Yapılandır (Onayla)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Fesih -->
                <div class="tab-pane fade" id="tab-fesih">
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <div class="mb-4">
                                <span class="avatar avatar-xl rounded-circle bg-danger text-white">
                                    <i class="ti ti-alert-octagon fs-1"></i>
                                </span>
                            </div>
                            <h3>Sözleşme Fesih İşlemi</h3>
                            <p class="text-muted">
                                Bu işlem geri alınamaz. Sözleşme "Feshedildi" durumuna getirilecek ve kalan tüm
                                ödenmemiş taksitler silinecektir.
                            </p>

                            <div class="card bg-light border-danger text-start mx-auto" style="max-width: 500px">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Toplam Sözleşme:</span>
                                        <strong><?= number_format($sozlesme['toplam_ucret'], 2, ',', '.') ?> TL</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tahsil Edilen (Kasa'da):</span>
                                        <strong class="text-success"><?= number_format($toplamOdenen, 2, ',', '.') ?>
                                            TL</strong>
                                    </div>
                                    <hr>
                                    <div class="mb-3">
                                        <label class="form-label">Fesih Nedeni / Açıklama</label>
                                        <textarea class="form-control" rows="3" id="fesihNedeni"
                                            placeholder="Örn: Şehir değişikliği nedeniyle ayrıldı..."></textarea>
                                    </div>

                                    <button type="button" class="btn btn-danger w-100" onclick="fesihOnayla()">
                                        <i class="ti ti-trash"></i> Sözleşmeyi Feshet
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

<!-- Scripts -->
<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/select2/js/select2.min.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Global Değişkenler
    const globalSozlesmeId = <?= $sozlesme_id ?>;
    const globalOdenen = <?= $toplamOdenen ?>;
    let yeniPlanData = []; // Hesaplanan taksitleri tutar

    // Select2 Init
    $(document).ready(function () {
        if ($('.select').length > 0) {
            $('.select').select2({ width: '100%' });
        }
    });

    // Para Formatlama
    function formatMoney(n) {
        return parseFloat(n).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function parseMoney(s) {
        if (!s) return 0;
        return parseFloat(s.toString().replace(/\./g, '').replace(',', '.')) || 0;
    }
    $('.money-input').on('blur', function () { $(this).val(formatMoney(parseMoney($(this).val()))); });

    // ----------------------------------------------------
    // TAB 1: Temel Bilgi Güncelleme
    // ----------------------------------------------------
    $('#formBasicUpdate').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize(); // action=update_basic zaten hidden içinde

        $.post('sozlesme-ajax/sozlesme-yonetimi-ajax.php', formData, function (res) {
            if (res.ok) {
                Swal.fire('Başarılı', res.msg, 'success').then(() => location.reload());
            } else {
                Swal.fire('Hata', res.msg, 'error');
            }
        }, 'json');
    });

    function toplamHesapla() {
        const kalan = parseMoney($('#inpYeniKalan').val());
        const yeniToplam = globalOdenen + kalan;
        $('#resYeniToplam').val(formatMoney(yeniToplam));
        hesaplaYapilandirma(); // Otomatik planı da dök
    }

    // ----------------------------------------------------
    // TAB 2: Yapılandırma Hesaplama
    // ----------------------------------------------------
    function hesaplaYapilandirma() {
        const yeniTutar = parseMoney($('#resYeniToplam').val());
        const taksitSayisi = parseInt($('#resTaksitSayisi').val()) || 1;
        const baslangic = new Date($('#resBaslangic').val());

        if (yeniTutar < globalOdenen) {
            Swal.fire('Uyarı', 'Yeni toplam tutar, ödenen tutardan (' + formatMoney(globalOdenen) + ' TL) düşük olamaz.', 'warning');
            return;
        }

        const kalanBorc = yeniTutar - globalOdenen;
        if (kalanBorc < 0) return; // Should not happen due to check above

        // Tabloyu temizle
        const tbody = $('#previewTable tbody');
        tbody.html('');
        yeniPlanData = [];

        // 1. Ödenmişleri Listele (Sabit)
        // PHP'den gelen veriyi JS dizisi olarak alabilirdik ama basitlik için loop yok.
        // Ancak görsel bütünlük için "Ödenmiş (Eski)" diye tek satır özet geçebiliriz veya detaylı gösterebiliriz.
        // Burada sadece YENİ planı ve ödenmiş toplamı gösterelim.

        tbody.append(`<tr class="table-success">
            <td><strong>Tahsil Edilen (Mevcut)</strong></td>
            <td>-</td>
            <td><strong>${formatMoney(globalOdenen)} TL</strong></td>
        </tr>`);

        // 2. Kalanı Taksitlendir
        if (kalanBorc > 0) {
            const taksitTutar = kalanBorc / taksitSayisi;
            let toplamHesap = 0;

            for (let i = 0; i < taksitSayisi; i++) {
                let vade = new Date(baslangic);
                vade.setMonth(vade.getMonth() + i);
                let vadeStr = vade.toISOString().split('T')[0];

                let buTaksit = taksitTutar;
                // Kuruş düzeltmesi (son taksit)
                if (i === taksitSayisi - 1) {
                    buTaksit = kalanBorc - toplamHesap;
                }
                buTaksit = parseFloat(buTaksit.toFixed(2));
                toplamHesap += buTaksit;

                yeniPlanData.push({ tarih: vadeStr, tutar: buTaksit });

                tbody.append(`<tr>
                    <td>Yeni Taksit ${i + 1}</td>
                    <td>${vadeStr}</td>
                    <td>${formatMoney(buTaksit)} TL</td>
                </tr>`);
            }
        } else {
            tbody.append(`<tr><td colspan="3" class="text-center">Borç kalmıyor.</td></tr>`);
        }

        $('#btnSaveRestructure').prop('disabled', false);
    }

    // Yapılandırma Kaydet
    function kaydetYapilandirma() {
        const kalanBakiye = parseMoney($('#resYeniToplam').val()) - globalOdenen;
        
        // Eğer kalan bakiye varsa ama taksit planı yoksa hata ver
        if (kalanBakiye > 1 && yeniPlanData.length === 0) {
            Swal.fire('Hata', 'Lütfen önce hesaplama yapınız.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Onaylıyor musunuz?',
            text: "Eski ödenmemiş taksitler silinecek ve bu yeni plan kaydedilecek.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Yapılandır'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('sozlesme-ajax/sozlesme-yonetimi-ajax.php', {
                    action: 'restructure_financials',
                    sozlesme_id: globalSozlesmeId,
                    yeni_toplam_tutar: $('#resYeniToplam').val(), // string formatlı gidebilir, backend parseFunction var
                    taksitler: JSON.stringify(yeniPlanData)
                }, function (res) {
                    if (res.ok) {
                        Swal.fire('Başarılı', res.msg, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Hata', res.msg, 'error');
                    }
                }, 'json');
            }
        });
    }

    // ----------------------------------------------------
    // TAB 3: Fesih
    // ----------------------------------------------------
    function fesihOnayla() {
        const neden = $('#fesihNedeni').val();
        if (neden.trim().length < 5) {
            Swal.fire('Uyarı', 'Lütfen geçerli bir fesih nedeni giriniz.', 'warning');
            return;
        }

        Swal.fire({
            title: 'DİKKAT! Sözleşme Feshedilecek',
            text: "Bu işlem geri alınamaz. Devam etmek istiyor musunuz?",
            icon: 'error', // Red alert
            showCancelButton: true,
            confirmButtonText: 'Evet, FESHET',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('sozlesme-ajax/sozlesme-yonetimi-ajax.php', {
                    action: 'terminate_contract',
                    sozlesme_id: globalSozlesmeId,
                    fesih_nedeni: neden
                }, function (res) {
                    if (res.ok) {
                        Swal.fire('Feshedildi', res.msg, 'success').then(() => window.location.href = 'ogrenci-listesi.php');
                    } else {
                        Swal.fire('Hata', res.msg, 'error');
                    }
                }, 'json');
            }
        });
    }

</script>
</body>

</html>