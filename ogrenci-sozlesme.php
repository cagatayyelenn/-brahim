<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$ogrenci_id = (int)($_GET['ogrenci_id'] ?? 0);
if ($ogrenci_id <= 0) {
    header('Location: ogrenci-listesi.php');
    exit;
}
$ogrencibilgisi = $Ydil->getone("SELECT * FROM ogrenci WHERE ogrenci_id={$ogrenci_id} ");
$ogradi = $ogrencibilgisi['ogrenci_adi'];
$ogrtel = $ogrencibilgisi['ogrenci_tel'];
$dogumTarihi = $ogrencibilgisi['ogrenci_dogumtar']; // "2008-03-07"
$bugun = new DateTime();
$dogum = new DateTime($dogumTarihi);
$yas = $bugun->diff($dogum)->y;


$kurs_ozet = $Ydil->getone(" SELECT  COUNT(*) as kurs_sayisi, SUM(toplam_tutar) as toplam_odeme, SUM(pesinat_tutari) as toplam_pesinat FROM kurs_satislari WHERE ogrenci_id={$ogrenci_id} ");
$taksit_ozet = $Ydil->getone(" SELECT COUNT(*) as toplam_taksit, SUM(CASE WHEN odendi=1 THEN 1 ELSE 0 END) as odenen_taksit, SUM(CASE WHEN odendi=0 THEN 1 ELSE 0 END) as bekleyen_taksit, SUM(CASE WHEN odendi=0 AND taksit_tarihi < NOW() THEN 1 ELSE 0 END) as geciken_taksit, COALESCE(SUM(CASE WHEN odendi=1 THEN taksit_tutari ELSE 0 END),0) as odenen_tutar FROM taksitler WHERE ogrenci_id={$ogrenci_id} ");
$kurslar = $Ydil->get("
  SELECT id, kurs_adi, toplam_tutar, satis_tarihi
  FROM kurs_satislari
  WHERE ogrenci_id = {$ogrenci_id}
  ORDER BY satis_tarihi DESC, id DESC
");
// Bayrak eşleştirme (dil koduna göre)
$bayraklar = [
    'ingilizce' => 'gb',
    'almanca'   => 'de',
    'fransızca' => 'fr',
    'ispanyolca'=> 'es'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Öğrenci Detay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css" />
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
    <style>
        .list-group-item {
            transition: background-color 0.2s ease-in-out;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        .btn-outline-danger {
            border-radius: 20px;
            padding: 2px 12px;
        }
    </style>
</head>
<body class="nav-fixed">
<?php include 'ekler/sidebar.php'; ?>
<div id="layoutSidenav">
    <?php include 'ekler/menu.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
                <div class="container-xl px-4">
                    <div class="page-header-content">
                        <div class="row align-items-center justify-content-between pt-3">
                            <div class="col-auto mb-3">
                                <h1 class="page-header-title">
                                    <div class="page-header-icon"><i data-feather="user"></i></div>
                                    <?= htmlspecialchars($ogradi) ?>
                                </h1>

                                <div class="text-muted small">
                                    Yaş:  <?= $yas; ?>  | Tel:  <?= $ogrtel; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="container-xl px-4 mt-4">
                <nav class="nav nav-borders">
                    <a class="nav-link ms-0" href="ogrenci-bilgisi.php?ogrenci_id=<?= $ogrenci_id; ?>">Profil</a>
                    <a class="nav-link " href="ogrenci-detay.php?ogrenci_id=<?= $ogrenci_id; ?>">Ödemeler</a>
                    <a class="nav-link active" >Sözleşmeler</a>
                </nav>
                <hr class="mt-0 mb-4" />
                <?php if (!empty($taksit_ozet['geciken_taksit']) && $taksit_ozet['geciken_taksit'] > 0): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $taksit_ozet['geciken_taksit']; ?> adet geçmiş taksidiniz bulunmaktadır.
                    </div>
                <?php endif; ?>
                 <div class="card card-header-actions mb-4">
                    <div class="card-header">
                        <?= $kurs_ozet['kurs_sayisi']; ?> Adet Sözleşme Bulunmuştur.
                        <a href="yenisozlesme.php?ogrid=<?= htmlspecialchars($ogrenci_id)?>"  class="btn btn-sm btn-primary" type="button">Yeni Sözleşme Oluştur.</a>
                    </div>
                    <div class="card-body px-0">

                        <?php if (!empty($kurslar)): ?>
                            <?php foreach ($kurslar as $k): ?>
                                <?php
                                // Görsel bilgiler
                                $bayrak = $bayraklar[strtolower($k['dil_kodu'])] ?? 'flag-icon';
                                $tarih  = date("d-m-Y", strtotime($k['satis_tarihi']));
                                $tutar  = number_format((float)$k['toplam_tutar'], 0, ',', '.')." TL";

                                // Bu kursun taksitleri
                                $satisId = (int)$k['id'];
                                $taksitler = $Ydil->get("SELECT t.id, t.satis_id, t.taksit_tutari, t.taksit_tarihi, t.odendi, t.odeme_tarihi, t.odeme_tur_id FROM taksitler t  WHERE t.satis_id = {$satisId} ORDER BY t.taksit_tarihi ASC, t.id ASC ") ?: [];
                                ?>
                                <style>
                                    #collapse-<?= $k['id'] ?>{
                                        padding-left:.5rem!important;padding-right:.5rem!important;
                                    }
                                    .taksit-badge{font-size:.75rem}
                                </style>

                                <div class="d-flex align-items-center justify-content-between px-4">
                                    <div class="d-flex align-items-center">
                                        <i class="flag-icon flag-icon-gb" style="font-size:2em;"></i>
                                        <div class="ms-4">
                                            <div class="small"><?= htmlspecialchars($k['kurs_adi']) ?></div>
                                            <div class="text-xs text-muted"><?= $tutar ?> / <?= $tarih ?></div>
                                        </div>
                                    </div>

                                    <div class="ms-4 small">
                                        <a class="btn btn-outline-primary" href="sozlesme-yazdir.php?satis_id=<?= (int)$k['id'] ?>&print=1" target="_blank">
                                            Sözleşmeyi Yazdır
                                        </a>
                                        <a class="btn btn-outline-secondary" data-bs-toggle="collapse" href="#collapse-<?= $k['id'] ?>" role="button" aria-expanded="false" aria-controls="collapse-<?= $k['id'] ?>">Sözleşme Detayları</a>
                                    </div>
                                </div>
                                <hr />

                                <div class="collapse px-2" id="collapse-<?= $k['id'] ?>">
                                    <ul class="list-group list-group-flush shadow-sm rounded">
                                        <?php if (empty($taksitler)): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Bu sözleşmeye ait taksit bulunamadı.</span>
                                                <button class="btn btn-outline-secondary btn-sm" disabled>—</button>
                                            </li>
                                        <?php else: ?>
                                            <?php
                                            $sira = 1;
                                            foreach ($taksitler as $t):
                                                $tarihFmt = $t['taksit_tarihi'] ? date('d.m.Y', strtotime($t['taksit_tarihi'])) : '-';
                                                $tutarFmt = number_format((float)$t['taksit_tutari'], 2, ',', '.') . ' TL';
                                                $odendi   = (int)$t['odendi'] === 1;

                                                // Durum rozeti
                                                if ($odendi) {
                                                    $durumHtml = '<span class="badge bg-success taksit-badge">Ödendi</span>';
                                                } else {
                                                    $gecmis = $t['taksit_tarihi'] && (strtotime($t['taksit_tarihi']) < strtotime(date('Y-m-d')));
                                                    $durumHtml = $gecmis
                                                        ? '<span class="badge bg-danger taksit-badge">Vadesi Geçmiş</span>'
                                                        : '<span class="badge bg-secondary taksit-badge">Beklemede</span>';
                                                }
                                                ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex flex-column">
                                                        <div class="fw-bold">Taksit #<?= $sira ?></div>
                                                        <div class="text-muted" style="font-size:.9rem;">
                                                            Tarih: <?= htmlspecialchars($tarihFmt) ?> &nbsp; • &nbsp; Tutar: <?= htmlspecialchars($tutarFmt) ?>
                                                            &nbsp; • &nbsp; <?= $durumHtml ?>
                                                        </div>
                                                    </div>

                                                    <?php if ($odendi): ?>
                                                        <!-- Makbuz sayfası örnek: makbuz.php?taksit_id=... -->
                                                        <a class="btn btn-outline-success btn-sm" href="taksit-makbuz.php?taksit_id=<?= (int)$t['id'] ?>">Makbuzu gör</a>
                                                    <?php else: ?>
                                                        <!-- Ödeme için data-* ile bilgileri taşıyalım -->
                                                        <button
                                                                type="button"
                                                                class="btn btn-outline-danger btn-sm btn-pay"
                                                                data-ogrenci-id="<?= (int)$ogrenci_id ?>"
                                                                data-satis-id="<?= (int)$t['satis_id'] ?>"
                                                                data-taksit-id="<?= (int)$t['id'] ?>"
                                                                data-tarih="<?= htmlspecialchars($tarihFmt) ?>"
                                                                data-tutar="<?= (float)$t['taksit_tutari'] ?>"
                                                                data-toplam="<?= (float)$k['toplam_tutar'] ?>"
                                                                data-pesinat="<?= (float)$k['pesinat_tutari'] ?>"
                                                                data-kalan="<?= (float)$k['kalan_tutar'] ?>"
                                                        >
                                                            Taksit Öde
                                                        </button>
                                                    <?php endif; ?>
                                                </li>
                                                <?php
                                                $sira++;
                                            endforeach;
                                            ?>
                                        <?php endif; ?>
                                    </ul>
                                    <hr />
                                </div>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">Bu öğrenciye ait kurs bulunamadı.</div>
                        <?php endif; ?>


                    </div>
                </div>

            </div>
        </main>
        <footer class="footer-admin mt-auto footer-light">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a>&middot;<a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>

<script>
    (function(){
        const isPaymentPage = /taksit-odeme\.php$/i.test(location.pathname);

        const fmtTL = n => new Intl.NumberFormat('tr-TR',
            {minimumFractionDigits:2, maximumFractionDigits:2}).format(Number(n))+' TL';

        // Bu alanlar sadece taksit-odeme.php içindeyse var:
        const sozlesmeKart = document.getElementById('sozlesmeKart');
        const odemeKart    = document.getElementById('odemeKart');
        const inpToplam    = document.getElementById('toplamTutar');
        const inpPesinat   = document.getElementById('pesinat');
        const inpKalan     = document.getElementById('kalanTutar1');
        const inpTaksit    = document.getElementById('taksittutari');
        const spnTarih     = document.getElementById('odemeTarihi');
        const hidTaksitId  = document.getElementById('hidTaksitId');
        const hidSatisId   = document.getElementById('hidSatisId');
        const hidTutar     = document.getElementById('hidTutar');
        const chkDifferent = document.getElementById('flexCheckDefault');
        const inpFarkli    = document.getElementById('farkliTutar');
        const btnOdeme     = document.getElementById('btnOdemeYap');

        document.querySelectorAll('.btn-pay').forEach(btn=>{
            btn.addEventListener('click', (e)=>{
                const d = btn.dataset;

                // Ödeme sayfasında değilsek -> yönlendir
                if (!isPaymentPage) {
                    const qs = new URLSearchParams({
                        ogrenci_id: d.ogrenciId || '',
                        satis_id:   d.satisId   || '',
                        taksit_id:  d.taksitId  || ''
                    }).toString();
                    location.href = 'taksit-odeme.php?' + qs;
                    return;
                }

                // Ödeme sayfasındaysak -> paneli doldur/aç
                e.preventDefault();

                if (hidTaksitId && hidSatisId && hidTutar) {
                    hidTaksitId.value = d.taksitId || '';
                    hidSatisId.value  = d.satisId  || '';
                    hidTutar.value    = d.tutar    || '';
                }

                if (inpToplam)  inpToplam.value  = fmtTL(d.toplam||0);
                if (inpPesinat) inpPesinat.value = fmtTL(d.pesinat||0);
                if (inpKalan)   inpKalan.value   = fmtTL(d.kalan||0);
                if (inpTaksit)  inpTaksit.value  = fmtTL(d.tutar||0);
                if (spnTarih)   spnTarih.textContent = d.tarih||'—';

                if (chkDifferent) chkDifferent.checked = false;
                if (inpFarkli){ inpFarkli.readOnly = true; inpFarkli.value=''; }

                if (sozlesmeKart) sozlesmeKart.style.display = 'block';
                if (odemeKart)    odemeKart.style.display    = 'block';
                if (btnOdeme)     btnOdeme.disabled = false;
            });
        });
    })();
</script>
</body>
</html>