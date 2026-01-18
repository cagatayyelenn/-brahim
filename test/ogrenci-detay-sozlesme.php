<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

if (!isset($_GET['id']) || $_GET['id'] === '') {
    header("Location: ogrenciler.php");
    exit;
}

$ogr_no = preg_replace('/\D+/', '', (string) $_GET['id']); // sadece rakam
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// Öğrenci + şube doğrulaması
$ogr = $db->gets("
    SELECT 
      o.ogrenci_id, o.ogrenci_numara, o.ogrenci_adi, o.ogrenci_soyadi
    FROM ogrenci1 o
    WHERE o.ogrenci_numara = :no AND o.sube_id = :sube
    LIMIT 1
", [':no' => $ogr_no, ':sube' => $sube_id]);

if (!$ogr) {
    header("Location: ogrenciler.php");
    exit;
}

$ogrenci_id = (int) $ogr['ogrenci_id'];


$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// Seçili değerleri (form yeniden dönerse) tut
$selYontem = (int) ($_POST['yontem_id'] ?? 0);
$selKasa = (int) ($_POST['kasa_id'] ?? 0);

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



$sql1 = "
SELECT 
  o.ogrenci_id,
  o.ogrenci_numara,
  CONCAT(o.ogrenci_adi,' ',o.ogrenci_soyadi) AS ogrenci_adsoyad,

  s.sozlesme_id,
  s.sozlesme_no,
  s.net_ucret,
  s.taksit_sayisi,
  s.odeme_tipi,
  s.sozlesme_tarihi,
  s.baslangic_tarihi,
  s.bitis_tarihi,

  t.taksit_id,
  t.sira_no,
  t.vade_tarihi,
  t.tutar          AS taksit_tutar,
  t.odendi_tutar   AS taksit_odenen,
  t.durum          AS taksit_durum
FROM ogrenci1 o
LEFT JOIN sozlesme1 s ON s.ogrenci_id = o.ogrenci_id
LEFT JOIN taksit1   t ON t.sozlesme_id = s.sozlesme_id
WHERE o.ogrenci_numara = :no AND o.sube_id = :sube
ORDER BY s.sozlesme_tarihi DESC, s.sozlesme_id DESC, t.vade_tarihi ASC
";

$rows = $db->get($sql1, [':no' => $ogr_no, ':sube' => $sube_id]);
$today = date('Y-m-d');
$contracts = []; // sozlesme_id => {header, taksitler, özetler}

foreach ($rows as $r) {
    $sid = $r['sozlesme_id'];

    // Sözleşmesi yoksa bile (NULL) satır gelebilir; atla
    if (!$sid) {
        continue;
    }

    if (!isset($contracts[$sid])) {
        $contracts[$sid] = [
            'header' => [
                'sozlesme_id' => (int) $sid,
                'sozlesme_no' => $r['sozlesme_no'],
                'net_ucret' => (float) $r['net_ucret'],
                'taksit_sayisi' => (int) $r['taksit_sayisi'],
                'odeme_tipi' => $r['odeme_tipi'],
                'sozlesme_tarihi' => $r['sozlesme_tarihi'],
                'baslangic' => $r['baslangic_tarihi'],
                'bitis' => $r['bitis_tarihi'],
            ],
            'taksitler' => [],
            'ozet' => [
                'taksit_say' => 0,
                'odenen_adet' => 0,
                'kalan_adet' => 0,
                'gecikmis_adet' => 0,
                'odenen_tutar' => 0.0,
                'gecikmis_tutar' => 0.0,
            ]
        ];
    }

    if ($r['taksit_id']) {
        $paidFull = ((float) $r['taksit_odenen'] >= (float) $r['taksit_tutar']) || ($r['taksit_durum'] === 'Odendi');
        $late = (!$paidFull && !empty($r['vade_tarihi']) && $r['vade_tarihi'] < $today);

        $contracts[$sid]['taksitler'][] = [
            'taksit_id' => (int) $r['taksit_id'],
            'sira_no' => (int) $r['sira_no'],
            'vade_tarihi' => $r['vade_tarihi'],
            'tutar' => (float) $r['taksit_tutar'],
            'odenen' => (float) $r['taksit_odenen'],
            'durum' => $r['taksit_durum'],
            'paidFull' => $paidFull,
            'late' => $late,
            'eksik' => max(0, (float) $r['taksit_tutar'] - (float) $r['taksit_odenen']),
        ];

        // Özet
        $contracts[$sid]['ozet']['taksit_say']++;
        $contracts[$sid]['ozet']['odenen_tutar'] += (float) $r['taksit_odenen'];

        if ($paidFull) {
            $contracts[$sid]['ozet']['odenen_adet']++;
        } else {
            if ($late)
                $contracts[$sid]['ozet']['gecikmis_adet']++;
            else
                $contracts[$sid]['ozet']['kalan_adet']++;
            $contracts[$sid]['ozet']['gecikmis_tutar'] += $late ? max(0, (float) $r['taksit_tutar'] - (float) $r['taksit_odenen']) : 0;
        }
    }
}

$pageTitle = $ogr['ogrenci_adi'] . " " . $ogr['ogrenci_soyadi'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
$page_styles[] = ['href' => 'assets/css/print-sozlesme.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
require_once 'ogrenci-detay-ortak.php';

?>
<div class="col-xxl-9 col-xl-8">
    <div class="row">
        <div class="col-md-12">

            <ul class="nav nav-tabs nav-tabs-bottom mb-4">
                <!--
                <li><a href="ogrenci-detay.php?id=<?= htmlspecialchars($ogr_no) ?>" class="nav-link "><i class="ti ti-school me-2"></i>Genel Bakış</a></li>
                <li><a href="ogrenci-detay.php?id=<?= htmlspecialchars($ogr_no) ?>" class="nav-link"><i class="ti ti-table-options me-2"></i>Time Table</a></li>
                <li><a href="ogrenci-detay.php?id=<?= htmlspecialchars($ogr_no) ?>" class="nav-link"><i class="ti ti-calendar-due me-2"></i>Leave & Attendance</a></li>
                <li><a href="ogrenci-detay.php?id=<?= htmlspecialchars($ogr_no) ?>" class="nav-link"><i class="ti ti-report-money me-2"></i>Fees</a></li>
                <li><a href="ogrenci-detay.php?id=<?= htmlspecialchars($ogr_no) ?>" class="nav-link"><i class="ti ti-books me-2"></i>Library</a></li>-->
                <li><a href="ogrenci-detay-sozlesme.php?id=<?= htmlspecialchars($ogr_no) ?>" class="nav-link active"><i
                            class="ti ti-bookmark-edit me-2"></i>Sözleşme Ve Taksitler</a></li>

            </ul>
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
                    <h4 class="mb-3">Sözleşmeler ve Taksitler</h4>
                </div>
                <div class="card-body">

                    <div class="accordions-items-seperate" id="accordionSoz">
                        <?php if (empty($contracts)): ?>
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="ti ti-info-circle me-2"></i>
                                Bu öğrenciye ait sözleşme bulunamadı.
                                <a class="btn btn-primary btn-sm ms-auto"
                                    href="sozlesme-olustur.php?id=<?= (int) $ogr_no ?>">Sözleşme Oluştur</a>
                            </div>
                        <?php else: ?>
                            <?php $i = 0;
                            foreach ($contracts as $sid => $c):
                                $h = $c['header'];
                                $o = $c['ozet'];
                                $taksitler = $c['taksitler'];
                                $collapseId = "sz_" . $sid;
                                $toplamSozlesme = (float) ($h['net_ucret'] ?? 0);
                                $odenenTutar = (float) $o['odenen_tutar'];
                                $odenmesiGereken = max(0, $toplamSozlesme - $odenenTutar);
                                ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header d-flex align-items-center">
                                        <button class="accordion-button flex-grow-1 <?= $i === 0 ? '' : 'collapsed' ?>"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>"
                                            aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
                                            <span class="avatar avatar-sm bg-success me-2"><i class="ti ti-checks"></i></span>
                                            <?= htmlspecialchars('Sözleşme #' . $sid) ?>
                                            <?php if (!empty($h['sozlesme_tarihi'])): ?>
                                                <span class="ms-2 text-muted">(Tarih:
                                                    <?= date('d.m.Y', strtotime($h['sozlesme_tarihi'])) ?>)</span>
                                            <?php endif; ?>
                                        </button>
                                        <?php
                                        $odemeVar = false;
                                        foreach ($taksitler as $t) {
                                            if ((float) $t['odenen'] > 0) {
                                                $odemeVar = true;
                                            }
                                        }
                                        if ($odemeVar == 0) {
                                            echo '<a href="javascript:void(0);" 
                                               class="btn btn-outline-danger btn-sm flex-shrink-0 sozlesmeSilBtn"
                                               data-id="' . $sid . '"
                                               <i class="ti ti-trash"></i>Sil
                                            </a>';
                                        }
                                        ?>
                                        <a href="sozlesme-guncelleme.php?id=<?= (int) $sid ?>"
                                            class="btn btn-outline-primary btn-sm  flex-shrink-0">
                                            <i class="ti ti-file-text"></i> Düzenle
                                        </a>

                                        <a href="sozlesme-belge.php?id=<?= (int) $sid ?>"
                                            class="btn btn-outline-primary btn-sm  flex-shrink-0" style="margin-right: 16px;"
                                            target="_blank" rel="noopener">
                                            <i class="ti ti-file-text"></i> Sözleşme Belgesi
                                        </a>
                                        <a href="sozlesme-senet.php?id=<?= (int) $sid ?>"
                                            class="btn btn-outline-dark btn-sm flex-shrink-0" target="_blank" rel="noopener">
                                            <i class="ti ti-receipt"></i> Senet Yazdır
                                        </a>
                                    </h2>

                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                        data-bs-parent="#accordionSoz">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Taksit Tarihi</th>
                                                            <th>Taksit Tutarı</th>
                                                            <th>Ödenen</th>
                                                            <th>Durum</th>
                                                            <th class="text-end">İşlem</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if ($taksitler): ?>
                                                            <?php foreach ($taksitler as $idx => $t):
                                                                $paid = $t['paidFull'];
                                                                $gecik = $t['late'];
                                                                $due = $t['eksik'];
                                                                $badge = $paid ? 'badge-soft-success' : ($gecik ? 'badge-soft-danger' : 'badge-soft-warning');
                                                                $label = $paid ? 'Ödendi' : ($gecik ? 'Gecikmiş' : 'Bekliyor');
                                                                ?>
                                                                <tr>
                                                                    <td><?= $idx + 1 ?></td>
                                                                    <td><?= htmlspecialchars(date('d.m.Y', strtotime($t['vade_tarihi']))) ?>
                                                                    </td>
                                                                    <td><?= number_format((float) $t['tutar'], 2, ',', '.') ?> ₺</td>
                                                                    <td><?= number_format((float) $t['odenen'], 2, ',', '.') ?> ₺</td>
                                                                    <td> <span
                                                                            class="badge <?= $badge ?> d-inline-flex align-items-center">
                                                                            <i class="ti ti-circle-filled fs-5 me-1"></i><?= $label ?>
                                                                        </span>
                                                                        <?php if (!$paid && $due > 0): ?>
                                                                            <small class="text-danger ms-1">Eksik:
                                                                                <?= number_format($due, 2, ',', '.') ?> ₺</small>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <?php if ($paid): ?>
                                                                            <a class="btn btn-light btn-sm"
                                                                                href="odeme-belge.php?taksit=<?= (int) $t['taksit_id'] ?>"
                                                                                target="_blank">Belge Göster</a>
                                                                        <?php else: ?>
                                                                            <button class="btn btn-primary btn-sm btn-ode"
                                                                                data-taksit-id="<?= (int) $t['taksit_id'] ?>"
                                                                                data-ogrenci-id="<?= (int) $ogrenci_id ?>"
                                                                                data-satis-id="<?= (int) $sid ?>"
                                                                                data-tutar="<?= htmlspecialchars($due) ?>">
                                                                                Öde
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="6" class="text-muted text-center">Bu sözleşmeye ait
                                                                    taksit bulunamadı.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>

                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="6" class="bg-dark text-white">
                                                                <div
                                                                    class="d-flex flex-wrap align-items-center justify-content-between">
                                                                    <div>Toplam Sözleşme:
                                                                        <strong><?= number_format($toplamSozlesme, 2, ',', '.') ?>
                                                                            ₺</strong><span class="mx-2">|</span></div>
                                                                    <div>Ödenen:
                                                                        <strong><?= number_format($odenenTutar, 2, ',', '.') ?>
                                                                            ₺</strong><span class="mx-2">|</span></div>
                                                                    <div>Ödenmesi Gereken:
                                                                        <strong><?= number_format($odenmesiGereken, 2, ',', '.') ?>
                                                                            ₺</strong><span class="mx-2">|</span></div>
                                                                    <div>Gecikmiş Tutar: <strong
                                                                            class="text-warning"><?= number_format($o['gecikmis_tutar'], 2, ',', '.') ?>
                                                                            ₺</strong></div>
                                                                    <div class="ms-auto">
                                                                        <span class="badge bg-secondary me-1">Taksit:
                                                                            <?= (int) $o['taksit_say'] ?></span>
                                                                        <span class="badge bg-success me-1">Ödenen:
                                                                            <?= (int) $o['odenen_adet'] ?></span>
                                                                        <span class="badge bg-warning me-1">Kalan:
                                                                            <?= (int) $o['kalan_adet'] ?></span>
                                                                        <span class="badge bg-danger">Gecikmiş:
                                                                            <?= (int) $o['gecikmis_adet'] ?></span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $i++; endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


<!-- Login Details -->
<div class="modal fade" id="login_detail">
    <div class="modal-dialog modal-dialog-centered  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Login Details</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="student-detail-info">
                    <span class="student-img"><img src="assets/img/students/student-01.jpg" alt="Img"></span>
                    <div class="name-info">
                        <h6>Janet <span>III, A</span></h6>
                    </div>
                </div>
                <div class="table-responsive custom-table no-datatable_length">
                    <table class="table datanew">
                        <thead class="thead-light">
                            <tr>
                                <th>User Type</th>
                                <th>User Name</th>
                                <th>Password </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Parent</td>
                                <td>parent53</td>
                                <td>parent@53</td>
                            </tr>
                            <tr>
                                <td>Student</td>
                                <td>student20</td>
                                <td>stdt@53</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="student-result.html#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
            </div>
        </div>
    </div>
</div>
<!-- /Login Details -->

<div class="modal fade" id="add_fees_collect" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <h4 class="modal-title">Sözleşme Taksit Ödeme </h4>
                    <span class="badge badge-sm bg-primary ms-2" id="badgeSozNo"> </span>
                </div>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="formTaksitTahsil" action="#" method="post" autocomplete="off">
                <input type="hidden" id="hidTaksitId" name="taksit_id">
                <input type="hidden" id="hidOgrenciId" name="ogrenci_id">
                <input type="hidden" id="hidSozlesmeId" name="sozlesme_id">
                <input type="hidden" id="hidTaksitVade" name="taksit_vade">
                <input type="hidden" id="hidTaksitTutarRaw" name="taksit_tutar_raw">
                <div class="modal-body">
                    <!-- ilerde dolduracağız
                            <div class="bg-light-300 p-3 pb-0 rounded mb-4">
                                <div class="row align-items-center">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <a href="student-details.html" class="avatar avatar-md me-2">
                                                <img src="assets/img/students/student-01.jpg" alt="img">
                                            </a>
                                            <a href="student-details.html" class="d-flex flex-column"><span class="text-dark">Janet</span>III, A</a>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <span class="fs-12 mb-1">Total Outstanding</span>
                                            <p class="text-dark">2000</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                            <span class="fs-12 mb-1">Last Date</span>
                                            <p class="text-dark">25 May 2024</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="mb-3">
                                                    <span class="badge badge-soft-danger"><i
                                                                class="ti ti-circle-filled me-2"></i>Unpaid</span>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label text-warning text-muted mb-1">Toplam Tutar</label>
                                <input type="text" class="form-control text-warning text-end" id="ozetToplam"
                                    value="50.000,00 TL" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label text-success text-muted mb-1">Alınan Peşinat</label>
                                <input type="text" class="form-control text-success text-end" id="ozetPesinat"
                                    value="20.000,00 TL" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label text-danger mb-1">Kalan Borç Tutarı</label>
                                <input type="text" class="form-control text-end text-danger fw-semibold" id="ozetKalan"
                                    value="30.000,00 TL" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label text-info mb-1">Taksit Tutarı</label>
                                <input type="text" class="form-control text-info text-end" id="taksitTutarGoster"
                                    value="3.000,00 TL" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Ödeme Türü</label>
                                <select class="form-select" name="yontem_id" required>
                                    <option value="">Ödeme Türü Seçiniz</option>
                                    <?php foreach ($odemeYontemleri as $y): ?>
                                        <option value="<?= (int) $y['yontem_id'] ?>">
                                            <?= htmlspecialchars($y['yontem_adi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Kasa</label>
                                <select class="form-select" name="kasa_id" required>
                                    <option value="">Kasa Türü Seçiniz</option>
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
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Ödeme Tarihi</label>
                                <div class="input-icon">
                                    <input type="text" class="form-control datetimepicker" name="odeme_tarihi" value="<?= date('d.m.Y') ?>">
                                    <span class="input-icon-addon"><i class="ti ti-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3"> </div>
                        </div>
                        <div id="alertFazla" class="alert alert-outline-danger alert-dismissible fade show d-none">
                            Girmiş Olduğunuz Tutar Kalan Ödemenizden Fazladır.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>
                        <div class="bg-light-300 p-3 pb-0 rounded pb-4">
                            <div class="row align-items-center">
                                <div class="col-lg-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="status-title">
                                            <h5>Durum</h5>
                                            <p>Farklı Tutar Ödemek İstiyorum</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="chkFarkli">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3"> </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-0">
                                        <label class="form-label">Tutar Giriniz</label>
                                        <input type="text" class="form-control text-end" id="inpOdenecekTutar"
                                            placeholder="0,00 TL" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">Vazgeç</a>
                    <button type="submit" class="btn btn-primary">Tahsil Et</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="sozlesmeSilModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Sözleşmeyi Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Bu sözleşmeyi silmek istediğinize emin misiniz?<br>
                <strong>Bu işlem geri alınamaz.</strong>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button class="btn btn-danger" id="sozlesmeSilOnayBtn">Evet, Sil</button>
            </div>

        </div>
    </div>
</div>

</div>



<script src="assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<script src="assets/js/moment.js" type="text/javascript"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<script src="assets/js/feather.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="assets/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
<script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js" type="text/javascript"></script>
<script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js" type="text/javascript"></script>
<script src="assets/js/script.js" type="text/javascript"></script>

<script>
    let silinecekID = 0;

    $(document).on("click", ".sozlesmeSilBtn", function () {
        silinecekID = $(this).data("id");
        $("#sozlesmeSilModal").modal("show");
    });

    $("#sozlesmeSilOnayBtn").on("click", function () {
        $.post("sozlesme-ajax/sozlesme-sil.php", { id: silinecekID }, function (res) {
            if (res.ok) {

                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı',
                    text: res.msg,
                    confirmButtonText: 'Tamam'
                }).then(() => {
                    location.reload();
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: res.msg,
                    confirmButtonText: 'Kapat'
                });
            }
        }, "json");
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('add_fees_collect');
        const bsModal = () => new bootstrap.Modal(modalEl);
        const badgeSozNo = document.getElementById('badgeSozNo');
        const inpToplam = document.getElementById('ozetToplam');
        const inpPesinat = document.getElementById('ozetPesinat');
        const inpKalan = document.getElementById('ozetKalan');
        const inpTaksit = document.getElementById('taksitTutarGoster');
        const chkFarkli = document.getElementById('chkFarkli');
        const inpCustom = document.getElementById('inpOdenecekTutar');
        const alertBox = document.getElementById('alertFazla');

        // TL yardımcıları
        const tlFmt = n => (Number(n || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
        const tlParse = s => {
            if (!s) return 0;
            s = (s + '').replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
            const n = parseFloat(s);
            return isNaN(n) ? 0 : n;
        };

        function hideAlert() { alertBox && alertBox.classList.add('d-none'); }
        function showAlert() { alertBox && alertBox.classList.remove('d-none'); }

        // "Farklı tutar" toggle
        chkFarkli?.addEventListener('change', () => {
            hideAlert();
            if (chkFarkli.checked) {
                // taksit alanını 0 göster, custom alanı aktif et
                inpTaksit.value = tlFmt(0);
                inpCustom.removeAttribute('disabled');
                inpCustom.value = '';
                inpCustom.focus();
            } else {
                // orijinal taksit kalanını geri yükle
                const raw = parseFloat(modalEl.dataset.taksitKalanRaw || '0');
                inpCustom.value = '';
                inpCustom.setAttribute('disabled', 'disabled');
                inpTaksit.value = tlFmt(raw);
            }
        });

        // Custom tutar validasyonu
        inpCustom?.addEventListener('input', () => {
            const kalanBorc = parseFloat(modalEl.dataset.kalanBorcRaw || '0');
            const val = tlParse(inpCustom.value);
            if (val > kalanBorc) showAlert(); else hideAlert();
        });

        // ÖDE butonu -> özet çek + modal doldur + aç
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-ode');
            if (!btn) return;

            const taksitId = btn.dataset.taksitId;
            const ogrenciId = btn.dataset.ogrenciId || '';
            const sozId = btn.dataset.sozlesmeId || btn.dataset.satisId; // sizde data-satis-id geliyor
            if (!taksitId || !sozId) {
                console.warn('Eksik buton datası (taksitId/sozlesmeId).');
                return;
            }

            // Modalı resetle
            hideAlert();
            if (chkFarkli) chkFarkli.checked = false;
            if (inpCustom) {
                inpCustom.value = '';
                inpCustom.setAttribute('disabled', 'disabled');
            }
            if (badgeSozNo) badgeSozNo.textContent = '';
            inpToplam && (inpToplam.value = '');
            inpPesinat && (inpPesinat.value = '');
            inpKalan && (inpKalan.value = '');
            inpTaksit && (inpTaksit.value = '');

            // ÖZETİ ÇEK
            try {
                const url = `sozlesme-ajax/taksit-ozet.php?taksit_id=${encodeURIComponent(taksitId)}&sozlesme_id=${encodeURIComponent(sozId)}`;
                const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const j = await r.json();

                if (!j.ok) {
                    alert(j.msg || 'Özet getirilemedi.');
                    return;
                }

                // Verileri modal alanlarına bas
                const sozNo = j.sozlesme?.no || '';
                const toplamTaksit = j.ozet?.toplam_taksit ?? 0;  // sözleşme toplamı (taksit1 toplamı)
                const pesinat = j.ozet?.pesinat ?? 0;        // bilgi amaçlı
                const kalanBorc = j.ozet?.kalan_borc ?? 0;     // sözleşme kalan borcu
                const taksitKalan = j.taksit?.kalan ?? 0;        // seçili taksitte kalan ödeme

                if (badgeSozNo) badgeSozNo.textContent = sozNo;
                inpToplam && (inpToplam.value = tlFmt(toplamTaksit));
                inpPesinat && (inpPesinat.value = tlFmt(pesinat));
                inpKalan && (inpKalan.value = tlFmt(kalanBorc));
                inpTaksit && (inpTaksit.value = tlFmt(taksitKalan));

                // Ham değerleri modal üzerinde sakla (sonraki POST adımı için lazım)
                modalEl.dataset.sozlesmeId = String(j.sozlesme?.id || sozId);
                modalEl.dataset.sozlesmeNo = String(sozNo || '');
                modalEl.dataset.taksitId = String(j.taksit?.id || taksitId);
                modalEl.dataset.ogrenciId = String(ogrenciId || '');
                modalEl.dataset.taksitKalanRaw = String(taksitKalan);
                modalEl.dataset.kalanBorcRaw = String(kalanBorc);
                modalEl.dataset.taksitVade = String(j.taksit?.vade || '');

                // Modalı aç
                bsModal().show();
            } catch (err) {
                console.error(err);
                alert('Özet alınırken bir sorun oluştu.');
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('add_fees_collect');
        const chkFarkli = document.getElementById('chkFarkli');
        const inpCustom = document.getElementById('inpOdenecekTutar');
        const inpTaksit = document.getElementById('taksitTutarGoster');
        const inpKalan = document.getElementById('ozetKalan');
        const alertBox = document.getElementById('alertFazla');

        // TL yardımcıları
        const tlFmt = n => (Number(n || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
        const tlParse = s => {
            if (!s) return 0;
            s = String(s).replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
            const n = parseFloat(s);
            return isNaN(n) ? 0 : n;
        };

        const showAlert = () => alertBox && alertBox.classList.remove('d-none');
        const hideAlert = () => alertBox && alertBox.classList.add('d-none');

        // Toggle davranışı
        chkFarkli?.addEventListener('change', () => {
            hideAlert();
            if (chkFarkli.checked) {
                // Farklı tutar: taksit sıfır gösterilsin, custom alan açılır
                inpTaksit && (inpTaksit.value = tlFmt(0));
                inpCustom && (inpCustom.removeAttribute('disabled'), inpCustom.value = '', inpCustom.focus());
            } else {
                // Varsayılan taksit kalanını geri yükle
                const raw = parseFloat(modalEl?.dataset?.taksitKalanRaw || '0');
                inpCustom && (inpCustom.value = '', inpCustom.setAttribute('disabled', 'disabled'));
                inpTaksit && (inpTaksit.value = tlFmt(raw));
            }
        });

        // Kullanıcı custom tutar yazarken:
        //  - TL maskesi (hafif)
        //  - Kalan borç aşıldı mı kontrol
        inpCustom?.addEventListener('input', (e) => {
            // Basit mask: sadece rakam ve ayırıcıları kabul et
            let v = e.target.value;
            // virgül sayısı > 1 olmasın
            const parts = v.split(',');
            if (parts.length > 2) {
                v = parts.slice(0, 2).join(',');
            }
            // fazla karakterleri temizle (rakam, nokta, virgül)
            v = v.replace(/[^\d,\.]/g, '');

            // aşıma bak
            const kalanBorc = modalEl?.dataset?.kalanBorcRaw
                ? parseFloat(modalEl.dataset.kalanBorcRaw)
                : tlParse(inpKalan?.value || '0');

            const valNum = tlParse(v);
            if (valNum > kalanBorc) showAlert(); else hideAlert();

            // kutuda formatlı göstermeyelim; kullanıcı rahat yazsın
            e.target.value = v;
        });

        // Modal her açılışında mevcut durumu temizle (opsiyonel ama faydalı)
        modalEl?.addEventListener('show.bs.modal', () => {
            hideAlert();
            if (chkFarkli) chkFarkli.checked = false;
            if (inpCustom) { inpCustom.value = ''; inpCustom.setAttribute('disabled', 'disabled'); }
            // taksit tutarını modalEl.dataset.taksitKalanRaw’dan yüklemek,
            // "özet" çekme kodunda yapılmalı (sen zaten yapıyorsun).
        });

        // Modal kapanırken de uyarıyı kapat
        modalEl?.addEventListener('hidden.bs.modal', () => hideAlert());
    });
</script>
<script>
    /* document.addEventListener('DOMContentLoaded', () => {
         const modalEl = document.getElementById('add_fees_collect');
         const form    = document.getElementById('formTaksitTahsil');
         if (!modalEl || !form) return;
 
         const chkFarkli = document.getElementById('chkFarkli');
         const inpCustom = document.getElementById('inpOdenecekTutar');
         const inpKalan  = document.getElementById('ozetKalan');
         const btnSubmit = form.querySelector('button[type="submit"]');
 
         // ufak yardımcılar
         const tlParse = (s) => {
             if (!s) return 0;
             s = String(s).replace(/[^\d,.-]/g,'').replace(/\./g,'').replace(',', '.');
             const n = parseFloat(s);
             return isNaN(n) ? 0 : n;
         };
         const hasSwal = () => (typeof Swal !== 'undefined');
 
         form.addEventListener('submit', async (ev) => {
             ev.preventDefault();
 
             // dataset’ten zorunlu veriler
             const taksit_id   = parseInt(modalEl.dataset.taksitId || '0', 10);
             const sozlesme_id = parseInt(modalEl.dataset.sozlesmeId || '0', 10);
             const ogrenci_id  = parseInt(modalEl.dataset.ogrenciId || '0', 10);
             const taksit_raw  = parseFloat(modalEl.dataset.taksitKalanRaw || '0'); // seçili taksitte kalan
             const kalan_borc  = parseFloat(modalEl.dataset.kalanBorcRaw   || '0'); // sözleşme kalan borç
 
             // select’ler
             const yontem_id = parseInt(form.querySelector('[name="yontem_id"]')?.value || '0', 10);
             const kasa_id   = parseInt(form.querySelector('[name="kasa_id"]')?.value   || '0', 10);
 
             if (!taksit_id || !sozlesme_id || !yontem_id || !kasa_id) {
                 const msg = 'Zorunlu alan(lar) eksik. (taksit/sozleşme/ödeme türü/kasa)';
                 return hasSwal() ? Swal.fire('Eksik Bilgi', msg, 'warning') : alert(msg);
             }
 
             // ödenecek tutarı belirle
             let farkli_tutar = (chkFarkli?.checked === true) ? 1 : 0;
             let odenecek_tutar = 0;
 
             if (farkli_tutar) {
                 odenecek_tutar = tlParse(inpCustom?.value || '0');
                 if (odenecek_tutar <= 0) {
                     const msg = 'Lütfen geçerli bir tutar giriniz.';
                     return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                 }
                 if (odenecek_tutar > kalan_borc) {
                     const msg = 'Girilen tutar, kalan borçtan fazladır.';
                     return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                 }
             } else {
                 // varsayılan: seçili taksitte kalan kadar
                 odenecek_tutar = (taksit_raw > 0 ? taksit_raw : 0);
                 if (odenecek_tutar <= 0) {
                     const msg = 'Taksit tutarı bulunamadı.';
                     return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                 }
                 if (odenecek_tutar > kalan_borc) {
                     const msg = 'Tutar kalan borçtan büyük olamaz.';
                     return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                 }
             }
 
             // isteğe bağlı: açıklama
             const aciklama = 'Taksit tahsilatı';
 
             // Submit butonunu kilitle
             const origHtml = btnSubmit?.innerHTML;
             if (btnSubmit) {
                 btnSubmit.disabled = true;
                 btnSubmit.innerHTML = 'İşleniyor...';
             }
 
             try {
                 const payload = {
                     taksit_id,
                     sozlesme_id,
                     ogrenci_id,
                     yontem_id,
                     kasa_id,
                     taksit_tutar_raw: taksit_raw.toFixed(2),
                     farkli_tutar,
                     odenecek_tutar: odenecek_tutar.toFixed(2),
                     aciklama
                 };
 
                 const resp = await fetch('sozlesme-ajax/taksit-tahsil-et.php', {
                     method: 'POST',
                     headers: { 'Content-Type':'application/json' },
                     body: JSON.stringify(payload)
                 });
 
                 // Sunucu form-redirect HTML dönerse JSON parse hatası olmasın
                 const text = await resp.text();
                 let data;
                 try { data = JSON.parse(text); }
                 catch { throw new Error('Geçersiz sunucu cevabı'); }
 
                 if (!data?.ok) {
                     const msg = data?.msg || 'Tahsilat kaydedilemedi.';
                     if (hasSwal()) await Swal.fire('Hata', msg, 'error'); else alert(msg);
                     return;
                 }
 
                 // Başarılı
                 if (hasSwal()) {
                     await Swal.fire({
                         icon: 'success',
                         title: 'Başarılı',
                         text: 'Taksit ödemeniz başarıyla kaydedildi.',
                         timer: 1500,
                         showConfirmButton: false
                     });
                 } else {
                     alert('Taksit ödemeniz başarıyla kaydedildi.');
                 }
 
                 // Modalı kapat, sayfayı yenile (veya satırı güncelle)
                 bootstrap.Modal.getInstance(modalEl)?.hide();
                 // İstersen sadece ilgili satırı güncelle; şimdilik basitçe:
                 window.location.reload();
 
             } catch (err) {
                 console.error(err);
                 if (hasSwal()) Swal.fire('Sunucu Hatası', 'İşlem sırasında bir hata oluştu.', 'error');
                 else alert('Sunucu hatası.');
             } finally {
                 if (btnSubmit) {
                     btnSubmit.disabled = false;
                     btnSubmit.innerHTML = origHtml || 'Tahsil Et';
                 }
             }
         });
     }); */
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('add_fees_collect');
        const form = document.getElementById('formTaksitTahsil');
        if (!modalEl || !form) return;

        const chkFarkli = document.getElementById('chkFarkli');
        const inpCustom = document.getElementById('inpOdenecekTutar');
        const inpKalan = document.getElementById('ozetKalan');
        const btnSubmit = form.querySelector('button[type="submit"]');

        const tlParse = (s) => {
            if (!s) return 0;
            s = String(s).replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
            const n = parseFloat(s);
            return isNaN(n) ? 0 : n;
        };
        const hasSwal = () => (typeof Swal !== 'undefined');

        form.addEventListener('submit', async (ev) => {
            ev.preventDefault();

            const taksit_id = parseInt(modalEl.dataset.taksitId || '0', 10);
            const sozlesme_id = parseInt(modalEl.dataset.sozlesmeId || '0', 10);
            const ogrenci_id = parseInt(modalEl.dataset.ogrenciId || '0', 10);
            const taksit_raw = parseFloat(modalEl.dataset.taksitKalanRaw || '0');
            const kalan_borc = parseFloat(modalEl.dataset.kalanBorcRaw || '0');

            const yontem_id = parseInt(form.querySelector('[name="yontem_id"]')?.value || '0', 10);
            const kasa_id = parseInt(form.querySelector('[name="kasa_id"]')?.value || '0', 10);

            if (!taksit_id || !sozlesme_id || !yontem_id || !kasa_id) {
                const msg = 'Zorunlu alan(lar) eksik. (taksit/sozleşme/ödeme türü/kasa)';
                return hasSwal() ? Swal.fire('Eksik Bilgi', msg, 'warning') : alert(msg);
            }

            let farkli_tutar = (chkFarkli?.checked === true) ? 1 : 0;
            let odenecek_tutar = 0;

            if (farkli_tutar) {
                odenecek_tutar = tlParse(inpCustom?.value || '0');
                if (odenecek_tutar <= 0) {
                    const msg = 'Lütfen geçerli bir tutar giriniz.';
                    return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                }
                if (odenecek_tutar > kalan_borc) {
                    const msg = 'Girilen tutar, kalan borçtan fazladır.';
                    return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                }
            } else {
                odenecek_tutar = (taksit_raw > 0 ? taksit_raw : 0);
                if (odenecek_tutar <= 0) {
                    const msg = 'Taksit tutarı bulunamadı.';
                    return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                }
                if (odenecek_tutar > kalan_borc) {
                    const msg = 'Tutar kalan borçtan büyük olamaz.';
                    return hasSwal() ? Swal.fire('Uyarı', msg, 'warning') : alert(msg);
                }
            }

            const aciklama = 'Taksit tahsilatı';

            const origHtml = btnSubmit?.innerHTML;
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = 'İşleniyor...';
            }

            try {
                // 🔻 JSON yerine FormData
                const fd = new FormData();
                fd.append('taksit_id', taksit_id);
                fd.append('sozlesme_id', sozlesme_id);
                fd.append('ogrenci_id', ogrenci_id);
                fd.append('yontem_id', yontem_id);
                fd.append('kasa_id', kasa_id);
                fd.append('taksit_tutar_raw', taksit_raw.toFixed(2));
                fd.append('farkli_tutar', farkli_tutar);               // 0 / 1
                fd.append('odenecek_tutar', odenecek_tutar.toFixed(2));
                fd.append('aciklama', aciklama);

                const resp = await fetch('sozlesme-ajax/taksit-tahsil-et.php', {
                    method: 'POST',
                    body: fd
                });

                const text = await resp.text();
                let data;
                try { data = JSON.parse(text); }
                catch { throw new Error('Geçersiz sunucu cevabı'); }

                if (!data?.ok) {
                    const msg = data?.msg || 'Tahsilat kaydedilemedi.';
                    if (hasSwal()) await Swal.fire('Hata', msg, 'error'); else alert(msg);
                    return;
                }

                if (hasSwal()) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Başarılı',
                        text: 'Taksit ödemeniz başarıyla kaydedildi.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert('Taksit ödemeniz başarıyla kaydedildi.');
                }

                bootstrap.Modal.getInstance(modalEl)?.hide();
                window.location.reload();

            } catch (err) {
                console.error(err);
                if (hasSwal()) Swal.fire('Sunucu Hatası', 'İşlem sırasında bir hata oluştu.', 'error');
                else alert('Sunucu hatası.');
            } finally {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = origHtml || 'Tahsil Et';
                }
            }
        });
    });
</script>

</body>

</html>