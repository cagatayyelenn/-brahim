<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);
?>


<?php
$aktifSubeId = (int) ($_SESSION['sube_id'] ?? 0);
if ($aktifSubeId) {
    $kasalar = $db->get("SELECT kasa_id,kasa_adi FROM kasa1 WHERE durum=1 AND (sube_id=:sid OR sube_id IS NULL) ORDER BY sira,kasa_adi", [':sid' => $aktifSubeId]);
} else {
    $kasalar = $db->finds('kasa1', 'durum', 1, ['kasa_id', 'kasa_adi', 'sira']);
    usort($kasalar, fn($a, $b) => ($a['sira'] <=> $b['sira']) ?: strcmp($a['kasa_adi'], $b['kasa_adi']));
}
// gelir ekeleme modal için
$hareketTurleri = $db->get("SELECT tur_id, tur_adi FROM kasa_hareket_turleri ORDER BY tur_adi");

$turler = $db->get("SELECT tur_id, tur_adi, islem_tipi FROM kasa_hareket_turleri WHERE aktif=1 ORDER BY islem_tipi DESC, tur_adi ASC");
// gelir ekeleme modal için

$odemeYontemleri = $db->finds('odeme_yontem1', 'durum', 1, ['yontem_id', 'yontem_adi', 'sira']);
usort($odemeYontemleri, fn($a, $b) => ($a['sira'] <=> $b['sira']) ?: strcmp($a['yontem_adi'], $b['yontem_adi']));

$pageTitle = "Kasa Listesi";
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
$page_styles[] = ['href' => 'assets/plugins/icons/themify/themify.css'];
$page_styles[] = ['href' => 'assets/plugins/daterangepicker/daterangepicker.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto  ">
                <h3 class="page-title mb-1">Kasa</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kasa İşlemleri</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="col-xl-12 d-flex">
                    <div class="row flex-fill">

                        <div class="col-xl-6 col-md-12 d-flex">
                            <a href="javascript:void(0);"
                                class="card bg-success-transparent border border-5 border-white animate-card flex-fill "
                                data-bs-toggle="modal" data-bs-target="#modalGelirEkle">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-lg bg-success rounded flex-shrink-0 me-2">
                                                <i class="ti-stats-up"></i>
                                            </span>
                                            <div class="overflow-hidden">
                                                <h6 class="fw-semibold text-default">Gelir Ekle</h6>
                                            </div>
                                        </div>
                                        <span
                                            class="btn btn-white success-btn-hover avatar avatar-sm p-0 flex-shrink-0 rounded-circle">
                                            <i class="ti ti-chevron-right fs-14"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-6 col-md-12 d-flex">
                            <a href="javascript:void(0);"
                                class="card bg-danger-transparent border border-5 border-white animate-card flex-fill "
                                data-bs-toggle="modal" data-bs-target="#modalGiderEkle">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-lg bg-danger rounded flex-shrink-0 me-2">
                                                <i class="ti-stats-down"></i>
                                            </span>
                                            <div class="overflow-hidden">
                                                <h6 class="fw-semibold text-default">Gider Ekle</h6>
                                            </div>
                                        </div>
                                        <span
                                            class="btn btn-white danger-btn-hover avatar avatar-sm p-0 flex-shrink-0 rounded-circle">
                                            <i class="ti ti-chevron-right fs-14"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <input type="hidden" id="aktif_sube_id" value="<?= (int) $sube_id ?>">
        <!-- Filter Section -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-center flex-wrap pb-0">

                <div class="d-flex align-items-center flex-wrap justify-content-center">
                    <div class="input-icon-start mb-3 me-2 position-relative">
                        <span class="icon-addon"><i class="ti ti-calendar"></i></span>
                        <input type="text" class="form-control date-range bookingrange me-3" placeholder="Tarih Seçiniz"
                            value="Tarih Seçiniz">
                    </div>

                    <div class="dropdown mb-3 me-2">
                        <a href="javascript:void(0);" class="btn btn-outline-light bg-white dropdown-toggle"
                            data-bs-toggle="dropdown">
                            <i class="ti ti-sort-ascending-2 me-2"></i><span id="kasaSecTxt">Kasa Seçiniz</span>
                        </a>
                        <ul class="dropdown-menu p-3">
                            <li><a href="javascript:void(0);" data-kasa-id="0"
                                    class="dropdown-item rounded-1   kasa-select">Bütün Kasalar</a></li>
                            <?php foreach ($kasalar as $k): ?>
                                <li>
                                    <a href="javascript:void(0);" data-kasa-id="<?= (int) $k['kasa_id'] ?>"
                                        class="dropdown-item rounded-1 kasa-select">
                                        <?= htmlspecialchars($k['kasa_adi']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="input-icon-start mb-3 me-2 position-relative">
                        <button id="btnListele" class="btn btn-outline-primary d-flex align-items-center">
                            <i class="ti ti-menu me-2"></i>Listele
                        </button>
                    </div>
                </div>
            </div>
            <div class="row g-3 px-3 pt-3">
                <div class="col-xl-3 d-flex">
                    <div class="card flex-fill w-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Nakit</h4>
                        </div>
                        <div class="card-body">
                            <div class="row" id="box_nakit">
                                <div class="col text-center border-end">
                                    <h6 class="mb-1">Giriş</h6>
                                    <p class="mb-1"><span id="nakit_giris">0,00 ₺</span></p>
                                </div>
                                <div class="col text-center border-end">
                                    <h6 class="mb-1">Çıkış</h6>
                                    <p class="mb-1"><span id="nakit_cikis">0,00 ₺</span></p>
                                </div>
                                <div class="col text-center">
                                    <h6 class="mb-1">Toplam</h6>
                                    <p class="mb-1"><span id="nakit_toplam">0,00 ₺</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 d-flex">
                    <div class="card flex-fill w-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">POS</h4>
                        </div>
                        <div class="card-body">
                            <div class="border rounded p-3" id="box_pos">
                                <div class="row">
                                    <div class="col text-center border-end">
                                        <h6 class="mb-1">Giriş</h6>
                                        <p class="mb-1"><span id="pos_giris">0,00 ₺</span></p>
                                    </div>
                                    <div class="col text-center border-end">
                                        <h6 class="mb-1">Çıkış</h6>
                                        <p class="mb-1"><span id="pos_cikis">0,00 ₺</span></p>
                                    </div>
                                    <div class="col text-center">
                                        <h6 class="mb-1">Toplam</h6>
                                        <p class="mb-1"><span id="pos_toplam">0,00 ₺</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 d-flex">
                    <div class="card flex-fill w-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Banka</h4>
                        </div>
                        <div class="card-body">
                            <div class="border rounded p-3" id="box_banka">
                                <div class="row">
                                    <div class="col text-center border-end">
                                        <h6 class="mb-1">Giriş</h6>
                                        <p class="mb-1"><span id="banka_giris">0,00 ₺</span></p>
                                    </div>
                                    <div class="col text-center border-end">
                                        <h6 class="mb-1">Çıkış</h6>
                                        <p class="mb-1"><span id="banka_cikis">0,00 ₺</span></p>
                                    </div>
                                    <div class="col text-center">
                                        <h6 class="mb-1">Toplam</h6>
                                        <p class="mb-1"><span id="banka_toplam">0,00 ₺</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 d-flex">
                    <div class="card flex-fill w-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Toplam</h4>
                        </div>
                        <div class="card-body">
                            <div class="border rounded p-3" id="box_toplam">
                                <div class="row">
                                    <div class="col text-center border-end">
                                        <h6 class="mb-1">Giriş</h6>
                                        <p class="mb-1"><span id="toplam_giris">0,00 ₺</span></p>
                                    </div>
                                    <div class="col text-center border-end">
                                        <h6 class="mb-1">Çıkış</h6>
                                        <p class="mb-1"><span id="toplam_cikis">0,00 ₺</span></p>
                                    </div>
                                    <div class="col text-center">
                                        <h6 class="mb-1">Toplam</h6>
                                        <p class="mb-1"><span id="toplam_toplam">0,00 ₺</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-body p-0 py-3">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table datatable align-middle mb-0" id="kasaTable">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Tarih</th>
                                <th>Kasa</th>
                                <th>Bağlantı</th>
                                <th>Kategori</th>
                                <th>Ödeme Türü</th>
                                <th>Açıklama</th>
                                <th>İşlemi Yapan</th>
                                <th>Yön</th>
                                <th class="text-end">Tutar</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->

<div class="modal fade" id="modalGelirEkle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="ti ti-cash fs-22 me-2 text-success"></i>
                    <h4 class="modal-title">Kasa Gelir / Tahsilat</h4>
                </div>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <form id="formGelirEkle" action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-lg-4">
                            <label class="form-label">Hareket Türü</label>
                            <select class="form-select" name="hareket_tur_id" id="hareketTur" required>
                                <option value="">Seçiniz</option>
                                <optgroup label="Gelir">
                                    <?php foreach ($turler as $t): ?>
                                        <?php if ($t['islem_tipi'] === 'giris'): ?>
                                            <option value="<?= (int) $t['tur_id'] ?>" data-islem="giris">
                                                <?= htmlspecialchars($t['tur_adi']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <input type="hidden" name="yon" id="hidYon" value="GIRIS">

                        <div class="col-lg-4">
                            <label class="form-label">Ödeme Türü</label>
                            <select class="form-select" name="yontem_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($odemeYontemleri as $y): ?>
                                    <option value="<?= (int) $y['yontem_id'] ?>"><?= htmlspecialchars($y['yontem_adi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Kasa</label>
                            <select class="form-select" name="kasa_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($kasalar as $k): ?>
                                    <option value="<?= (int) $k['kasa_id'] ?>"><?= htmlspecialchars($k['kasa_adi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tarih</label>
                            <div class="position-relative">
                                <input type="text" class="form-control datetimepicker" name="hareket_tarihi" required>
                                <span
                                    class="position-absolute end-0 top-0 h-100 d-flex align-items-center px-2 text-muted">
                                    <i class="ti ti-calendar"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tutar</label>
                            <input type="text" class="form-control text-end" name="tutar" placeholder="0,00" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <input type="text" class="form-control" name="aciklama"
                                placeholder="Örn: Ek kayıt geliri / bağış">
                        </div>

                        <input type="hidden" name="yon" value="GIRIS">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-cash me-1"></i> Kaydet
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<div class="modal fade" id="modalGiderEkle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="ti ti-credit-card-off fs-22 me-2 text-danger"></i>
                    <h4 class="modal-title">Kasa Gider / Çıkış</h4>
                </div>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <form id="formGiderEkle" action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-lg-4">
                            <label class="form-label">Hareket Türü</label>
                            <select class="form-select" name="hareket_tur_id" required>
                                <option value="">Seçiniz</option>
                                <optgroup label="Gider">
                                    <?php foreach ($turler as $t): ?>
                                        <?php if ($t['islem_tipi'] === 'cikis'): ?>
                                            <option value="<?= (int) $t['tur_id'] ?>" data-islem="cikis">
                                                <?= htmlspecialchars($t['tur_adi']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <input type="hidden" name="yon" value="CIKIS">

                        <div class="col-lg-4">
                            <label class="form-label">Ödeme Türü</label>
                            <select class="form-select" name="yontem_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($odemeYontemleri as $y): ?>
                                    <option value="<?= (int) $y['yontem_id'] ?>"><?= htmlspecialchars($y['yontem_adi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Kasa</label>
                            <select class="form-select" name="kasa_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($kasalar as $k): ?>
                                    <option value="<?= (int) $k['kasa_id'] ?>"><?= htmlspecialchars($k['kasa_adi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tarih</label>
                            <div class="position-relative">
                                <input type="text" class="form-control datetimepicker" name="hareket_tarihi" required>
                                <span
                                    class="position-absolute end-0 top-0 h-100 d-flex align-items-center px-2 text-muted">
                                    <i class="ti ti-calendar"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tutar</label>
                            <input type="text" class="form-control text-end" name="tutar" placeholder="0,00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <input type="text" class="form-control" name="aciklama"
                                placeholder="Örn: Elektrik faturası">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-credit-card-off me-1"></i> Kaydet (Gider)
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Delete Modal -->
<div class="modal fade" id="delete-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="accounts-invoices.html">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>Confirm Deletion</h4>
                    <p>You want to delete all the marked items, this cant be undone once you delete.</p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Delete Modal -->

</div>
<!-- /Main Wrapper -->

<script src="assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>


<!-- Moment ve locale dosyası -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment-with-locales.min.js"></script>
<script>moment.locale('tr');</script>

<!-- Daterangepicker -->
<script src="assets/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>

<!-- Diğer scriptler -->
<script src="assets/js/feather.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="assets/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
<script src="assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<script src="assets/js/script.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function () {
        $('#formGelirEkle').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const postUrl = 'sozlesme-ajax/kasa-gelir-kaydet.php';

            Swal.fire({ title: 'İşleniyor', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

            $.ajax({
                url: postUrl,
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json'
            }).done(function (data) {
                if (data && data.ok) {
                    const modalEl = document.getElementById('modalGelirEkle');
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.hide();
                    $form[0].reset();

                    Swal.fire({ icon: 'success', title: 'Kayıt eklendi', text: data.msg || 'Gelir eklendi', timer: 1000, showConfirmButton: false }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Hata', text: (data && data.msg) ? data.msg : 'Kayıt eklenemedi.' });
                }
            }).fail(function (xhr) {
                Swal.fire({ icon: 'error', title: 'Sunucu Hatası', text: xhr.responseText || 'İstek başarısız.' });
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const $ = window.jQuery;

        $('#formGiderEkle').on('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);

            Swal.fire({ title: 'İşleniyor', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

            $.ajax({
                url: 'sozlesme-ajax/kasa-gider-kaydet.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (res) {
                if (res && res.ok && Number(res.id) > 0) {
                    // modal varsa kapat
                    const modalEl = document.getElementById('modalGiderEkle');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                    }

                    Swal.fire({ icon: 'success', title: 'Başarılı', text: res.msg || 'Kayıt eklendi', timer: 1000, showConfirmButton: false }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Hata', text: (res && res.msg) ? res.msg : 'Kayıt eklenemedi' });
                }
            }).fail(function (xhr) {
                Swal.fire({ icon: 'error', title: 'Hata', text: 'Sunucuya ulaşılamadı' });
                console.log('AJAX FAIL:', xhr.responseText);
            });
        });
    });
</script>

<script>
    let dt;               // DataTables instance
    let activeKasaId = 0; // Seçili kasa_id

    function trMoney(n) {
        const v = Number(n || 0);
        return v.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
    }
    function moneyCell(yon, tutar) {
        const val = trMoney(tutar);
        if (yon === 'GIRIS') return { badge: '<span class="badge bg-success">GİRİŞ</span>', text: `+${val}`, cls: 'text-success' };
        if (yon === 'CIKIS') return { badge: '<span class="badge bg-danger">ÇIKIŞ</span>', text: `-${val}`, cls: 'text-danger' };
        return { badge: '', text: val, cls: '' };
    }
    function parseRange() {
        const raw = $('.bookingrange').val() || '';
        const m = raw.match(/(\d{2})\/(\d{2})\/(\d{4})\s*-\s*(\d{2})\/(\d{2})\/(\d{4})/);
        if (!m) {
            const t = moment().format('YYYY-MM-DD');
            return { start: t, end: t };
        }
        return { start: `${m[3]}-${m[2]}-${m[1]}`, end: `${m[6]}-${m[5]}-${m[4]}` };
    }
    function getSubeId() {
        const el = document.getElementById('aktif_sube_id');
        return el ? (parseInt(el.value, 10) || 0) : 0;
    }

    /* ---- DateRangePicker ---- */
    function initRangePicker() {
        if (!$('.bookingrange').length) return;
        if (typeof $.fn.daterangepicker !== 'function') {
            console.error('Daterangepicker yüklü değil!');
            return;
        }
        const start = moment(), end = moment();
        function booking_range(s, e) {
            $('.bookingrange').val(s.format('DD/MM/YYYY') + ' - ' + e.format('DD/MM/YYYY'));
        }
        $('.bookingrange').daterangepicker({
            startDate: start, endDate: end, autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY', separator: ' - ', applyLabel: 'Uygula', cancelLabel: 'İptal',
                fromLabel: 'Başlangıç', toLabel: 'Bitiş', customRangeLabel: 'Özel Aralık', weekLabel: 'Hf',
                daysOfWeek: ['Paz', 'Pts', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'],
                monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                firstDay: 1
            },
            ranges: {
                'Bugün': [moment(), moment()],
                'Dün': [moment().subtract(1, 'day'), moment().subtract(1, 'day')],
                'Son 7 Gün': [moment().subtract(6, 'day'), moment()],
                'Son 30 Gün': [moment().subtract(29, 'day'), moment()],
                'Bu Yıl': [moment().startOf('year'), moment().endOf('year')]
            }
        }, booking_range);
        $('.bookingrange').val('Tarih Seçiniz');
        $('.bookingrange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });
    }

    /* ---- DataTables ---- */
    function initDT() {
        if (!$('#kasaTable').length) return;
        if (!$.fn.DataTable) {
            console.error('DataTables yüklü değil!');
            return;
        }
        if ($.fn.DataTable.isDataTable('#kasaTable')) {
            dt = $('#kasaTable').DataTable();
            return;
        }
        dt = $('#kasaTable').DataTable({
            dom: 'lfrtip',
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[0, 'desc']], // 2. sütun Tarih
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' },
            columnDefs: [
                { targets: [8, 10], orderable: false },
                { targets: [0, 3, 8, 10], searchable: false }
            ]
        });
    }

    /* ---- Liste verileri ---- */
    function loadTable() {
        if (!dt) { initDT(); }
        const { start, end } = parseRange();
        const sube_id = getSubeId();
        $.getJSON('sozlesme-ajax/kasa-listesi.php', {
            start, end, kasa_id: activeKasaId, sube_id, page: 1, limit: 500
        }).done(function (res) {
            if (!dt) { return; }
            if (!res || !res.status) { dt.clear().draw(); return; }
            const rows = (res.rows || []).map(r => {
                const m = moneyCell(r.yon, r.tutar);
                const bag = (r.ogrenci_numara && r.ogrenci_adsoyad)
                    ? `<a href="ogrenci-detay.php?id=${r.ogrenci_numara}" class="link-primary">${r.ogrenci_numara} - ${r.ogrenci_adsoyad}</a>`
                    : '';
                const actions = `
        <div class="dropdown">
          <a href="#" class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0" data-bs-toggle="dropdown">
            <i class="ti ti-dots-vertical fs-14"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-3">
            <li><a class="dropdown-item rounded-1" href="#" data-id="${r.id}">
              <i class="ti ti-edit-circle me-2"></i>Kasa verisini sil
            </a></li>
          </ul>
        </div>`;
                return [
                    `<a href="kasa-hareket-detay.php?id=${r.id}" data-order="${r.id}" class="link-primary">${r.id}</a>`,
                    r.tarih || '', r.kasa || '', bag,
                    r.kategori || '', r.odeme_turu || '',
                    r.aciklama || '', r.islem_yapan || '',
                    m.badge,
                    `<span class="d-block text-end ${m.cls}">${m.text}</span>`,
                    actions
                ];
            });
            dt.clear().rows.add(rows).draw();
            dt.order([0, 'desc']).draw(); // en son eklenen yukarı gelsin
        });
    }

    /* ---- Özet kutuları ---- */
    function loadSummary() {
        const { start, end } = parseRange();
        const sube_id = getSubeId();
        $.getJSON('sozlesme-ajax/kasa-ozeti.php', { start, end, kasa_id: activeKasaId, sube_id })
            .done(function (res) {
                if (!res || !res.status) return;
                const oz = res.ozet || {};
                const gn = res.genel || { giris: 0, cikis: 0, toplam: 0 };
                $('#nakit_giris').text(trMoney(oz.NAKIT?.giris || 0));
                $('#nakit_cikis').text(trMoney(oz.NAKIT?.cikis || 0));
                $('#nakit_toplam').text(trMoney(oz.NAKIT?.toplam || 0));
                $('#pos_giris').text(trMoney(oz.POS?.giris || 0));
                $('#pos_cikis').text(trMoney(oz.POS?.cikis || 0));
                $('#pos_toplam').text(trMoney(oz.POS?.toplam || 0));
                $('#banka_giris').text(trMoney(oz.BANKA?.giris || 0));
                $('#banka_cikis').text(trMoney(oz.BANKA?.cikis || 0));
                $('#banka_toplam').text(trMoney(oz.BANKA?.toplam || 0));
                $('#toplam_giris').text(trMoney(gn.giris || 0));
                $('#toplam_cikis').text(trMoney(gn.cikis || 0));
                $('#toplam_toplam').text(trMoney(gn.toplam || 0));
            });
    }
    $(function () {
        const $act = $('.kasa-select.active').first();
        if ($act.length) {
            activeKasaId = parseInt($act.data('kasa-id') || 0, 10);
            $('#kasaSecTxt').text($act.text().trim());
        } else {
            // hiçbir aktif yoksa varsayılan "Kasa Seçiniz" kalsın
            $('#kasaSecTxt').text('Kasa Seçiniz');
        }
    });

    /* ---- Olaylar ---- */
    $(document).on('click', '.kasa-select', function () {
        $('.kasa-select').removeClass('active');
        $(this).addClass('active');
        activeKasaId = parseInt($(this).data('kasa-id') || 0, 10);
        $('#kasaSecTxt').text($(this).text().trim());
    });

    $(document).on('click', '#btnListele', function (e) {
        e.preventDefault();
        loadSummary();
        loadTable();
    });

    /* ---- Başlat ---- */
    $(function () {
        initRangePicker();
        initDT();
    });
</script>

<script>
    $(document).on("click", ".dropdown-item", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        if (!id) return;

        Swal.fire({
            title: "Emin misiniz?",
            text: "Bu kasa hareketi silinecek! Ve sistem uyuşmazlığı olabilir",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Evet, Sil",
            cancelButtonText: "Vazgeç"
        }).then((res) => {
            if (res.isConfirmed) {

                $.post("sozlesme-ajax/kasa-sil.php", { id: id }, function (json) {

                    if (json.ok) {
                        Swal.fire({
                            icon: "success",
                            title: "Silindi",
                            text: json.msg,
                            timer: 1500
                        });

                        loadTable(); // tabloyu yenile
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Hata",
                            text: json.msg || "Silme işlemi yapılamadı."
                        });
                    }

                }, "json");

            }
        });
    });
</script>
</body>

</html>