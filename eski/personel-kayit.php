<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$subeler="SELECT * FROM `sube` ORDER BY `sube`.`sube_id` DESC ";
$subes=$Ydil->get($subeler);


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Personel Kayıt Alanı - NGLS Yabancı Dil Dünyası</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
         <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
         <script type="text/javascript" src="js/sweetalert2.all.min.js"></script>
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
                                            <div class="page-header-icon"><i data-feather="user-plus"></i></div>
                                             Personel Kayıt
                                        </h1>
                                    </div>
                                    <div class="col-12 col-xl-auto mb-3">
                                        <a class="btn btn-sm btn-light text-primary" href="personel-listesi.php">
                                            <i class="me-1" data-feather="arrow-left"></i>
                                            Personel Listesine Geri Dön
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- Main page content-->
                    <div class="container-xl px-4 mt-4">
                        <form method="post" action="">
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">Personel Bilgileri</div>
                                    <div class="card-body">
                                      <div class="row gx-3 mb-3">
                                           <div class="col-md-8">
                                              <label class="small mb-1" for="inputtc">Personel Tc Numarası</label>
                                              <input class="form-control" id="inputtc" name="inputtc" type="text" placeholder="Lütfen Tc Kimlik Numarası Giriniz" maxlength="11" pattern="[0-9]{11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"  />
                                          </div>
                                      </div>
                                      <div class="row gx-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputpersoneladi">Personel Adı</label>
                                                <input class="form-control" id="inputpersoneladi" name="inputpersoneladi" type="text" placeholder="Lütfen Adınızı Giriniz" value="" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputpersonelsoyadi">Personel Soyadı</label>
                                                <input class="form-control" id="inputpersonelsoyadi" name="inputpersonelsoyadi" type="text" placeholder="Lütfen Soyadınızı Giriniz" value="" />
                                            </div>
                                      </div>
                                      <div class="mb-3">
                                          <label class="small mb-1" for="inputpersoneltel">Personel Telefonu</label>
                                          <input class="form-control" id="inputpersoneltel" name="inputpersoneltel" type="text" placeholder="Lütfen Telefonunuzu Giriniz" value="" />
                                      </div>
                                      <div class="mb-3">
                                            <label class="small mb-1" for="inputpersonelmail">Personel Mail Adresi</label>
                                            <input class="form-control" id="inputpersonelmail" name="inputpersonelmail" type="email" placeholder="Lütfen Mail Adresi Giriniz" value="" />
                                        </div>
                                      <div class="row gx-3 mb-3">
                                          <div class="col-md-6">
                                            <label class="small mb-1">Cinsiyet</label>
                                            <select class="form-select" name="personelcinsiyet" aria-label="Cinsiyet Seçimi"  >
                                              <option selected disabled value="">Cinsiyet Seçiniz</option>
                                              <option value="1">Erkek</option>
                                              <option value="0">Kadın</option>
                                            </select>
                                          </div>
                                          <div class="col-md-6">
                                              <label class="small mb-1" for="inputpersoneldogumtar">Personel Doğum Tarihi</label>
                                              <input class="form-control" id="inputpersoneldogumtar" name="inputpersoneldogumtar" type="date" value="" />
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">Adres Bilgileri</div>
                                    <div class="card-body">
                                      <div class="mb-3">
                                            <label class="small mb-1" for="inputEmailAddress">Adres</label>
                                            <textarea class="form-control" id="personeladres" name="personeladres" placeholder="Lütfen Adres Giriniz" rows="8"  ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4  ">
                                 <div class="card mb-4 mb-xl-0">
                                  <div class="card-header">Yetki Bilgisi</div>
                                  <div class="card-body">
                                    <div class="mb-3">
                                        <label class="small mb-1">Yetki Durumu</label>

                                        <select class="form-select" name="yetki" aria-label="Alan Seçimi" required>
                                            <option selected disabled value="">Yetki Türü Seçiniz</option>
                                            <option value="1">Admin</option>
                                            <?php
                                            if(!empty($subes)){
                                                foreach($subes as $row){
                                                    echo '<option value="'.$row['sube_id'].'">'.htmlspecialchars($row['sube_adi']).' Şube Personeli</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                  </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                 <div class="card mb-4 mb-xl-0">
                                  <div class="card-header"> Kayıt İşlemi</div>
                                  <div class="card-body">
                                      <input id="action_type" type="hidden" name="action_type" value="add"/>
                                     <button class="btn btn-primary"  type="submit">Personel Kaydet</button>
                                  </div>
                                </div>
                            </div>
                        </div>
                        </form>
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
        <?php
        if($_POST["action_type"]=="add"){

          $columns[]='personel_tc';
          $columns[]='personel_adi';
          $columns[]='personel_soyadi';
          $columns[]='personel_tel';
          $columns[]='personel_mail';
          $columns[]='personel_cinsiyet';
          $columns[]='personel_dogumtar';
          $columns[]='personel_adres';
          $columns[]='yetki';

          $values[]=$_POST['inputtc'];
          $values[]=$_POST['inputpersoneladi'];
          $values[]=$_POST['inputpersonelsoyadi'];
          $values[]=$_POST['inputpersoneltel'];
          $values[]=$_POST['inputpersonelmail'];
          $values[]=$_POST['personelcinsiyet'];
          $values[]=$_POST['inputpersoneldogumtar'];
          $values[]=$_POST['personeladres'];
          $values[]=$_POST['yetki'];

          $response=$Ydil->newInsert("personel",$columns,$values);

          $columnsss[]='ad';
          $columnsss[]='soyad';
          $columnsss[]='mail_adres';
          $columnsss[]='telefon';
          $columnsss[]='sifre';
          $columnsss[]='kisi';
          $columnsss[]='kisi_id';

          $valuesss[]=$_POST['inputpersoneladi'];
          $valuesss[]=$_POST['inputpersonelsoyadi'];
          $valuesss[]=$_POST['inputpersonelmail'];
          $valuesss[]=$_POST['inputpersoneltel'];
          $valuesss[]='0';
          $valuesss[]='personel';
          $valuesss[]=$response['id']; 

          $response=$Ydil->newInsert("kullanici_giris",$columnsss,$valuesss);

            

          if ($response['status'] == 1) {
              $message = 'Kayıt başarılı, personel listesi sayfasına yönlendiriliyorsunuz.';
              $redirect = 'personel-listesi.php'; // Yönlendirme yapılacak sayfa
          } else {
              // Hata durumu
              $message = $response['message']; // Hata mesajı
              $redirect = ''; // Hata durumunda yönlendirme yapılmayacak
          }
           if (isset($message)): ?>
           <script>
             // JavaScript ile mesajı ve yönlendirmeyi göster
             window.onload = function() {
                 <?php if ($response['status'] == 1): ?>
                     // Kayıt başarılı, yönlendirme yapılacak
                     Swal.fire({
                         title: 'Başarılı!',
                         text: '<?php echo $message; ?>',
                         icon: 'success',
                         showConfirmButton: false, // "Tamam" butonunu gizle
                         timer: 3000 // 3 saniye sonra yönlendirme
                     }).then(function() {
                         window.location.href = '<?php echo $redirect; ?>'; // Yönlendirme
                     });
                 <?php else: ?>
                     // Hata durumunda gösterilecek
                     Swal.fire({
                            title: 'Hata!',
                            text: '<?php echo $message; ?>',
                            icon: 'error',
                            confirmButtonText: 'Tamam'
                        });
                 <?php endif; ?>
             };
          </script>

        <?php endif; ?>
      <?php  } ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script src="js/scripts.js"></script>
    </body>
</html>
