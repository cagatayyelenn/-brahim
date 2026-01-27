<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$ogretmenler="SELECT 
    o.ogretmen_id,
    o.ogretmen_tc,
    o.ogretmen_adi,
    o.ogretmen_soyadi,
    o.ogretmen_tel,
    o.ogretmen_mail,
    o.ogretmen_cinsiyet,
    o.ogretmen_dogumtar,
    o.ogretmen_adres,
    a.alan_id,
    a.alan_adi
FROM ogretmen AS o
INNER JOIN alan AS a 
        ON o.alan_id = a.alan_id
ORDER BY o.ogretmen_id ASC;";
$ogretmens=$Ydil->get($ogretmenler);


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Öğretmen Listesi</title>
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
                                            Öğretmen Listesi
                                        </h1>
                                        <div class="page-header-subtitle">Şuanda Çalışan ve daha önce çalışmış tüm öğretmenleri burada bulabilirsiniz.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- Main page content-->
                    <div class="container-xl px-4 mt-n10">
                        <div class="card mb-4">
                            <div class="card-header"><a href="ogretmen-kayit.php">Öğretmen Ekle</a></div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                          <th>T.C</th>
                                          <th>Adı</th>
                                          <th>Soyadı</th>
                                          <th>Telefon</th>
                                          <th>Yaş</th>
                                          <th>Mail</th>
                                          <th>Alan</th>
                                          <th>İşlemler </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <?php foreach ($ogretmens as $ogretmenlr) {
                                        $dogum = new DateTime($ogretmenlr['ogretmen_dogumtar']);
                                        $bugun = new DateTime();
                                        $yas = $bugun->diff($dogum)->y; ?>
                                       <tr>
                                            <td><?= $ogretmenlr['ogretmen_tc']; ?></td>
                                            <td><?= $ogretmenlr['ogretmen_adi']; ?></td>
                                            <td><?= $ogretmenlr['ogretmen_soyadi']; ?></td>
                                            <td><?= $ogretmenlr['ogretmen_tel']; ?></td>
                                            <td><?= $yas; ?></td>
                                            <td><?= $ogretmenlr['ogretmen_mail']; ?></td>
                                            <td>
                                              <span class="badge bg-green-soft text-green"><?= $ogretmenlr['alan_adi']; ?></span>
                                            <td>
                                                 <button class="btn btn-datatable btn-icon btn-transparent-dark"><i data-feather="trash-2"></i></button>
                                            </td>
                                        </tr>
                                    <?php  } ?>


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
