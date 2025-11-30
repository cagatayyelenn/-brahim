<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";
$sql = "SELECT 
    o.ogrenci_id, 
    o.ogrenci_adi, 
    o.ogrenci_soyadi, 
    o.ogrenci_tel, 
    o.ogrenci_dogumtar, 
    COALESCE(cs.course_count, 0) AS kurs_sayisi, 
    COALESCE(cs.total_odeme, 0) AS toplam_kurs_odeme, 
    COALESCE(tk.total_taksit, 0) AS toplam_taksit, 
    COALESCE(tk.unpaid_amount, 0) AS odenmemis_bakiye, 
    COALESCE(tk.overdue_count, 0) AS gecikmis_taksit_sayisi
FROM ogrenci o
LEFT JOIN (
    SELECT ogrenci_id, COUNT(*) AS course_count, SUM(toplam_tutar) AS total_odeme
    FROM kurs_satislari
    GROUP BY ogrenci_id
) cs ON cs.ogrenci_id = o.ogrenci_id
LEFT JOIN (
    SELECT 
        ogrenci_id, 
        COUNT(*) AS total_taksit, 
        SUM(CASE WHEN odendi = 0 THEN taksit_tutari ELSE 0 END) AS unpaid_amount, 
        SUM(CASE WHEN odendi = 0 AND DATE(taksit_tarihi) < CURDATE() THEN 1 ELSE 0 END) AS overdue_count
    FROM taksitler
    GROUP BY ogrenci_id
) tk ON tk.ogrenci_id = o.ogrenci_id
WHERE COALESCE(tk.overdue_count, 0) > 0   -- 🔴 sadece gecikmiş taksiti olanlar
ORDER BY tk.overdue_count DESC;";
$rows = $Ydil->get($sql);

function fmt_tl($n) {
    return number_format((float)$n, 2, ',', '.').' TL';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Sqooler</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
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
            <header class="page-header page-header-dark" style="background-color: #007bff; padding-bottom: 50px;">

                <div class="container-xl px-4">
                    <div class="page-header-content pt-4">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mt-4">
                                <h1 class="page-header-title">
                                    <div class="page-header-icon"><i data-feather="filter"></i></div>
                                    Ödemesi Geçikmiş Öğrenci Listesi
                                </h1>
                                <div class="page-header-subtitle">İşlem yapmak istediğiniz öğrencinin sonda bulunan kart üstüne tıklayın.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Main page content-->

            <div class="container-xl px-4 mt-n10">


                <!-- Example DataTable for Dashboard Demo-->
                <div class="card mb-4">
                    <div class="card-header">Öğrenci Listesi</div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                            <tr>
                                <th>Adı</th>
                                <th>Soyadı</th>
                                <th>Telefon</th>
                                <th>Yaşı</th>
                                <th>Toplam Kurs Ödemesi</th>
                                <th>Ödenmemiş Bakiye</th>
                                <th>Geçikmiş Taksit Sayısı</th>
                                <th>İşlem</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>

                                <th>Adı</th>
                                <th>Soyadı</th>
                                <th>Telefon</th>
                                <th>Yaşı</th>
                                <th>Toplam Kurs Ödemesi</th>
                                <th>Ödenmemiş Bakiye</th>
                                <th>Geçikmiş Taksit Sayısı</th>
                                <th>İşlem</th>
                            </tr>
                            </tfoot>

                            <tbody>
                            <?php foreach ($rows as $r): ?>
                                <?php
                                // yaş
                                $yas = '';
                                if (!empty($r['ogrenci_dogumtar']) && $r['ogrenci_dogumtar'] !== '0000-00-00') {
                                    try {
                                        $dogum = new DateTime($r['ogrenci_dogumtar']);
                                        $yas   = (new DateTime())->diff($dogum)->y;
                                    } catch (Exception $e) {}
                                }
                                // kırmızı satır class
                                $style = ((int)$r['gecikmis_taksit_sayisi'] > 0) ? '<div class="dropdown-notifications-item-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>' : '';
                                ?>
                                <tr >
                                    <td><?= htmlspecialchars($r['ogrenci_adi']) ?></td>
                                    <td><?= htmlspecialchars($r['ogrenci_soyadi']) ?></td>
                                    <td><?= htmlspecialchars($r['ogrenci_tel']) ?></td>
                                    <td><?= htmlspecialchars($yas) ?></td> 
                                    <td><div class="badge bg-success text-white rounded-pill"><?= fmt_tl($r['toplam_kurs_odeme']) ?></div></td>
                                    <td><div class="badge bg-warning rounded-pill"><?= fmt_tl($r['odenmemis_bakiye']) ?></div></td>
                                    <td><?php if (!empty($r['gecikmis_taksit_sayisi']) && $r['gecikmis_taksit_sayisi'] > 0): ?>
                                            <div class="badge bg-danger rounded-pill">
                                                <?= (int)$r['gecikmis_taksit_sayisi']; ?>
                                            </div>
                                        <?php else: ?>
                                            <div>Yok</div>
                                        <?php endif; ?></td>
                                    <td>
                                        <a href="ogrenci-detay.php?ogrenci_id=<?= (int)$r['ogrenci_id'] ?>" class="btn btn-datatable btn-icon btn-transparent-dark me-2">
                                            <i class="fa-regular fa-credit-card"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <footer class="footer-admin mt-auto footer-light">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website 2021</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a>
                        &middot;
                        <a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="js/datatables/datatables-simple-demo.js"></script>
</body>
</html>
