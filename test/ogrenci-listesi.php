<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();


$sube_id = (int) ($_SESSION['sube_id'] ?? 0);
$showPasif = isset($_GET['showPasif']) && $_GET['showPasif'] == '1';

$where = "WHERE o.sube_id = :sube_id";
if (!$showPasif) {
    $where .= " AND o.aktif = 1";
}

$sql = "SELECT 
    o.ogrenci_id,
    o.ogrenci_numara AS ogrenci_no,
    o.ogrenci_tc,
    CONCAT(o.ogrenci_adi, ' ', o.ogrenci_soyadi) AS ad_soyad,
    o.ogrenci_tel AS telefon,
    o.ogrenci_mail AS email,
    o.ogrenci_cinsiyet AS cinsiyet,
    o.ogrenci_dogumtar AS dogum_tarihi,
    IF(o.aktif = 1, 'Aktif', 'Pasif') AS durum
FROM ogrenci1 o
$where
ORDER BY o.aktif DESC, o.ogrenci_id DESC";

$ogrenciler = $db->get($sql, [':sube_id' => $sube_id]);



// Küçük yardımcı
if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('formatDateTRSafe')) {
    function formatDateTRSafe($d)
    {
        if (function_exists('formatDateTR'))
            return formatDateTR($d);
        if (!$d || $d === '0000-00-00')
            return '-';
        $ts = strtotime($d);
        return $ts ? date('d.m.Y', $ts) : '-';
    }
}
?>


<?php
$pageTitle = "Öğrenci Listesi";
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Öğrenci Listesi</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="ansayfa.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Öğrenci Listesi</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">

                <!-- Pasif Göster/Gizle Switch -->
                <div class="form-check form-switch me-3 mb-2">
                    <input class="form-check-input" type="checkbox" id="chkPasif" <?= $showPasif ? 'checked' : '' ?>
                        onchange="window.location.search = '?showPasif=' + (this.checked ? '1' : '0')">
                    <label class="form-check-label fw-medium" for="chkPasif">Pasifleri Göster</label>
                </div>
                <a href="javascript:void(0);"
                    class="dropdown-toggle btn btn-light fw-medium d-inline-flex align-items-center"
                    data-bs-toggle="dropdown">
                    <i class="ti ti-file-export me-2"></i>Dışa Aktar
                </a>
                <ul class="dropdown-menu  dropdown-menu-end p-3">
                    <li>
                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                class="ti ti-file-type-pdf me-2"></i>Export as PDF</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                class="ti ti-file-type-xls me-2"></i>Export as Excel </a>
                    </li>
                </ul>
            </div> -->
            <div class="mb-2">
                <a href="ogrenci-ekle.php" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-square-rounded-plus me-2"></i>Öğrenci Ekle
                </a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
            <h4 class="mb-3">Öğrenci Listesi</h4>
            <div class="d-flex align-items-center flex-wrap">
                <div class="dropdown mb-3 me-2">
                    <!--  <a href="javascript:void(0);" class="btn btn-outline-light bg-white dropdown-toggle"
                           data-bs-toggle="dropdown" data-bs-auto-close="outside"><i
                                    class="ti ti-filter me-2"></i>Filter</a>
                        <div class="dropdown-menu drop-width">
                            <form action="">
                                <div class="d-flex align-items-center border-bottom p-3">
                                    <h4>Filter</h4>
                                </div>
                                <div class="p-3 pb-0 border-bottom">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Class</label>
                                                <select class="select">
                                                    <option>Select</option>
                                                    <option>I</option>
                                                    <option>II</option>
                                                    <option>III</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Section</label>
                                                <select class="select">
                                                    <option>Select</option>
                                                    <option>A</option>
                                                    <option>B</option>
                                                    <option>C</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <select class="select">
                                                    <option>Select</option>
                                                    <option>Janet</option>
                                                    <option>Joann</option>
                                                    <option>Kathleen</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Gender</label>
                                                <select class="select">
                                                    <option>Select</option>
                                                    <option>Male</option>
                                                    <option>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="select">
                                                    <option>Select</option>
                                                    <option>Active</option>
                                                    <option>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 d-flex align-items-center justify-content-end">
                                    <a href="students.html#" class="btn btn-light me-3">Reset</a>
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                </div>
                            </form>
                        </div> -->
                </div>
            </div>
        </div>
        <div class="card-body p-0 py-3">
            <div class="custom-datatable-filter table-responsive">
                <table class="table datatable align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th class="no-sort" style="width:44px">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th style="min-width:120px;">Öğrenci No</th>
                            <th style="min-width:120px;">TC</th>
                            <th>Ad - Soyad</th>
                            <th>Durum</th>
                            <th>Doğum Tarihi</th>

                            <th class="no-sort" style="min-width:180px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ogrenciler)): ?>
                            <?php foreach ($ogrenciler as $o):
                                $numara = h($o['ogrenci_no'] ?? '');
                                $tc = h($o['ogrenci_tc'] ?? '');
                                $adSoyad = h($o['ad_soyad'] ?? '');
                                $telefon = h($o['telefon'] ?? '');
                                $eposta = h($o['email'] ?? ($o['eposta'] ?? ''));
                                $dogumRaw = $o['dogum_tarihi'] ?? null;
                                $dogum = h(formatDateTRSafe($dogumRaw));
                                $cinsiyetV = trim((string) ($o['cinsiyet'] ?? ''));
                                // 1/Erkek, 0/Kız, diğer durumlar:
                                $cinsiyet = $cinsiyetV === '' ? '-' : (($cinsiyetV === '1' || $cinsiyetV === 'Erkek') ? 'Erkek' : (($cinsiyetV === '0' || $cinsiyetV === 'Kız') ? 'Kız' : h($cinsiyetV)));
                                $durumV = trim((string) ($o['durum'] ?? 'Belirsiz'));
                                $isAktif = (mb_strtolower($durumV, 'UTF-8') === 'aktif');
                                $badgeCls = $isAktif ? 'badge badge-soft-success d-inline-flex align-items-center'
                                    : 'badge badge-soft-secondary d-inline-flex align-items-center';

                                // Avatar (isimden)
                                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($adSoyad ?: $numara) . '&size=64&background=DDD&color=333&bold=true';
                                ?>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input row-check" type="checkbox" value="<?= $numara ?>">
                                        </div>
                                    </td>

                                    <td>
                                        <a href="ogrenci-detay.php?id=<?= $numara ?>" class="link-primary fw-semibold">
                                            <?= $numara ?: '-' ?>
                                        </a>
                                    </td>

                                    <td><?= $tc ?: '-' ?></td>

                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-md me-2">
                                                <img src="<?= h($avatarUrl) ?>" class="img-fluid rounded-circle" alt="avatar">
                                            </span>
                                            <div class="ms-1">
                                                <a href="ogrenci-detay.php?id=<?= $numara ?>"
                                                    class="text-dark fw-semibold"><?= $adSoyad ?: '-' ?></a>
                                                <?php if ($telefon): ?>
                                                    <div class="small text-muted"><?= h($telefon) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="<?= $badgeCls ?>">
                                            <i class="ti ti-circle-filled fs-5 me-1"></i><?= h($durumV) ?>
                                        </span>
                                    </td>

                                    <td><?= $dogum ?></td>



                                    <td>
                                        <div class="d-flex align-items-center">
                                            <!-- Telefon -->
                                            <a href="<?= $telefon ? 'tel:' . preg_replace('/\s+/', '', $telefon) : '#' ?>"
                                                class="btn btn-outline-light bg-white btn-icon d-flex align-items-center justify-content-center rounded-circle p-0 me-2"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="<?= $telefon ? h($telefon) : 'Telefon numarası yok' ?>">
                                                <i class="ti ti-phone<?= $telefon ? '' : ' text-muted' ?>"></i>
                                            </a>

                                            <!-- E-posta -->
                                            <a href="<?= $eposta ? 'mailto:' . h($eposta) : '#' ?>"
                                                class="btn btn-outline-light bg-white btn-icon d-flex align-items-center justify-content-center rounded-circle p-0 me-2"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="<?= $eposta ? h($eposta) : 'E-posta adresi yok' ?>">
                                                <i class="ti ti-mail<?= $eposta ? '' : ' text-muted' ?>"></i>
                                            </a>

                                            <a href="#" class="btn btn-light btn-sm fw-semibold me-2 btn-taksitler"
                                                data-ogr-no="<?= htmlspecialchars($numara, ENT_QUOTES, 'UTF-8') ?>"
                                                data-bs-toggle="modal" data-bs-target="#sozlesmeler">
                                                Taksitler
                                            </a>

                                            <div class="dropdown">
                                                <a href="#"
                                                    class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ti ti-dots-vertical fs-14"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end p-2">
                                                    <li>
                                                        <a class="dropdown-item rounded-1"
                                                            href="ogrenci-detay.php?id=<?= $numara ?>">
                                                            <i class="ti ti-user-circle me-2"></i>Öğrenci Sayfası
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-1"
                                                            href="ogrenci-duzenle.php?id=<?= $numara ?>">
                                                            <i class="ti ti-edit-circle me-2"></i>Öğrenci Düzenle
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-1"
                                                            href="sozlesme-olustur.php?id=<?= $numara ?>">
                                                            <i class="ti ti-file-text me-2"></i>Sözleşme Oluştur
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger rounded-1 btn-ogr-sil" href="#"
                                                            data-numara="<?= $numara ?>">
                                                            <i class="ti ti-trash-x me-2"></i>Öğrenci Sil
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Kayıt bulunamadı.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
<!-- /Page Wrapper -->


<!-- Delete Modal -->
<div class="modal fade" id="delete-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <span class="delete-icon"><i class="ti ti-trash-x"></i></span>
                <h4>Confirm Deletion</h4>
                <p>...</p>
                <div class="d-flex justify-content-center">
                    <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</a>
                    <button type="button" class="btn btn-danger" id="btnDeleteConfirm">Evet, Pasife Al</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Delete Modal -->

<!-- Add Fees Collect -->
<div class="modal fade" id="sozlesmeler" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Öğrenci Taksitleri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>

            <div class="modal-body pt-2">
                <div id="tks-loader" class="text-center py-4" style="display:none;">
                    Yükleniyor...
                </div>

                <div id="tks-empty" class="alert alert-soft-secondary mb-0" style="display:none;">
                    Bu öğrenciye ait taksit bulunamadı.
                </div>

                <div id="tks-wrap" class="table-responsive" style="display:none;">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Sözleşme No</th>
                                <th>#</th>
                                <th>Vade Tarihi</th>
                                <th>Tutar</th>
                                <th>Ödendi</th>
                                <th>Kalan</th>
                                <th>Durum</th>
                                <th class="text-end">İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="tks-tbody"><!-- JS dolduracak --></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>
<!-- /Add Expenses Category -->

</div>

<script src="assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<script src="assets/js/moment.js" type="text/javascript"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<script src="assets/js/feather.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="assets/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
<script src="assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="assets/js/script.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Bootstrap tooltips
        if (window.bootstrap) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }

        // Select-all
        const selectAll = document.getElementById('select-all');
        const checks = document.querySelectorAll('.row-check');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checks.forEach(ch => ch.checked = selectAll.checked);
            });
        }

        // DataTables (opsiyonel)
        if (window.jQuery && jQuery.fn.DataTable) {
            jQuery('.datatable').DataTable({
                pageLength: 25,
                order: [[1, 'desc']], // Öğrenci No
                columnDefs: [
                    { targets: 'no-sort', orderable: false }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
                }
            });
        }

        // Örnek: Silme butonuna tıklama
        document.querySelectorAll('.btn-ogr-sil').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const numara = this.dataset.numara || '';
                // burada swal ile sorup ajax ile silme yapabilirsiniz
                // confirm(...) veya Swal.fire(...) vs.
                console.log('Silinecek öğrenci:', numara);
            });
        });
    });
</script>

<script>
    (function () {
        function tl(n) { return Number(n || 0).toLocaleString('tr-TR', { style: 'currency', currency: 'TRY' }); }
        function b(r) {
            if (r === 'Odendi') return '<span class="badge bg-success"><i class="ti ti-circle-filled me-1"></i>Ödendi</span>';
            if (r === 'Kismi') return '<span class="badge bg-info"><i class="ti ti-circle-filled me-1"></i>Kısmi</span>';
            if (r === 'Gecikmis') return '<span class="badge bg-danger"><i class="ti ti-alert-circle me-1"></i>Gecikmiş</span>';
            return '<span class="badge bg-secondary"><i class="ti ti-circle-filled me-1"></i>Ödenmedi</span>';
        }

        async function loadTaksitler(ogrNo) {
            const loader = document.getElementById('tks-loader');
            const empty = document.getElementById('tks-empty');
            const wrap = document.getElementById('tks-wrap');
            const tbody = document.getElementById('tks-tbody');

            loader.style.display = 'block';
            empty.style.display = 'none';
            wrap.style.display = 'none';
            tbody.innerHTML = '';

            try {
                const res = await fetch('sozlesme-ajax/ogrenci-taksitler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams({ ogr_no: ogrNo })
                });
                const json = await res.json();

                loader.style.display = 'none';

                if (!json.ok || !Array.isArray(json.rows) || json.rows.length === 0) {
                    empty.style.display = 'block';
                    return;
                }

                // rows: sözleşme bazlı taksitler (her satır 1 taksit)
                json.rows.forEach(r => {
                    const kalan = (Number(r.tutar || 0) - Number(r.odendi_tutar || 0));
                    const canPay = kalan > 0;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                      <td>${r.sozlesme_no || '-'}</td>
                      <td>${r.sira_no || '-'}</td>
                      <td>${r.vade_tarihi_tr || '-'}</td>
                      <td>${tl(r.tutar)}</td>
                      <td>${tl(r.odendi_tutar || 0)}</td>
                      <td>${tl(kalan)}</td>
                      <td>${b(r.durum)}</td>
                      <td class="text-end">
                        ${canPay
                            ? `<a href="#"
                    class="btn btn-sm btn-outline-success">
                   <i class="ti ti-cash me-1"></i> Ödenmedi
                 </a>`
                            : `<span class="text-muted">—</span>`
                        }
          </td>
        `;
                    tbody.appendChild(tr);
                });

                wrap.style.display = 'block';

            } catch (err) {
                loader.style.display = 'none';
                empty.style.display = 'block';
                empty.textContent = 'Bir hata oluştu.';
                console.error(err);
            }
        }

        // “Taksitler” butonu tıklanınca modal açılırken yükle
        document.addEventListener('click', function (e) {
            const a = e.target.closest('.btn-taksitler');
            if (!a) return;
            const ogrNo = a.getAttribute('data-ogr-no');
            if (!ogrNo) return;

            // Modal açılınca fetch et
            const modalEl = document.getElementById('sozlesmeler');
            const handler = function () { loadTaksitler(ogrNo); modalEl.removeEventListener('shown.bs.modal', handler); };
            modalEl.addEventListener('shown.bs.modal', handler);
        });
    })();
</script>


<script>
    (function ($) {
        let secilenOgrNo = null;

        // Sil butonuna tıklayınca: öğrenci numarasını al, modalı aç
        $(document).on('click', '.btn-ogr-sil', function (e) {
            e.preventDefault();
            secilenOgrNo = $(this).data('numara');
            console.log("Silinecek öğrenci:", secilenOgrNo);

            // Modal içindeki bilgilendirme metni
            $('#delete-modal h4').text('Öğrenciyi Pasife Al');
            $('#delete-modal p').html(
                'Öğrenci No <b>' + (secilenOgrNo || '') + '</b> pasife alınacak.<br>Devam etmek istiyor musunuz?'
            );

            // Modalı göster
            const m = new bootstrap.Modal(document.getElementById('delete-modal'));
            m.show();
        });

        // Modal içindeki "Evet, Pasife Al" butonu
        // Modal HTML’ine aşağıdaki butonu eklemen gerekiyor (aşağıda örnek var):
        // <button type="button" class="btn btn-danger" id="btnDeleteConfirm">Evet, Pasife Al</button>
        $(document).on('click', '#btnDeleteConfirm', function () {
            if (!secilenOgrNo) {
                alert('Öğrenci numarası bulunamadı.');
                return;
            }

            // İstek at
            $.ajax({
                url: 'sozlesme-ajax/ogrenci-sil.php',   // DOSYA YOLU BURASI -> /test/ajax/ogrenci-sil.php’de olmalı
                method: 'POST',
                dataType: 'json',
                data: { ogrenci_numara: secilenOgrNo },
                beforeSend: function () {
                    $('#btnDeleteConfirm').prop('disabled', true).text('İşleniyor...');
                }
            })
                .done(function (res) {
                    // Modalı kapat
                    const modalEl = document.getElementById('delete-modal');
                    const modalObj = bootstrap.Modal.getInstance(modalEl);
                    if (modalObj) modalObj.hide();

                    if (res && res.ok) {
                        // Başarı
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı',
                            text: res.msg || 'Öğrenci pasife alındı.'
                        }).then(() => {
                            // satırı kaldır veya sayfayı yenile
                            location.reload();
                        });
                    } else {
                        // Uyarı / Hata
                        Swal.fire({
                            icon: res.code === 'BORC_VAR' ? 'warning' : 'error',
                            title: res.code === 'BORC_VAR' ? 'Borç Var' : 'Hata',
                            html: res.msg || 'İşlem tamamlanamadı.'
                        });
                    }
                })
                .fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Hata', text: 'Sunucuya ulaşılamadı.' });
                })
                .always(function () {
                    $('#btnDeleteConfirm').prop('disabled', false).text('Evet, Pasife Al');
                });
        });

    })(jQuery);
</script>


</body>

</html>