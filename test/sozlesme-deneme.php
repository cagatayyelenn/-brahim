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
$birimler = $db->finds('birim'); // Birim tablosunda durum kolonu yok
$kasalar = $db->finds('kasa1', 'durum', 1);  // Kasa tablosu: kasa1, durum kolonu: durum
$odemeYontemleri = $db->finds('odeme_yontem1', 'durum', 1); // Ödeme yöntemleri

// Öğrenci ID kontrolü
$ogrenci_id = isset($_GET['id']) ? $_GET['id'] : 0;
$ogrenci = [];

if ($ogrenci_id) {
    // Tablo adı: ogrenci1, URL'deki id: ogrenci_numara
    $ogrenci = $db->get("SELECT * FROM ogrenci1 WHERE ogrenci_numara = :id", [':id' => $ogrenci_id]);
}

// Eğer öğrenci bulunamazsa listeye yönlendir
if (!$ogrenci) {
    header("Location: ogrenci-listesi.php");
    exit;
}
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

                        <div class="alert alert-primary d-flex align-items-center mb-4">
                            <span class="avatar avatar-md bg-primary text-white me-3">
                                <i class="ti ti-user fs-4"></i>
                            </span>
                            <div>
                                <h5 class="mb-1 text-primary">Seçili Öğrenci</h5>
                                <p class="mb-0 fs-16">
                                    <strong><?= htmlspecialchars($ogrenci['ogrenci_adi'] . ' ' . $ogrenci['ogrenci_soyadi']) ?></strong>
                                    <span class="text-muted ms-2">(Öğrenci No: <?= htmlspecialchars($ogrenci['ogrenci_numara']) ?>)</span>
                                </p>
                            </div>
                        </div>

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
                                        <input type="text" class="form-control money-input" name="birim_fiyat"
                                            id="birimFiyat" placeholder="0,00" required>
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
                                        <input type="text" class="form-control money-input" name="pesinat" id="pesinat"
                                            placeholder="0,00">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Ödeme Seçeneği</label>
                                        <select class="form-select select" name="odeme_yontemi">
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($odemeYontemleri as $y): ?>
                                                <option value="<?= $y['yontem_id'] ?>"><?= $y['yontem_adi'] ?></option>
                                            <?php endforeach; ?>
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

    // Para formatlama fonksiyonu (1234.56 -> 1.234,56)
    function formatMoney(n) {
        var num = parseFloat(n) || 0;
        return num.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Para ayrıştırma fonksiyonu (1.234,56 -> 1234.56)
    function parseMoney(s) {
        if (!s) return 0;
        // Sadece sayı, virgül ve eksi işaretini bırak, gerisini sil (noktaları sil)
        // 1.234,56 -> 1234,56 -> 1234.56
        var clean = s.toString().replace(/\./g, '').replace(',', '.');
        return parseFloat(clean) || 0;
    }

    // Input maskeleme (Basitçe blur'da formatla, focus'ta temizle)
    $('.money-input').on('blur', function () {
        var val = parseMoney($(this).val());
        $(this).val(formatMoney(val));
    }).on('focus', function () {
        var val = parseMoney($(this).val());
        if (val === 0) $(this).val('');
        else $(this).val(val.toString().replace('.', ',')); // Düzenlerken virgüllü göster
    });

    // Hesaplama Fonksiyonları
    function hesaplaStep2() {
        var fiyat = parseMoney(document.getElementById('birimFiyat').value);
        var miktar = parseFloat(document.getElementById('miktar').value) || 0;
        var toplam = fiyat * miktar;

        document.getElementById('hesaplananTutar').value = formatMoney(toplam) + ' TL';

        // Step 3 verilerini hazırla
        document.getElementById('odemeBirimTutar').value = formatMoney(fiyat) + ' TL';
        document.getElementById('odemeTutar').value = formatMoney(toplam) + ' TL';
        document.getElementById('odemeToplamTutar').value = formatMoney(toplam) + ' TL';

        // Eğer peşinat varsa kalan tutarı da güncelle
        hesaplaStep3();
    }

    function hesaplaStep3() {
        var toplamStr = document.getElementById('odemeToplamTutar').value.replace(' TL', '');
        var toplam = parseMoney(toplamStr);
        var pesinat = parseMoney(document.getElementById('pesinat').value);
        var kalan = toplam - pesinat;

        document.getElementById('kalanTutar').value = formatMoney(kalan) + ' TL';
    }

    // Event Listeners
    document.getElementById('birimFiyat').addEventListener('input', hesaplaStep2); // Yazarken de hesaplasın (arka planda parseMoney hallediyor)
    document.getElementById('miktar').addEventListener('input', hesaplaStep2);
    document.getElementById('pesinat').addEventListener('input', hesaplaStep3);

    // Navigasyon
    function nextStep(step) {
        // Mevcut adımı bul (Gidilecek adım - 1)
        var currentStep = step - 1;
        var valid = true;

        // Mevcut adımdaki required olan input ve selectleri kontrol et
        $('#step' + currentStep + ' [required]').each(function () {
            if (!$(this).val()) {
                valid = false;
                $(this).addClass('is-invalid'); // Hata görseli ekle (Bootstrap class)
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!valid) {
            // SweetAlert varsa kullan, yoksa alert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Eksik Bilgi',
                    text: 'Lütfen zorunlu alanları doldurunuz.',
                    confirmButtonText: 'Tamam'
                });
            } else {
                alert('Lütfen zorunlu alanları doldurunuz.');
            }
            return; // İlerlemeyi durdur
        }

        // Validasyon başarılıysa hesaplamaları yap
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
        var kalan = parseMoney(kalanStr);
        var taksitSayisi = parseInt(document.getElementById('taksitSayisi').value) || 1;
        var baslangicTarihi = new Date(document.getElementById('taksitBaslangic').value);

        if (kalan <= 0) {
            alert("Kalan tutar 0 veya geçersiz!");
            return;
        }

        var taksitTutar = kalan / taksitSayisi;
        // Küsürat düzeltmesi (toplam tutarın tam tutması için)
        var toplamTaksit = 0;

        var tbody = document.querySelector('#taksitTablosu tbody');
        tbody.innerHTML = '';

        for (var i = 0; i < taksitSayisi; i++) {
            var vade = new Date(baslangicTarihi);
            vade.setMonth(vade.getMonth() + i); // Her ay 1 artır

            var gun = vade.getDate().toString().padStart(2, '0');
            var ay = (vade.getMonth() + 1).toString().padStart(2, '0');
            var yil = vade.getFullYear();
            var tarihFormat = gun + '.' + ay + '.' + yil;

            // Son taksit kuruş farkını düzeltsin
            var buTaksit = taksitTutar;
            if (i === taksitSayisi - 1) {
                buTaksit = kalan - toplamTaksit;
            }
            buTaksit = parseFloat(buTaksit.toFixed(2)); // Yuvarlama
            toplamTaksit += buTaksit;

            var row = `<tr>
                <td>${i + 1}</td>
                <td>${tarihFormat}</td>
                <td>${formatMoney(buTaksit)} TL</td>
            </tr>`;
            tbody.innerHTML += row;
        }
    }
</script>
<script>
    // Form Gönderimi (AJAX)
    $('#contractForm').on('submit', function (e) {
        e.preventDefault();

        // Temel validasyonlar
        var ogrenci_id = <?= (int) $ogrenci_id ?>;
        if (!ogrenci_id) {
            alert("Lütfen bir öğrenci seçiniz (URL'de id parametresi yok).");
            return;
        }

        // Verileri topla
        var formData = {
            ogrenci_id: ogrenci_id,
            donem_id: $('select[name="donem_id"]').val(),
            sinif_id: $('select[name="sinif_id"]').val(),
            grup_id: $('select[name="grup_id"]').val(),
            alan_id: $('select[name="alan_id"]').val(),

            birim_id: $('select[name="birim_id"]').val(),
            birim_fiyat: parseMoney($('#birimFiyat').val()),
            miktar: $('#miktar').val(),
            toplam_ucret: parseMoney($('#hesaplananTutar').val().replace(' TL', '')),

            pesinat: parseMoney($('#pesinat').val()),
            yontem_id: $('select[name="odeme_yontemi"]').val(),
            kasa_id: $('select[name="kasa_id"]').val(),

            taksitler: []
        };

        // Taksitleri topla
        $('#taksitTablosu tbody tr').each(function () {
            var tarihStr = $(this).find('td:eq(1)').text(); // 01.01.2025
            var tutarStr = $(this).find('td:eq(2)').text().replace(' TL', '');

            // Tarihi YYYY-MM-DD formatına çevir
            var parts = tarihStr.split('.');
            var tarihYMD = parts[2] + '-' + parts[1] + '-' + parts[0];

            formData.taksitler.push({
                tarih: tarihYMD,
                tutar: parseMoney(tutarStr)
            });
        });

        formData.taksitler = JSON.stringify(formData.taksitler);

        // AJAX İsteği
        $.ajax({
            url: 'sozlesme-ajax/sozlesme-kaydet.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function () {
                $('button[type="submit"]').prop('disabled', true).html('<i class="ti ti-loader animate-spin"></i> Kaydediliyor...');
            },
            success: function (res) {
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: 'Sözleşme başarıyla oluşturuldu.',
                        showConfirmButton: true,
                        confirmButtonText: 'Tamam'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // 1. Yeni sekmede sözleşme çıktısını aç
                            var printUrl = res.redirect || ('sozlesme-belge.php?id=' + res.sozlesme_id);
                            window.open(printUrl, '_blank');

                            // 2. Mevcut sayfayı öğrenci listesine yönlendir
                            window.location.href = 'ogrenci-listesi.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: res.msg || 'Bir hata oluştu.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Sunucu Hatası',
                    text: 'İşlem gerçekleştirilemedi.'
                });
            },
            complete: function () {
                $('button[type="submit"]').prop('disabled', false).html('<i class="ti ti-check"></i> SÖZLEŞME OLUŞTUR');
            }
        });
    });
</script>
</body>

</html>