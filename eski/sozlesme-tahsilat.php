<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$ogrenciler="SELECT * FROM `ogrenci` ORDER BY `ogrenci`.`ogrenci_id` DESC";
$ogrencis=$Ydil->get($ogrenciler);

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Muhasene İşlemleri</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                                            <div class="page-header-icon"><i data-feather="activity"></i></div>
                                            Muhasebe İşlemleri
                                        </h1>
                                        <div class="page-header-subtitle">Example dashboard overview and content summary</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- Main page content-->
                    <div class="container-xl px-4 mt-n10">
                        <div class="row">
                            <div class="col-xl-4 mb-4">
                                <a class="card lift h-100" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    <div class="card-body d-flex justify-content-center flex-column">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="me-3">
                                                <i class="feather-xl text-primary mb-3" data-feather="package"></i>
                                                <h5>Yeni Sözleşme Oluşturma</h5>
                                                <div class="text-muted small">Kursiyer için yeni sözleşme oluşturabilirsin</div>
                                            </div>
                                            <img src="assets/img/illustrations/browser-stats.svg" alt="..." style="width: 8rem" />
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-xl-4 mb-4">
                                <!-- Dashboard example card 2-->
                                <a class="card lift h-100" data-bs-toggle="modal" data-bs-target="#exampleModaltahsilat">
                                    <div class="card-body d-flex justify-content-center flex-column">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="me-3">
                                                <i class="feather-xl text-secondary mb-3" data-feather="book"></i>
                                                <h5>Tahsilat Al</h5>
                                                <div class="text-muted small">Kursiyer taksit ödeme yapması için bu alandan devam ediniz.</div>
                                            </div>
                                            <img src="assets/img/illustrations/processing.svg" alt="..." style="width: 8rem" />
                                        </div>
                                    </div>
                                </a>
                            </div> <!-- Dashboard example card 3
                            <div class="col-xl-4 mb-4">

                                <a class="card lift h-100" href="#!">
                                    <div class="card-body d-flex justify-content-center flex-column">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="me-3">
                                                <i class="feather-xl text-green mb-3" data-feather="layout"></i>
                                                <h5>Pages &amp; Layouts</h5>
                                                <div class="text-muted small">To help get you started when building your new UI</div>
                                            </div>
                                            <img src="assets/img/illustrations/windows.svg" alt="..." style="width: 8rem" />
                                        </div>
                                    </div>
                                </a>
                            </div> ee-->
                        </div>

                    </div>
                </main>
                <!-- Button trigger modal -->

                <!-- Modal sozlesme  -->
                <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Öğrenci Listesi</h5>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-header">
                            <input class="form-control" id="xhrCustomSearch" name="xhrCustomSearch" placeholder=" Öğrenci Adı Giriniz" />
                        </div>
                        <div class="modal-body">
                            <table  class="table table-bordered table-genis">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th scope="col">Öğrenci Adı</th>
                                        <th scope="col">Öğrenci Soyadı</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-content">
                                    <tr>
                                        <td colspan="3" class="text-center">Arama Bekleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="col-md-12">
                                <ul class="pagination ml-auto pagination-content">

                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Kapat</button></div>
                    </div>
                  </div>
                </div>

                <!-- Modal tahsilat  -->
                <div class="modal fade" id="exampleModaltahsilat" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">Öğrenci Listesi</h5>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-header">
                                <input class="form-control" id="xhrCustomSearchtahsilat" name="xhrCustomSearchtahsilat" placeholder=" Öğrenci Adı Giriniz" />
                            </div>
                            <div class="modal-body">
                                <table  class="table table-bordered table-genis">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th scope="col">Öğrenci Adı</th>
                                        <th scope="col">Öğrenci Soyadı</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody-contentt">
                                    <tr>
                                        <td colspan="3" class="text-center">Arama Bekleniyor...</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="col-md-12">
                                    <ul class="pagination ml-auto pagination-content">

                                    </ul>
                                </div>
                            </div>
                            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Kapat</button></div>
                        </div>
                    </div>
                </div>


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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-pie-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables/datatables-simple-demo.js"></script>

        <script>
            let searchTimer;

            $(document).on('keyup', '#xhrCustomSearch', function () {
                clearTimeout(searchTimer); // Önceki zamanlayıcıyı iptal et
                let query = $(this).val();

                searchTimer = setTimeout(function () {
                    // Boşsa istek atmaya gerek yok
                    if (query.trim() === '') return;

                    $.ajax({
                        url: 'ogrenci-getir.php', // isteğin gideceği PHP dosyası
                        type: 'GET',         // POST kullanmak istersen type: 'POST'
                        data: { search: query },
                        beforeSend: function () {
                            console.log('Arama başlatılıyor...');
                        },
                        dataType:'json',
                        success: function (response) {
                            // Sonuçları ekrana yaz
                            $('#tbody-content').html(response.html);
                        },
                        error: function () {
                            console.error('Arama isteğinde hata oluştu.');
                        }
                    });
                }, 750); // 0.75 saniye gecikme
            });
        </script>
        <script>
            let searchTimert;

            $(document).on('keyup', '#xhrCustomSearchtahsilat', function () {
                clearTimeout(searchTimert); // Önceki zamanlayıcıyı iptal et
                let queryt = $(this).val();

                searchTimert = setTimeout(function () {
                    // Boşsa istek atmaya gerek yok
                    if (queryt.trim() === '') return;

                    $.ajax({
                        url: 'ogrenci_getirt.php', // isteğin gideceği PHP dosyası
                        type: 'GET',         // POST kullanmak istersen type: 'POST'
                        data: { search: queryt },
                        beforeSend: function () {
                            console.log('Arama başlatılıyor...');
                        },
                        dataType:'json',
                        success: function (response) {
                            // Sonuçları ekrana yaz
                            $('#tbody-contentt').html(response.html);
                        },
                        error: function () {
                            console.error('Arama isteğinde hata oluştu.');
                        }
                    });
                }, 750); // 0.75 saniye gecikme
            });
        </script>
    </body>
</html>
