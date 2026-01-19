<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$db = new Ydil();

$pageTitle = "Ödemesi Geciken Öğrenciler";
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];

require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';

// SQL Sorgusu: Gecikmiş taksitleri bul ve öğrenci/şube ile birleştir
// t.odendi_tutar < t.tutar  -> Tamamı ödenmemiş
// t.vade_tarihi < CURDATE() -> Vadesi geçmiş
$sql = "
SELECT 
    sube.sube_adi,
    sube.sube_id,
    o.ogrenci_id,
    o.ogrenci_numara,
    o.ogrenci_adi,
    o.ogrenci_soyadi,
    o.ogrenci_tel,
    o.veli_tel,
    COUNT(t.taksit_id) as geciken_taksit_sayisi,
    SUM(t.tutar - t.odendi_tutar) as toplam_geciken_tutar,
    MIN(t.vade_tarihi) as en_eski_vade
FROM taksit1 t
JOIN sozlesme1 s ON s.sozlesme_id = t.sozlesme_id
JOIN ogrenci1 o ON o.ogrenci_id = s.ogrenci_id
LEFT JOIN sube ON sube.sube_id = o.sube_id
WHERE t.vade_tarihi < CURDATE()
  AND t.odendi_tutar < t.tutar
  AND o.aktif = 1
GROUP BY sube.sube_id, sube.sube_adi, o.ogrenci_id, o.ogrenci_numara, o.ogrenci_adi, o.ogrenci_soyadi
ORDER BY sube.sube_adi ASC, toplam_geciken_tutar DESC
";

// Verileri çek
$rows = $db->get($sql);

// Verileri PHP tarafında şubeye göre grupla
$groupedData = [];
if ($rows) {
    foreach ($rows as $row) {
        $subeAdi = $row['sube_adi'] ?: 'Şubesiz / Diğer';
        if (!isset($groupedData[$subeAdi])) {
            $groupedData[$subeAdi] = [];
        }
        $groupedData[$subeAdi][] = $row;
    }
}

function money_tr($v)
{
    return number_format((float) $v, 2, ',', '.') . ' ₺';
}
?>

<div class="page-wrapper">
    <div class="content">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Ödemesi Geciken Öğrenciler</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gecikmiş Ödemeler</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <?php
                    $isFirst = true;
                    foreach ($groupedData as $subeAdi => $students):
                        $slug = 'tab-' . md5($subeAdi);
                        $activeClass = $isFirst ? 'active' : '';
                        ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $activeClass ?>" id="<?= $slug ?>-tab" data-bs-toggle="tab"
                                data-bs-target="#<?= $slug ?>" type="button" role="tab" aria-controls="<?= $slug ?>"
                                aria-selected="<?= $isFirst ? 'true' : 'false' ?>">
                                <?= htmlspecialchars($subeAdi) ?>
                                <span class="badge bg-danger ms-2 rounded-pill">
                                    <?= count($students) ?>
                                </span>
                            </button>
                        </li>
                        <?php
                        $isFirst = false;
                    endforeach;
                    ?>
                    <?php if (empty($groupedData)): ?>
                        <li class="nav-item"><a class="nav-link active">Kayıt Bulunamadı</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="myTabContent">
                    <?php
                    $isFirst = true;
                    foreach ($groupedData as $subeAdi => $students):
                        $slug = 'tab-' . md5($subeAdi);
                        $activeClass = $isFirst ? 'show active' : '';

                        // Şube toplamı
                        $subeToplamTutar = 0;
                        foreach ($students as $st)
                            $subeToplamTutar += $st['toplam_geciken_tutar'];
                        ?>
                        <div class="tab-pane fade <?= $activeClass ?>" id="<?= $slug ?>" role="tabpanel"
                            aria-labelledby="<?= $slug ?>-tab">

                            <div class="alert alert-soft-danger d-flex align-items-center mb-3">
                                <i class="ti ti-info-circle fs-22 me-2"></i>
                                <div>
                                    <strong>
                                        <?= htmlspecialchars($subeAdi) ?>
                                    </strong> şubesi için toplam
                                    <strong>
                                        <?= money_tr($subeToplamTutar) ?>
                                    </strong> gecikmiş ödeme bulunmaktadır.
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover datatable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Öğrenci No</th>
                                            <th>Ad Soyad</th>
                                            <th>Veli Tel</th>
                                            <th class="text-center">Geciken Taksit</th>
                                            <th>En Eski Vade</th>
                                            <th class="text-end">Toplam Gecikme</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $st): ?>
                                            <tr>
                                                <td><a href="ogrenci-detay.php?id=<?= $st['ogrenci_numara'] ?>"
                                                        class="fw-bold link-primary">
                                                        <?= htmlspecialchars($st['ogrenci_numara']) ?>
                                                    </a></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="avatar avatar-sm me-2 bg-primary-transparent rounded-circle">
                                                            <?= mb_substr($st['ogrenci_adi'], 0, 1) . mb_substr($st['ogrenci_soyadi'], 0, 1) ?>
                                                        </span>
                                                        <?= htmlspecialchars($st['ogrenci_adi'] . ' ' . $st['ogrenci_soyadi']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($st['veli_tel'] ?? '-') ?>
                                                </td>
                                                <td class="text-center"><span class="badge bg-warning-transparent fs-12">
                                                        <?= $st['geciken_taksit_sayisi'] ?> Adet
                                                    </span></td>
                                                <td>
                                                    <?= date('d.m.Y', strtotime($st['en_eski_vade'])) ?>
                                                </td>
                                                <td class="text-end fw-bold text-danger">
                                                    <?= money_tr($st['toplam_geciken_tutar']) ?>
                                                </td>
                                                <td>
                                                    <a href="ogrenci-detay.php?id=<?= $st['ogrenci_numara'] ?>"
                                                        class="btn btn-sm btn-light">
                                                        <i class="ti ti-eye"></i> İncele
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php
                        $isFirst = false;
                    endforeach;
                    ?>

                    <?php if (empty($groupedData)): ?>
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl bg-success-transparent mb-3">
                                <i class="ti ti-check fs-36"></i>
                            </div>
                            <h3>Harika!</h3>
                            <p class="text-muted">Şu anda sistemde ödemesi geciken öğrenci bulunmuyor.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/feather.min.js"></script>
<script src="assets/js/jquery.slimscroll.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/js/script.js"></script>

</body>

</html>