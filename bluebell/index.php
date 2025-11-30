<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';

$db = new Ydil();

/* ------------------ Şube bazlı öğrenci listesi ------------------ */
$sube_id = (int)($_SESSION['sube_id'] ?? 0);

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
WHERE o.sube_id = :sube_id
ORDER BY o.ogrenci_numara DESC";

$ogrenciler = $db->get($sql, [':sube_id' => $sube_id]);

/* ------------------ Yardımcı fonksiyonlar ------------------ */
if (!function_exists('h')) {
    function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('formatDateTRSafe')) {
    function formatDateTRSafe($d){
        if (function_exists('formatDateTR')) return formatDateTR($d);
        if (!$d || $d==='0000-00-00') return '-';
        $ts = strtotime($d);
        return $ts ? date('d.m.Y', $ts) : '-';
    }
}

/* ------------------ Sayaçlar (TOPLAM / AKTİF / PASİF) ------------------ */
$toplamOgrenci = is_array($ogrenciler) ? count($ogrenciler) : 0;
$aktifSay = 0;
$pasifSay = 0;

if (!empty($ogrenciler)) {
    foreach ($ogrenciler as $o) {
        $durum = mb_strtolower(trim((string)($o['durum'] ?? '')), 'UTF-8');
        if ($durum === 'aktif') {
            $aktifSay++;
        } else {
            $pasifSay++;
        }
    }
}

/* ------------------ Sayfa başlık & stil ------------------ */
$pageTitle = "Öğrenci Listesi";
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>

<div class="page-wrapper">
    <div class="content">

        <!-- ÜST BAŞLIK -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Öğrenci Listesi</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="anasayfa.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Öğrenci Listesi</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <a href="ogrenci-ekle.php" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-square-rounded-plus me-2"></i>Öğrenci Ekle
                    </a>
                </div>
            </div>
        </div>

        <!-- ÖĞRENCİ ÖZET KARTI (TOPLAM / AKTİF / PASİF) -->
        <div class="row mb-4">
            <div class="col-xxl-3 col-sm-6 d-flex">
                <div class="card flex-fill animate-card border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xl bg-danger-transparent me-2 p-1">
                                <img src="assets/img/icons/student.svg" alt="img">
                            </div>
                            <div class="overflow-hidden flex-fill">
                                <div class="d-flex align-items-center justify-content-between">
                                    <!-- TOPLAM ÖĞRENCİ DİNAMİK -->
                                    <h2 class="counter">
                                        <?= (int)$toplamOgrenci ?>
                                    </h2>
                                    <!-- İstersen dinamik bir yüzde de koyabilirsin; şimdilik boş bırakıldı -->
                                    <span class="badge bg-danger">
                                        <?= $toplamOgrenci > 0 ? '100%' : '0%' ?>
                                    </span>
                                </div>
                                <p>Toplam Öğrenci</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
                            <p class="mb-0">
                                Aktif :
                                <span class="text-dark fw-semibold">
                                    <?= (int)$aktifSay ?>
                                </span>
                            </p>
                            <span class="text-light">|</span>
                            <p>
                                Aktif Değil :
                                <span class="text-dark fw-semibold">
                                    <?= (int)$pasifSay ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ÖĞRENCİ LİSTESİ TABLOSU (DataTable) -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
                <h4 class="mb-3">Öğrenci Listesi</h4>
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
                            <th>Cinsiyet</th>
                            <th class="no-sort" style="min-width:180px;">İşlemler</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($ogrenciler)): ?>
                            <?php foreach ($ogrenciler as $o):
                                $numara    = h($o['ogrenci_no']   ?? '');
                                $tc        = h($o['ogrenci_tc']   ?? '');
                                $adSoyad   = h($o['ad_soyad']     ?? '');
                                $telefon   = h($o['telefon']      ?? '');
                                $eposta    = h($o['email']        ?? ($o['eposta'] ?? ''));
                                $dogumRaw  = $o['dogum_tarihi']   ?? null;
                                $dogum     = h(formatDateTRSafe($dogumRaw));
                                $cinsiyetV = trim((string)($o['cinsiyet'] ?? ''));
                                $cinsiyet  = $cinsiyetV==='' ? '-' : (
                                    ($cinsiyetV==='1' || $cinsiyetV==='Erkek') ? 'Erkek' :
                                        (($cinsiyetV==='0' || $cinsiyetV==='Kız') ? 'Kız' : h($cinsiyetV))
                                );
                                $durumV    = trim((string)($o['durum'] ?? 'Belirsiz'));
                                $isAktif   = (mb_strtolower($durumV,'UTF-8') === 'aktif');
                                $badgeCls  = $isAktif
                                    ? 'badge badge-soft-success d-inline-flex align-items-center'
                                    : 'badge badge-soft-secondary d-inline-flex align-items-center';

                                $avatarUrl = 'https://ui-avatars.com/api/?name='.urlencode($adSoyad ?: $numara).'&size=64&background=DDD&color=333&bold=true';
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
                                                <a href="ogrenci-detay.php?id=<?= $numara ?>" class="text-dark fw-semibold">
                                                    <?= $adSoyad ?: '-' ?>
                                                </a>
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

                                    <td><?= h($cinsiyet) ?></td>

                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="<?= $telefon ? 'tel:'.preg_replace('/\s+/','', $telefon) : '#' ?>"
                                               class="btn btn-outline-light bg-white btn-icon d-flex align-items-center justify-content-center rounded-circle p-0 me-2"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="<?= $telefon ? h($telefon) : 'Telefon numarası yok' ?>">
                                                <i class="ti ti-phone<?= $telefon ? '' : ' text-muted' ?>"></i>
                                            </a>

                                            <a href="<?= $eposta ? 'mailto:'.h($eposta) : '#' ?>"
                                               class="btn btn-outline-light bg-white btn-icon d-flex align-items-center justify-content-center rounded-circle p-0 me-2"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="<?= $eposta ? h($eposta) : 'E-posta adresi yok' ?>">
                                                <i class="ti ti-mail<?= $eposta ? '' : ' text-muted' ?>"></i>
                                            </a>

                                            <div class="dropdown">
                                                <a href="#" class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
                                                   data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ti ti-dots-vertical fs-14"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end p-2">
                                                    <li>
                                                        <a class="dropdown-item rounded-1" href="ogrenci-detay.php?id=<?= $numara ?>">
                                                            <i class="ti ti-user-circle me-2"></i>Öğrenci Sayfası
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-1" href="ogrenci-duzenle.php?id=<?= $numara ?>">
                                                            <i class="ti ti-edit-circle me-2"></i>Öğrenci Düzenle
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-1" href="sozlesme-olustur.php?id=<?= $numara ?>">
                                                            <i class="ti ti-file-text me-2"></i>Sözleşme Oluştur
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted">Kayıt bulunamadı.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- JS -->
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

<script>
    document.addEventListener('DOMContentLoaded', function(){
        // Bootstrap tooltips
        if (window.bootstrap) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){
                new bootstrap.Tooltip(el);
            });
        }

        // Select-all
        const selectAll = document.getElementById('select-all');
        const checks = document.querySelectorAll('.row-check');
        if (selectAll) {
            selectAll.addEventListener('change', function(){
                checks.forEach(ch => ch.checked = selectAll.checked);
            });
        }

        // DataTables
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
    });
</script>

</body>
</html>
