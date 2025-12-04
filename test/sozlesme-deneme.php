<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "Sözleşme Oluştur";

// Dropdown verilerini çekelim
$donemler = $db->finds('donem', 'donem_durum', 1);
$siniflar = $db->finds('sinif', 'sinif_durum', 1);
$gruplar = $db->finds('grup', 'grup_durum', 1);
$alanlar = $db->finds('alan', 'alan_durum', 1);
$birimler = $db->finds('birim', 'birim_durum', 1); // Birim tablosu varsayımı
$kasalar = $db->finds('kasa', 'kasa_durum', 1);  // Kasa tablosu varsayımı

// Öğrenci ID varsa bilgisini çekebiliriz (Opsiyonel)
$ogrenci_id = isset($_GET['id']) ? $_GET['id'] : 0;
// $ogrenci = ... (Gerekirse öğrenci adı vs. gösterilebilir)

?>

<?php
$page_styles[] = ['href' => 'assets/css/animate.css'];
$page_styles[] = ['href' => 'assets/plugins/daterangepicker/daterangepicker.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>

<div class="page-wrapper">
    <div class="content content-two">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="mb-1">Sözleşme Oluştur</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sözleşme</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h4 class="text-dark">Sözleşme Sihirbazı</h4>
                    </div>
                    <div class="card-body">

                        <!-- Adım Göstergeleri (Opsiyonel) -->
                        <div class="progress mb-4" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: 25%;" id="progressBar">Adım 1/4
                            </div>
                        </div>

                        <form id="contractForm" method="POST" action="">

                            <!-- ADIM 1: Öğrenci Kurs Bilgileri -->
                            <div id="step1" class="step-section">
                                <h5 class="mb-3 text-primary">1. Öğrenci Kurs Bilgileri</h5>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Dönem</label>
                                        <select class="form-select select" name="donem_id" required>
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($donemler as $d): ?>
                                                <option value="<?= $d['donem_id'] ?>"><?= $d['donem_adi'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Sınıf</label>
                                        <select class="form-select select" name="sinif_id" required>
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($siniflar as $s): ?>
                                                <option value="<?= $s['sinif_id'] ?>"><?= $s['sinif_adi'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Grup</label>
                                        <select class="form-select select" name="grup_id" required>
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($gruplar as $g): ?>
                                                <option value="<?= $g['grup_id'] ?>"><?= $g['grup_adi'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Alan</label>
                                        <select class="form-select select" name="alan_id" required>
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($alanlar as $a): ?>
                                                <option value="<?= $a['alan_id'] ?>"><?= $a['alan_adi'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-primary btn-next" onclick="nextStep(2)">İlerle
                                        <i class="ti ti-arrow-right"></i></button>
                                </div>
                            </div>

                            <!-- ADIM 2: Kurs Sözleşme Bilgisi -->
                            <div id="step2" class="step-section" style="display:none;">
                                <h5 class="mb-3 text-primary">2. Kurs Sözleşme Bilgisi</h5>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Birim Seçiniz</label>
                                        <select class="form-select select" name="birim_id" id="birimSelect" required>
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($birimler as $b): ?>
                                                <option value="<?= $b['birim_id'] ?>"><?= $b['birim_adi'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Birim Fiyatı</label>
                                        <input type="number" class="form-control" name="birim_fiyat" id="birimFiyat"
                                            step="0.01" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Miktar</label>
                                        <input type="number" class="form-control" name="miktar" id="miktar" value="1"
                                            min="1" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Toplam Tutar</label>
                                        <input type="text" class="form-control bg-light" id="hesaplananTutar" readonly>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i
                                            class="ti ti-arrow-left"></i> Geri</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">İleri <i
                                            class="ti ti-arrow-right"></i></button>
                                </div>
                            </div>

                            <!-- ADIM 3: Ödeme Bilgisi -->
                            <div id="step3" class="step-section" style="display:none;">
                                <h5 class="mb-3 text-primary">3. Ödeme Bilgisi</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Birim Tutar</label>
                                        <input type="text" class="form-control bg-light" id="odemeBirimTutar" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tutar</label>
                                        <input type="text" class="form-control bg-light" id="odemeTutar" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Toplam Tutar</label>
                                        <input type="text" class="form-control bg-light fw-bold" id="odemeToplamTutar"
                                            readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Peşinat Tutarı</label>
                                        <input type="number" class="form-control" name="pesinat" id="pesinat"
                                            step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Ödeme Seçeneği</label>
                                        <select class="form-select select" name="odeme_yontemi">
                                            <option value="Nakit">Nakit</option>
                                            <option value="Kredi Kartı">Kredi Kartı</option>
                                            <option value="Havale/EFT">Havale/EFT</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Kasa</label>
                                        <select class="form-select select" name="kasa_id">
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($kasalar as $k): ?>
                                                <option value="<?= $k['kasa_id'] ?>"><?= $k['kasa_adi'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)"><i
                                            class="ti ti-arrow-left"></i> Geri</button>
                                    <button type="button" class="btn btn-primary" onclick="nextStep(4)">İleri <i
                                            class="ti ti-arrow-right"></i></button>
                                </div>
                            </div>

                            <!-- ADIM 4: Taksit Bilgisi -->
                            <div id="step4" class="step-section" style="display:none;">
                                <h5 class="mb-3 text-primary">4. Taksit Bilgisi</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Kalan Tutar</label>
                                        <input type="text" class="form-control bg-light fw-bold text-danger"
                                            id="kalanTutar" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Taksit Başlangıcı</label>
                                        <input type="date" class="form-control" name="taksit_baslangic"
                                            id="taksitBaslangic" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Taksit Sayısı</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="taksit_sayisi"
                                                id="taksitSayisi" min="1" value="1">
                                            <button class="btn btn-outline-primary" type="button"
                                                onclick="taksitOlustur()">Taksit Oluştur</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Taksit Listesi -->
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-sm" id="taksitTablosu">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Taksit No</th>
                                                <th>Vade Tarihi</th>
                                                <th>Tutar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- JS ile dolacak -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)"><i
                                            class="ti ti-arrow-left"></i> Geri</button>
                                    <button type="submit" class="btn btn-success btn-lg"><i class="ti ti-check"></i>
                                        SÖZLEŞME OLUŞTUR</button>
                                </div>
                            </div>

                        </form>
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

<script>
    (function () {
        if (!window.jQuery) return;
        jQuery(function ($) {
            if ($.fn.select2) $('.select').each(function () { $(this).select2({ width: '100%' }); });
            if (window.feather && feather.replace) feather.replace();
        });
    })();

    // Hesaplama Fonksiyonları
    function hesaplaStep2() {
        var fiyat = parseFloat(document.getElementById('birimFiyat').value) || 0;
        var miktar = parseFloat(document.getElementById('miktar').value) || 0;
        var toplam = fiyat * miktar;
        document.getElementById('hesaplananTutar').value = toplam.toFixed(2) + ' TL';

        // Step 3 verilerini hazırla
        document.getElementById('odemeBirimTutar').value = fiyat.toFixed(2) + ' TL';
        document.getElementById('odemeTutar').value = toplam.toFixed(2) + ' TL';
        document.getElementById('odemeToplamTutar').value = toplam.toFixed(2) + ' TL';
    }

    function hesaplaStep3() {
        var toplamStr = document.getElementById('odemeToplamTutar').value.replace(' TL', '');
        var toplam = parseFloat(toplamStr) || 0;
        var pesinat = parseFloat(document.getElementById('pesinat').value) || 0;
        var kalan = toplam - pesinat;

        document.getElementById('kalanTutar').value = kalan.toFixed(2) + ' TL';
    }

    // Event Listeners
    document.getElementById('birimFiyat').addEventListener('input', hesaplaStep2);
    document.getElementById('miktar').addEventListener('input', hesaplaStep2);
    document.getElementById('pesinat').addEventListener('input', hesaplaStep3);

    // Navigasyon
    function nextStep(step) {
        // Validasyon eklenebilir
        if (step === 3) hesaplaStep2();
        if (step === 4) hesaplaStep3();

        $('.step-section').hide();
        $('#step' + step).fadeIn();

        // Progress Bar Güncelle
        var width = (step / 4) * 100;
        $('#progressBar').css('width', width + '%').text('Adım ' + step + '/4');
    }

    function prevStep(step) {
        $('.step-section').hide();
        $('#step' + step).fadeIn();
        var width = (step / 4) * 100;
        $('#progressBar').css('width', width + '%').text('Adım ' + step + '/4');
    }

    // Taksit Oluşturma
    function taksitOlustur() {
        var kalanStr = document.getElementById('kalanTutar').value.replace(' TL', '');
        var kalan = parseFloat(kalanStr) || 0;
        var taksitSayisi = parseInt(document.getElementById('taksitSayisi').value) || 1;
        var baslangicTarihi = new Date(document.getElementById('taksitBaslangic').value);

        if (kalan <= 0) {
            alert("Kalan tutar 0 veya geçersiz!");
            return;
        }

        var taksitTutar = kalan / taksitSayisi;
        var tbody = document.querySelector('#taksitTablosu tbody');
        tbody.innerHTML = '';

        for (var i = 0; i < taksitSayisi; i++) {
            var vade = new Date(baslangicTarihi);
            vade.setMonth(vade.getMonth() + i); // Her ay 1 artır

            var gun = vade.getDate().toString().padStart(2, '0');
            var ay = (vade.getMonth() + 1).toString().padStart(2, '0');
            var yil = vade.getFullYear();
            var tarihFormat = gun + '.' + ay + '.' + yil;

            var row = `<tr>
                <td>${i + 1}</td>
                <td>${tarihFormat}</td>
                <td>${taksitTutar.toFixed(2)} TL</td>
            </tr>`;
            tbody.innerHTML += row;
        }
    }
</script>
</body>

</html>