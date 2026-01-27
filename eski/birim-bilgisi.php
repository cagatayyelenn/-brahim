<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php"; 

$birimler="SELECT * FROM `birim` ORDER BY `birim`.`birim_id` ASC";
$birims=$Ydil->get($birimler);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Birim Bilgisi - Sqooler</title>
        <link href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
        <script type="text/javascript" src="js/sweetalert2.all.min.js"></script>
    </head>

    <body class="nav-fixed">
        <?php include 'ekler/sidebar.php'; ?>
        <div id="layoutSidenav">
            <?php include 'ekler/menu.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
                        <div class="container-xl px-4">
                            <div class="page-header-content pt-4">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mt-4">
                                        <h1 class="page-header-title">
                                            <div class="page-header-icon"><i data-feather="edit-3"></i></div>
                                            Birim Bilgisi
                                        </h1>
                                        <div class="page-header-subtitle">Birim bilgisi ile ilgili işlemler yapılır</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- Main page content-->
                    <div class="container-xl px-4 mt-n10">
                        <div class="row">
                            <div class="col-lg-9">
                                <!-- Default Bootstrap Form Controls-->
                                <div id="ekle">
                                  <div class="card mb-4">
                                      <div class="card-header">Alan Ekle</div>
                                      <div class="card-body">
                                          <!-- Component Preview-->
                                          <div class="sbp-preview">
                                              <div class="sbp-preview-content">
                                                  <form method="post" action="" >
                                                      <div class="mb-3">
                                                          <label for="exampleFormControlInput1">Birim Adı Giriniz</label>
                                                          <input class="form-control" id="birimadi" name="birimadi" type="text" placeholder="Aylık" required />
                                                      </div>
                                                      <div class="mb-3">
                                                          <label for="birim_saat">Ders Saati</label>
                                                          <input class="form-control" id="birim_saat" name="birim_saat" type="number" placeholder="Örn: 24" required />
                                                      </div>
                                                      <div class="mb-0">
                                                          <button class="btn btn-primary" type="submit" name="ekle">Ekle</button>
                                                      </div>
                                                  </form>
                                              </div>
                                              <div class="sbp-preview-text">Birim listesinde olan alan adı girmeyiniz.</div>
                                          </div>
                                      </div>
                                  </div>
                              </div>

                                <div class="card card-header-actions mb-4" id="liste">
                                  <div class="card-header">
                                    Birim Listesi
                                  </div>
                                  <div class="card-body px-0">
                                      <?php foreach ($birims as $birimss ) { ?>
                                       <div class="d-flex align-items-center justify-content-between px-4">
                                          <div class="d-flex align-items-center">
                                              <div class="ms-4">
                                                  <div class="small">
                                                      <?= $birimss['birim_adi']; ?>
                                                      — <b><?= (int)$birimss['birim_saat']; ?> saat</b>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="ms-4 small">
                                              <div class="badge bg-light text-danger me-3">Düzenlemek istediğiniz birimi silip tekrardan ekleyiniz</div>
                                              <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $birimss['birim_id']; ?>)">Sil</a>
                                          </div>
                                      </div>
                                      <hr>
                                      <?php } ?>


                                  </div>
                              </div>
                            </div>

                            <!-- Sticky Navigation-->
                            <div class="col-lg-3">
                                <div class="nav-sticky">
                                    <div class="card">
                                        <div class="card-body">
                                            <ul class="nav flex-column" id="stickyNav">
                                                <li class="nav-item"><a class="nav-link" href="#ekle">Birim Ekle</a></li>
                                                <li class="nav-item"><a class="nav-link" href="#liste">Birim Listesi</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
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


        <script>
          function confirmDelete(id) {
              Swal.fire({
                  title: 'Emin misiniz?',
                  text: "Bu işlemi geri alamazsınız!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#d33',
                  cancelButtonColor: '#3085d6',
                  confirmButtonText: 'Evet, sil!',
                  cancelButtonText: 'Hayır, iptal et'
              }).then((result) => {
                  if (result.isConfirmed) {
                      window.location.href = "delete/birim-sil.php?sil=" + id;
                  }
              });
          }
      </script>
      <?php
        if (isset($_SESSION['delete_status']) && isset($_SESSION['delete_message'])) {
            echo "<script>
                Swal.fire({
                    title: '{$_SESSION['delete_message']}',
                    icon: '{$_SESSION['delete_status']}',
                    confirmButtonText: 'Tamam'
                });
            </script>";

            // Session mesajlarını temizle ki sayfa her yenilendiğinde tekrar gösterilmesin
            unset($_SESSION['delete_status']);
            unset($_SESSION['delete_message']);
        }
        ?>

        <?php
        if($_POST) { //Post Kontrolüm
            $columns = ['birim_adi','ders_saat'];
            $values  = [$_POST['birimadi'], $_POST['birim_saat']];
            $response = $Ydil->newInsert("birim",$columns,$values);
          $deger = preg_replace('/\s+/', '', $response['status']);
          if ($deger == 1) {
              echo '<script>
                  Swal.fire({
                      title: "Başarılı",
                      text: "Veritabanına alan bilgisi eklendi",
                      icon: "success",
                      confirmButtonText: "Tamam"
                  }).then(() => {
                      window.location.href = "/birim-bilgisi.php";
                  });
              </script>';
          } else {
              echo '<script>
                  Swal.fire({
                      title: "Başarısız",
                      text: "Veritabanına alan bilgisi eklenmedi",
                      icon: "error",
                      confirmButtonText: "Tamam"
                  }).then(() => {
                      window.location.href = "/birim-bilgisi.php";
                  });
              </script>';
          }


        }
        ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.17.1/components/prism-core.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.17.1/plugins/autoloader/prism-autoloader.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
        <script src="js/litepicker.js"></script>
    </body>
</html>
