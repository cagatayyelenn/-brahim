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

// --- SÖZLEŞME VE TAKSİT VERİLERİNİ ÇEK ---
// 1. Öğrencinin tüm sözleşmelerini bul
$sozlesmeler = $db->gets("
    SELECT * FROM sozlesme1 
    WHERE ogrenci_id = :oid 
    ORDER BY sozlesme_tarihi DESC, sozlesme_id DESC
", [':oid' => $ogrenci]);

$contracts = [];

if ($sozlesmeler) {
    foreach ($sozlesmeler as $soz) {
        $sid = $soz['sozlesme_id'];

        // 2. Her sözleşme için taksitleri çek
        $taksitler = $db->gets("
            SELECT * FROM taksit1 
            WHERE sozlesme_id = :sid 
            ORDER BY vade_tarihi ASC
        ", [':sid' => $sid]);

        // 3. Peşinat bilgisi (odeme1 tablosunda, taksit_id'si 0 veya boş olanlar peşinat sayılabilir, 
        //    ya da genel ödeme toplamından taksit toplamını çıkarabiliriz. 
        //    Mevcut yapıda 'odeme1' tablosundan peşinatı şöyle almayı deneyelim:
        //    Genelde peşinat ilk ödemedir veya tipi farklıdır. Şimdilik sadece tutarı alalım.)
        //    NOT: Kullanıcı yapısına göre 'odeme1' tablosunu sadece peşinat için kullanıyorsa:
        $odeme_toplam = $db->get("SELECT SUM(tutar) as top FROM odeme1 WHERE sozlesme_id = :sid", [':sid' => $sid]);
        $toplam_odenen_genel = (float) ($odeme_toplam[0]['top'] ?? 0);

        // Taksitlerin toplamı
        $toplam_taksit_tutari = 0;
        $toplam_taksit_odenen = 0;
        $taksit_listesi = [];

        if ($taksitler) {
            foreach ($taksitler as $t) {
                $toplam_taksit_tutari += (float) $t['tutar'];
                $toplam_taksit_odenen += (float) $t['odendi_tutar'];

                $taksit_listesi[] = [
                    'taksit_id' => $t['taksit_id'],
                    'vade_tarihi' => $t['vade_tarihi'],
                    'tutar' => (float) $t['tutar'],
                    'odenen' => (float) $t['odendi_tutar'],
                    'durum' => $t['durum'], // 0/1
                    'late' => ((float) $t['odendi_tutar'] < (float) $t['tutar'] && $t['vade_tarihi'] < $today)
                ];
            }
        }

        // Peşinat Hesabı: Sözleşme Net Ücret - Toplam Taksit Tutarı (Eğer fark varsa peşinattır)
        $sozlesme_tutar = (float) $soz['net_ucret'];
        $pesinat = max(0, $sozlesme_tutar - $toplam_taksit_tutari);

        // Görüntüleme dizisini oluştur
        $contracts[$sid] = [
            'header' => [
                'sozlesme_id' => $sid,
                'sozlesme_no' => $soz['sozlesme_no'],
                'net_ucret' => $sozlesme_tutar,
                'taksit_sayisi' => (int) $soz['taksit_sayisi'],
                'odeme_tipi' => $soz['odeme_tipi'],
                'sozlesme_tarihi' => $soz['sozlesme_tarihi'],
                'pesinat' => $pesinat
            ],
            'taksitler' => $taksit_listesi,
            'ozet' => [
                'odenen_tutar' => $toplam_taksit_odenen + $pesinat // kabaca toplam ödenen
            ]
        ];
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
        <?php $i = 0;
        foreach ($contracts as $sid => $c):
            $h = $c['header'];
            $collapseId = "sz_ajax_" . $sid;
            ?>
            <div class="accordion-item mb-3">
                <h2 class="accordion-header d-flex align-items-center">
                    <button class="accordion-button flex-grow-1 <?= $i === 0 ? '' : 'collapsed' ?>" type="button"
                        data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                        <span class="avatar avatar-sm bg-success me-2 rounded-circle"><i class="ti ti-file-text"></i></span>
                        <div>
                            <span class="fw-bold d-block text-dark">Sözleşme #<?= $sid ?></span>
                            <span class="text-muted fs-12"><?= date('d.m.Y', strtotime($h['sozlesme_tarihi'])) ?></span>
                        </div>
                        <div class="ms-auto me-3 text-end d-none d-md-block">
                            <span class="d-block fw-bold text-dark"><?= number_format($h['net_ucret'], 2, ',', '.') ?> TL</span>
                            <span class="text-success fs-12">Tahsilat:
                                <?= number_format($c['ozet']['odenen_tutar'], 2, ',', '.') ?> TL</span>
                        </div>
                    </button>
                    <!-- Aksiyon Butonları -->
                    <div class="d-flex align-items-center gap-2 pe-3">
                        <a href="sozlesme-guncelleme.php?id=<?= $sid ?>" class="btn btn-outline-primary btn-sm"><i
                                class="ti ti-edit"></i></a>
                        <a href="sozlesme-belge.php?id=<?= $sid ?>" target="_blank" class="btn btn-outline-dark btn-sm"><i
                                class="ti ti-printer"></i></a>
                        <a href="sozlesme-senet.php?id=<?= $sid ?>" target="_blank" class="btn btn-outline-warning btn-sm"><i
                                class="ti ti-receipt"></i></a>
                    </div>
                </h2>

                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                    data-bs-parent="#accordionSozAjax">
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
                                        <th class="text-end">İşlem</th>
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
                                            <td></td>
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
                                            <td class="text-end">
                                                <?php if ($kalan > 0): ?>
                                                    <button class="btn btn-primary btn-sm btn-ode"
                                                        data-taksit-id="<?= $t['taksit_id'] ?>" data-ogrenci-id="<?= $ogrenci_id ?>"
                                                        data-sozlesme-id="<?= $sid ?>" data-tutar="<?= $kalan ?>">
                                                        Öde
                                                    </button>
                                                <?php endif; ?>
                                            </td>
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