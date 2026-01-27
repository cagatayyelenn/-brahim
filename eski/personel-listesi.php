<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$personeller="SELECT p.personel_id, p.personel_tc, p.personel_adi, p.personel_soyadi, p.personel_tel, p.personel_mail, p.personel_dogumtar, p.yetki, k.id AS kullanici_id, k.mail_adres AS kullanici_mail, k.telefon AS kullanici_tel, k.kisi, s.sube_id, s.sube_adi FROM personel AS p JOIN kullanici_giris AS k ON p.personel_id = k.kisi_id LEFT JOIN sube AS s ON s.sube_id = p.yetki ORDER BY `p`.`personel_id` DESC";
$personels=$Ydil->get($personeller);


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Personel Listesi</title>
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
                                            Personel Listesi
                                        </h1>
                                        <div class="page-header-subtitle">Şuanda Çalışan ve daha önce çalışmış tüm personelleri burada bulabilirsiniz.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- Main page content-->
                    <div class="container-xl px-4 mt-n10">
                        <div class="card mb-4">
                            <div class="card-header"><a href="personel-kayit.php">Personel Ekle</a></div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                          <th>T.C</th>
                                          <th>Adı</th>
                                          <th>Soyadı</th>
                                          <th>Telefon</th>
                                          <th>Yaşı</th>
                                          <th>Mail</th>
                                          <th>Yetki</th>
                                          <th>İşlemler </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <?php foreach ($personels as $personellr) {
                                        $dogum = new DateTime($personellr['personel_dogumtar']);
                                        $bugun = new DateTime();
                                        $yas = $bugun->diff($dogum)->y;

                                        ?>
                                       <tr>
                                            <td><?= $personellr['personel_tc']; ?></td>
                                            <td><?= $personellr['personel_adi']; ?></td>
                                            <td><?= $personellr['personel_soyadi']; ?></td>
                                            <td><?= $personellr['personel_tel']; ?></td>
                                            <td><?= $yas; ?></td>
                                            <td><?= $personellr['personel_mail']; ?></td>
                                            <td><div class="badge bg-success text-white rounded-pill"><?= !empty($personellr['sube_adi']) ? $personellr['sube_adi'] : 'Yönetici'; ?></div></td>

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
