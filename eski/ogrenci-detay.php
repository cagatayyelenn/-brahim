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


// Son ödenen taksit
$son_odeme = $Ydil->getone(" SELECT taksit_tarihi, taksit_tutari  FROM taksitler  WHERE ogrenci_id={$ogrenci_id} AND odendi=1  ORDER BY taksit_tarihi DESC, id DESC  LIMIT 1 ");

if ($son_odeme) {
    $son_odeme_bilgisi  = $son_odeme['taksit_tutari'];
    $gelentarih = date("d.m.Y", strtotime($son_odeme['taksit_tarihi']));
} else {
    // Ödenmiş taksit yoksa → son satış
    $son_satis = $Ydil->getone("SELECT pesinat_tutari, satis_tarihi  FROM kurs_satislari  WHERE ogrenci_id={$ogrenci_id}  ORDER BY satis_tarihi DESC, id DESC  LIMIT 1 ");

    if ($son_satis) {
        $son_odeme_bilgisi = $son_satis['pesinat_tutari'];
        $gelentarih =  date("d.m.Y", strtotime($son_satis['satis_tarihi']));
    } else {
        echo "Bu öğrenci için ödeme veya satış kaydı bulunamadı.";
    }
}

// Kurs sayısı, toplam ödeme ve toplam peşinat
$kurs_ozet = $Ydil->getone(" SELECT  COUNT(*) as kurs_sayisi, SUM(toplam_tutar) as toplam_odeme, SUM(pesinat_tutari) as toplam_pesinat FROM kurs_satislari WHERE ogrenci_id={$ogrenci_id} ");
// Taksitler özeti
$taksit_ozet = $Ydil->getone(" SELECT COUNT(*) as toplam_taksit, SUM(CASE WHEN odendi=1 THEN 1 ELSE 0 END) as odenen_taksit, SUM(CASE WHEN odendi=0 THEN 1 ELSE 0 END) as bekleyen_taksit, SUM(CASE WHEN odendi=0 AND taksit_tarihi < NOW() THEN 1 ELSE 0 END) as geciken_taksit, COALESCE(SUM(CASE WHEN odendi=1 THEN taksit_tutari ELSE 0 END),0) as odenen_tutar FROM taksitler WHERE ogrenci_id={$ogrenci_id} ");
// Toplam ödenen = peşinat + ödenmiş taksitler
$toplam_odenen = ($kurs_ozet['toplam_pesinat'] ?? 0) + ($taksit_ozet['odenen_tutar'] ?? 0);
// Kalan borç = toplam ödeme – toplam ödenen
$kalan_borc = ($kurs_ozet['toplam_odeme'] ?? 0) - $toplam_odenen;

// Çıktı

$toplamkurssayisi = $kurs_ozet['kurs_sayisi'];
//echo "Toplam ödeme: <b>".number_format($kurs_ozet['toplam_odeme'],0,",",".")." TL</b><br>";
//echo "Toplam alınan peşinat: <b>".number_format($kurs_ozet['toplam_pesinat'],0,",",".")." TL</b><br>";
//echo "Ödenmiş taksit sayısı: <b>{$taksit_ozet['odenen_taksit']}</b><br>";
$bekleyentaksitsay = $taksit_ozet['bekleyen_taksit'];
$gecmistaksit = $taksit_ozet['geciken_taksit'];
//echo "Toplam ödenen: <b>".number_format($toplam_odenen,0,",",".")." TL</b><br>";
$kalanborcec = number_format($kalan_borc,0,",",".");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Öğrenci Detay</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
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
                    <a class="nav-link active" >Ödemeler</a>
                    <a class="nav-link" href="ogrenci-sozlesme.php?ogrenci_id=<?= $ogrenci_id; ?>">Sözleşmeler</a>
                </nav>
                <hr class="mt-0 mb-4" />
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100 border-start-lg border-start-primary">
                            <div class="card-body">
                                <div class="h3"><?= (int)$toplamkurssayisi; ?> Adet Sözleşme vardır.</div>
                                <a class="text-arrow-icon small" href="javascript:void(0);">
                                    Sözleşme Detaylarını Gör
                                    <i data-feather="arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100 border-start-lg border-start-secondary">
                            <div class="card-body">
                                <div class="h3">Son Ödeme / Tarihi</div>
                                    <div class="small text-muted"><?= $son_odeme_bilgisi; ?> TL / <?= $gelentarih ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card h-100 border-start-lg border-start-success">
                            <div class="card-body">
                                <div class="h3 d-flex align-items-center">Kalan Borç / Bekleyen Taksitler</div>
                                <div class="small text-success">
                                    <?= $kalanborcec ?> TL / <?= $bekleyentaksitsay; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (!empty($taksit_ozet['geciken_taksit']) && $taksit_ozet['geciken_taksit'] > 0): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $taksit_ozet['geciken_taksit']; ?> adet geçmiş taksidiniz bulunmaktadır.
                    </div>
                <?php endif; ?>
                <div class="card card-header-actions mb-4">
                    <div class="card-header">
                        Taksit Ödeme Tablosu
                        <button class="btn btn-sm btn-primary" type="button"
                                onclick="location.href='taksit-odeme.php?ogrenci_id=<?= (int)$ogrenci_id ?>'">
                            Taksit Öde
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive table-billing-history">
                            <?php
                            // Öğrencinin tüm taksitlerini al
                            $taksitler = $Ydil->get(" SELECT * FROM taksitler WHERE ogrenci_id={$ogrenci_id} ORDER BY  CASE 
                                WHEN odendi = 0 AND taksit_tarihi < NOW() THEN 1   -- gecikenler
                                WHEN odendi = 0 AND taksit_tarihi >= NOW() THEN 2  -- bekleyenler
                                WHEN odendi = 1 THEN 3                             -- ödenmişler
                              END, taksit_tarihi ASC ");
                            ?>

                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Sözleşme ID</th>
                                    <th>Tarih</th>
                                    <th>Tutar</th>
                                    <th>Durumu</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($taksitler)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Kayıt bulunamadı.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($taksitler as $t): ?>
                                        <?php
                                        $fno   = '#'.$t['satis_id']; // sözleşme id
                                        $tarih = date("d.m.Y", strtotime($t['taksit_tarihi']));
                                        $tutar = number_format((float)$t['taksit_tutari'], 2, ',', '.').' TL';

                                        // Durum belirleme
                                        if ((int)$t['odendi'] === 1) {
                                            $durum = '<span class="badge bg-success">Ödendi</span>';
                                        } elseif ($t['taksit_tarihi'] < date("Y-m-d") && (int)$t['odendi'] === 0) {
                                            $durum = '<span class="badge bg-danger">Vadesi Geçmiş</span>';
                                        } else {
                                            $durum = '<span class="badge bg-secondary">Beklemede</span>';
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($fno) ?></td>
                                            <td><?= htmlspecialchars($tarih) ?></td>
                                            <td><?= htmlspecialchars($tutar) ?></td>
                                            <td><?= $durum ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
</body>
</html>