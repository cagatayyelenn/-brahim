<?php
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

// ID kontrol
$ogr_no = preg_replace('/\D+/', '', $_GET['id'] ?? '');
if (!$ogr_no) {
    echo '<div class="alert alert-danger">Geçersiz Öğrenci ID</div>';
    exit;
}

$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

// Öğrenciyi doğrula
$ogr = $db->gets("SELECT ogrenci_id, ogrenci_adi, ogrenci_soyadi FROM ogrenci1 WHERE ogrenci_numara = :no AND sube_id = :sube LIMIT 1", [':no' => $ogr_no, ':sube' => $sube_id]);

if (!$ogr) {
    echo '<div class="alert alert-danger">Öğrenci bulunamadı.</div>';
    exit;
}

$ogrenci_id = $ogr['ogrenci_id'];
$today = date('Y-m-d');

// --- SÖZLEŞME SORGUSU (ogrenci-detay-sozlesme.php dosyasından alındı) ---
$sql1 = "
SELECT 
  o.ogrenci_id,
  s.sozlesme_id, s.sozlesme_no, s.net_ucret, s.taksit_sayisi, s.odeme_tipi, s.sozlesme_tarihi, s.baslangic_tarihi, s.bitis_tarihi,
  t.taksit_id, t.sira_no, t.vade_tarihi, t.tutar AS taksit_tutar, t.odendi_tutar AS taksit_odenen, t.durum AS taksit_durum,
  odm.tutar AS pesinat_tutar
FROM ogrenci1 o
LEFT JOIN sozlesme1 s ON s.ogrenci_id = o.ogrenci_id
LEFT JOIN taksit1   t ON t.sozlesme_id = s.sozlesme_id
LEFT JOIN odeme1  odm ON odm.sozlesme_id = s.sozlesme_id
WHERE o.ogrenci_id = :oid
ORDER BY s.sozlesme_tarihi DESC, s.sozlesme_id DESC, t.vade_tarihi ASC
";

$rows = $db->get($sql1, [':oid' => $ogrenci_id]);
$contracts = [];

foreach ($rows as $r) {
    $sid = $r['sozlesme_id'];
    if (!$sid) continue;

    if (!isset($contracts[$sid])) {
        $rawPesinat = (float)($r['pesinat_tutar'] ?? 0);
        $toplamTaksit = 0;
        foreach($rows as $subRow) {
            if($subRow['sozlesme_id'] == $sid) {
                $toplamTaksit += (float)$subRow['taksit_tutar'];
            }
        }
        $sozlesmeTutar = (float)$r['net_ucret'];
        $effectivePesinat = $rawPesinat;
        if (($toplamTaksit + $rawPesinat) > $sozlesmeTutar) {
             $effectivePesinat = max(0, $sozlesmeTutar - $toplamTaksit);
        }

        $contracts[$sid] = [
            'header' => [
                'sozlesme_id' => (int) $sid,
                'sozlesme_no' => $r['sozlesme_no'],
                'net_ucret' => $sozlesmeTutar,
                'taksit_sayisi' => (int) $r['taksit_sayisi'],
                'odeme_tipi' => $r['odeme_tipi'],
                'sozlesme_tarihi' => $r['sozlesme_tarihi'],
                'pesinat' => $effectivePesinat
            ],
            'taksitler' => [],
            'ozet' => ['odenen_tutar' => $effectivePesinat]
        ];
    }

    if ($r['taksit_id']) {
        $contracts[$sid]['taksitler'][] = [
            'taksit_id' => (int) $r['taksit_id'],
            'vade_tarihi' => $r['vade_tarihi'],
            'tutar' => (float) $r['taksit_tutar'],
            'odenen' => (float) $r['taksit_odenen'],
            'durum' => $r['taksit_durum'],
            'late' => ((float)$r['taksit_odenen'] < (float)$r['taksit_tutar'] && $r['vade_tarihi'] < $today)
        ];
        $contracts[$sid]['ozet']['odenen_tutar'] += (float) $r['taksit_odenen'];
    }
}
?>

<!-- İÇERİK BAŞLANGICI -->
<div class="accordions-items-seperate" id="accordionSozAjax">
    <?php if (empty($contracts)): ?>
        <div class="alert alert-warning d-flex align-items-center">
            <i class="ti ti-info-circle me-2"></i>
            Bu öğrenciye ait sözleşme bulunamadı.
            <a class="btn btn-primary btn-sm ms-auto" href="sozlesme-olustur.php?id=<?= $ogr_no ?>">Sözleşme Oluştur</a>
        </div>
    <?php else: ?>
        <?php $i = 0; foreach ($contracts as $sid => $c): 
            $h = $c['header'];
            $collapseId = "sz_ajax_" . $sid;
        ?>
            <div class="accordion-item mb-3">
                <h2 class="accordion-header d-flex align-items-center">
                    <button class="accordion-button flex-grow-1 <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                        <span class="avatar avatar-sm bg-success me-2 rounded-circle"><i class="ti ti-file-text"></i></span>
                        <div>
                            <span class="fw-bold d-block text-dark">Sözleşme #<?= $sid ?></span>
                            <span class="text-muted fs-12"><?= date('d.m.Y', strtotime($h['sozlesme_tarihi'])) ?></span>
                        </div>
                        <div class="ms-auto me-3 text-end d-none d-md-block">
                            <span class="d-block fw-bold text-dark"><?= number_format($h['net_ucret'], 2, ',', '.') ?> TL</span>
                            <span class="text-success fs-12">Tahsilat: <?= number_format($c['ozet']['odenen_tutar'], 2, ',', '.') ?> TL</span>
                        </div>
                    </button>
                    <!-- Aksiyon Butonları -->
                    <div class="d-flex align-items-center gap-2 pe-3">
                         <a href="sozlesme-guncelleme.php?id=<?= $sid ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-edit"></i></a>
                         <a href="sozlesme-belge.php?id=<?= $sid ?>" target="_blank" class="btn btn-outline-dark btn-sm"><i class="ti ti-printer"></i></a>
                         <a href="sozlesme-senet.php?id=<?= $sid ?>" target="_blank" class="btn btn-outline-warning btn-sm"><i class="ti ti-receipt"></i></a>
                    </div>
                </h2>

                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#accordionSozAjax">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Vade</th>
                                        <th>Tutar</th>
                                        <th>Ödenen</th>
                                        <th>Kalan</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Peşinat Satırı -->
                                    <?php if ($h['pesinat'] > 0): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark">Peşinat</span></td>
                                        <td><?= number_format($h['pesinat'], 2, ',', '.') ?> ₺</td>
                                        <td><?= number_format($h['pesinat'], 2, ',', '.') ?> ₺</td>
                                        <td>0,00 ₺</td>
                                        <td><span class="badge bg-success">Ödendi</span></td>
                                    </tr>
                                    <?php endif; ?>

                                    <!-- Taksitler -->
                                    <?php foreach ($c['taksitler'] as $t): 
                                        $kalan = $t['tutar'] - $t['odenen'];
                                        $durumClass = $t['late'] ? 'danger' : ($kalan <= 0 ? 'success' : 'warning');
                                        $durumText = $t['late'] ? 'Gecikmiş' : ($kalan <= 0 ? 'Tamamlandı' : 'Ödenmedi');
                                    ?>
                                    <tr>
                                        <td><?= date('d.m.Y', strtotime($t['vade_tarihi'])) ?></td>
                                        <td class="fw-medium"><?= number_format($t['tutar'], 2, ',', '.') ?> ₺</td>
                                        <td class="text-success"><?= number_format($t['odenen'], 2, ',', '.') ?> ₺</td>
                                        <td class="text-danger fw-bold"><?= number_format($kalan, 2, ',', '.') ?> ₺</td>
                                        <td><span class="badge bg-<?= $durumClass ?>"><?= $durumText ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php $i++; endforeach; ?>
    <?php endif; ?>
</div>
