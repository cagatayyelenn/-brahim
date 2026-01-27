<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

  
$alanlar="SELECT * FROM `alan` ORDER BY `alan`.`alan_id` ASC";
$alans=$Ydil->get($alanlar);

$donemler="SELECT * FROM `donem` ORDER BY `donem`.`donem_id` DESC";
$donems=$Ydil->get($donemler);

$gruplar="SELECT * FROM `grup` ORDER BY `grup`.`grup_id` ASC";
$grups=$Ydil->get($gruplar);

$siniflar="SELECT * FROM `sinif` ORDER BY `sinif`.`sinif_id` ASC";
$sinifs=$Ydil->get($siniflar);


$iller="SELECT * FROM `il` ORDER BY `il`.`il_id` ASC";
$ils=$Ydil->get($iller);

$ogrenciler="SELECT ogrenci_id FROM `ogrenci` ORDER BY `ogrenci`.`ogrenci_id` DESC";
$ogrencis=$Ydil->getone($ogrenciler);

if(empty($ogrencis)){
  $sayi = "100";

}else {
  $sayi = $ogrencis['ogrenci_id']+101;
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
        <title>Öğrenci Kayıt Alanı - NGLS Yabancı Dil Dünyası</title>
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
                                             Maltepe Şubesi Öğrenci Kayıt
                                        </h1>
                                    </div>
                                    <div class="col-12 col-xl-auto mb-3">
                                        <a class="btn btn-sm btn-light text-primary" href="ogrenci-listesi.php">
                                            <i class="me-1" data-feather="arrow-left"></i>
                                            Öğrenci Listesine Geri Dön
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- Main page content-->
                    <div class="container-xl px-4 mt-4">
                        <form method="post" action=""  id="saveStudentForm">
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">Öğrenci Bilgileri</div>
                                    <div class="card-body">
                                      <div class="mb-3">
                                          <label class="small mb-1" for="inputogrencinumara">Öğrenci Numarası</label>
                                          <input class="form-control" id="inputogrencinumara"  name="inputogrencinumara" type="text" placeholder="<?= $sayi ?>" value="<?= $sayi ?>" />
                                      </div>
                                      <div class="row gx-3 mb-3">
                                           <div class="col-md-8">
                                              <label class="small mb-1" for="inputtc">Öğrenci Tc Numarası</label>
                                              <input class="form-control" id="inputtc" name="inputtc" type="text" placeholder="Lütfen Tc Kimlik Numarası Giriniz" maxlength="11" pattern="[0-9]{11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                                          </div>
                                           <div class="col-md-4" style="padding-top: 40px;">
                                              <input class="form-check-input" id="hazirtc" type="checkbox" />
                                              <label class="form-check-label" for="hazirtc">Hazır Tc</label>
                                          </div>
                                      </div>
                                      <div class="row gx-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputogrenciadi">Öğrenci Adı</label>
                                                <input class="form-control" id="inputogrenciadi" name="inputogrenciadi" type="text" placeholder="Lütfen Adınızı Giriniz" value="" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputogrencisoyadi">Öğrenci Soyadı</label>
                                                <input class="form-control" id="inputogrencisoyadi" name="inputogrencisoyadi" type="text" placeholder="Lütfen Soyadınızı Giriniz" value="" />
                                            </div>
                                      </div>
                                      <div class="mb-3">
                                          <label class="small mb-1" for="inputogrencitel">Öğrenci Telefonu</label>
                                          <input class="form-control" id="inputogrencitel" name="inputogrencitel" type="text" placeholder="Öğrenciye Telefonunu Giriniz" value="" />
                                      </div>
                                      <div class="mb-3">
                                            <label class="small mb-1" for="inputogrencimail">Öğrenci Mail Adresi</label>
                                            <input class="form-control" id="inputogrencimail" name="inputogrencimail" type="email" placeholder="Öğrenciye Mail Adresi Giriniz" value="" />
                                        </div>
                                      <div class="row gx-3 mb-3">
                                          <div class="col-md-6">
                                            <label class="small mb-1">Cinsiyet</label>
                                            <select class="form-select" name="ogrencicinsiyet" aria-label="Cinsiyet Seçimi"  >
                                              <option selected disabled value="">Cinsiyet Seçiniz</option>
                                              <option value="1">Erkek</option>
                                              <option value="0">Kadın</option>
                                            </select>
                                          </div>
                                          <div class="col-md-6">
                                              <label class="small mb-1" for="inputogrencidogumtar">Öğrenci Doğum Tarihi</label>
                                              <input class="form-control" id="inputogrencidogumtar" name="inputogrencidogumtar" type="date" value="" />
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">Kurs Bilgileri</div>
                                    <div class="card-body">
                                      <div class="row gx-3 mb-3">
                                        <div class="col-md-6">
                                          <label class="small mb-1">Dönem</label>
                                          <select class="form-select" name="ogrencidonem" aria-label="Dönem Seçimi"  >
                                            <option selected disabled value="0">Dönem Seçiniz</option>
                                            <?php foreach ($donems as $donemss ) {  ?>
                                            <option value="<?= $donemss['donem_id']; ?>"><?= $donemss['donem_adi']; ?></option>
                                            <?php } ?>
                                          </select>
                                        </div>
                                        <div class="col-md-6">
                                          <label class="small mb-1">Sınıf</label>
                                          <select class="form-select" name="ogrencisinif" aria-label="Sınıf Seçimi"  >
                                            <option selected disabled value="0">Sınıf Seçiniz</option>
                                            <?php foreach ($sinifs as $sinifss ) {  ?>
                                            <option value="<?= $sinifss['sinif_id']; ?>"><?= $sinifss['sinif_adi']; ?></option>
                                            <?php } ?>
                                          </select>
                                        </div>
                                      </div>
                                      <div class="row gx-3 mb-3">

                                        <div class="col-md-6">
                                          <label class="small mb-1">Grup</label>
                                          <select class="form-select" name="ogrencigrup" aria-label="Grup Seçimi"  >
                                            <option selected disabled value="">Grup Seçiniz</option>
                                            <?php foreach ($grups as $grupss ) {  ?>
                                            <option value="<?= $grupss['grup_id']; ?>"><?= $grupss['grup_adi']; ?></option>
                                            <?php } ?>
                                          </select>
                                        </div>
                                          <div class="col-md-6">
                                              <label class="small mb-1">Alan</label>
                                              <select class="form-select" name="ogrencialan" aria-label="Alan Seçimi"  >
                                                  <option selected disabled value="">Alan Seçiniz</option>
                                                  <?php foreach ($alans as $alanss ) {  ?>
                                                      <option value="<?= $alanss['alan_id']; ?>"><?= $alanss['alan_adi']; ?></option>
                                                  <?php } ?>
                                              </select>
                                          </div>
                                      </div>
                                      <div class="row gx-3 mb-3">
                                          <div class="col-md-6">
                                              <label class="small mb-1">İl</label>
                                              <select class="form-select" name="ogrenciil" id="ogrenciil" aria-label="İl Seçimi"  >
                                                  <option selected disabled value="">İl Seçiniz</option>
                                                  <?php foreach ($ils as $ilss ) {  ?>
                                                  <option value="<?= $ilss['il_id']; ?>"><?= $ilss['il_adi']; ?></option>
                                                  <?php } ?>
                                              </select>
                                          </div>
                                          <div class="col-md-6">
                                              <label class="small mb-1">İlçe</label>
                                              <select class="form-select" name="ogrenciilce" id="ogrenciilce" aria-label="İlçe Seçimi"  >
                                                  <option selected disabled value="">İlçe Seçiniz</option>
                                              </select>
                                          </div>
                                      </div>
                                      <div class="mb-3">
                                            <label class="small mb-1" for="inputEmailAddress">Adres</label>
                                            <textarea class="form-control" id="inputAdres" name="ogrenciadres" placeholder="Lütfen Adres Giriniz" rows="8"  ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4  ">
                                 <div class="card mb-4 mb-xl-0">
                                  <div class="card-header">Veli Bilgisi</div>
                                  <div class="card-body">
                                    <div class="mb-3">
                                       <label class="small mb-1">Veli Seçiniz</label>
                                       <div class="form-check">
                                           <input class="form-check-input" type="radio" name="veli" id="anne" value="anne"   />
                                           <label class="form-check-label" for="anne">Anne</label>
                                       </div>
                                       <div class="form-check">
                                           <input class="form-check-input" type="radio" name="veli" id="baba" value="baba"   />
                                           <label class="form-check-label" for="baba">Baba</label>
                                       </div>
                                     </div>
                                    <div class="mb-3">
                                        <label class="small mb-1" for="inputvelitc">Veli Tc Bilgisi</label>
                                        <input class="form-control" id="inputvelitc" name="inputvelitc" type="text" placeholder="Veli Tc Giriniz" value="" />
                                    </div>
                                    <div class="row gx-3 mb-3">
                                        <!-- Form Group (first name)-->
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="inputveliadi">Veli Adı </label>
                                            <input class="form-control" id="inputveliadi" name="inputveliadi" type="text" placeholder="Veli Adı Giriniz" value="" />
                                        </div>
                                        <!-- Form Group (last name)-->
                                        <div class="col-md-6">
                                            <label class="small mb-1" for="inputvelisoyadi">Veli Soyadı </label>
                                            <input class="form-control" id="inputvelisoyadi" name="inputvelisoyadi" type="text" placeholder="Veli Soyadı Giriniz" value="" />
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small mb-1" for="inputvelitel">Veli Telefon Bilgisi</label>
                                        <input class="form-control" id="inputvelitel" name="inputvelitel" type="text" placeholder="Veli Telefon Giriniz" value="" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="small mb-1" for="inputvelimail">Veli Mail Adresi Bilgisi</label>
                                        <input class="form-control" id="inputvelimail" name="inputvelimail" type="email" placeholder="Veli Mail Adresi Giriniz" value="" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="small mb-1">Veli Adres Bilgisi Öğrenci İle Aynı</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="veliadres" id="adresevet" value="evet"   onclick="toggleVeliBilgisi()" />
                                            <label class="form-check-label" for="adresevet">Evet</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="veliadres" id="adreshayir" value="hayir"   onclick="toggleVeliBilgisi()" />
                                            <label class="form-check-label" for="adreshayir">Hayır</label>
                                        </div>
                                    </div>
                                  </div>
                                </div>
                            </div>
                            <!-- Gizlenecek/Açılacak Veli Bilgisi Alanı -->
                            <div class="col-xl-12 mb-3" id="veliBilgisi" style="display: none;">
                                 <div class="card mb-4 mb-xl-0">
                                  <div class="card-header"> Veli Adres Bilgisi</div>
                                  <div class="card-body">
                                    <div class="row gx-3  ">
                                        <label class="small mb-1" for="inputogrenciadi">Veli Adresi</label>
                                        <textarea class="form-control" id="veliAdres" name="veliAdres" placeholder="Lütfen Adres Giriniz" rows="8" ></textarea>
                                     </div>
                                  </div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                 <div class="card mb-4 mb-xl-0">
                                  <div class="card-header"> Kayıt İşlemi</div>
                                  <div class="card-body">
                                      <input id="action_type" type="hidden" name="action_type" value="add"/>
                                     <button class="btn btn-primary" id="saveCustomerButton"  type="submit">Öğrenciyi Kaydet</button>
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

          $columns[]='ogrenci_numara';
          $columns[]='ogrenci_tc';
          $columns[]='ogrenci_adi';
          $columns[]='ogrenci_soyadi';
          $columns[]='ogrenci_tel';
          $columns[]='ogrenci_mail';
          $columns[]='ogrenci_cinsiyet';
          $columns[]='ogrenci_dogumtar';
          $columns[]='donem_id';
          $columns[]='sinif_id';
          $columns[]='grup_id';
          $columns[]='alan_id';
          $columns[]='il_id';
          $columns[]='ilce_id';
          $columns[]='ogrenci_adres';
          $columns[]='per_id';
          $columns[]='sube_id';


          $values[]=$_POST['inputogrencinumara'];
          $values[]=$_POST['inputtc'];
          $values[]=$_POST['inputogrenciadi'];
          $values[]=$_POST['inputogrencisoyadi'];
          $values[]=$_POST['inputogrencitel'];
          $values[]=$_POST['inputogrencimail'];
          $values[]=$_POST['ogrencicinsiyet'];
          $values[]=$_POST['inputogrencidogumtar'];
          $values[]=$_POST['ogrencidonem'];
          $values[]=$_POST['ogrencisinif'];
          $values[]=$_POST['ogrencigrup'];
          $values[]=$_POST['ogrencialan'];
          $values[]=$_POST['ogrenciil'];
          $values[]=$_POST['ogrenciilce'];
          $values[]=$_POST['ogrenciadres'];
          $values[]=$_SESSION['user_id'];
          $values[]=$_SESSION['subedurum'];

          $ogrResp=$Ydil->newInsert("ogrenci",$columns,$values);

          $uid=@$ogrResp['id'];

            if (!empty($_POST['veliadres']) && $_POST['veliadres'] === 'evet') {
                $ilcesid = (int)$_POST['ogrenciilce'];
                $ilcevt  = "SELECT il.il_adi, ilce.ilce_adi 
                FROM il 
                INNER JOIN ilce ON il.il_id = ilce.il_id 
                WHERE ilce_id = {$ilcesid}";
                $ilcev   = $Ydil->getone($ilcevt);

                $vadres = $_POST['ogrenciadres']." - ".$ilcev['ilce_adi']." / ".$ilcev['il_adi'];
            }
            elseif (!empty($_POST['veliadres'])) {
                $vadres = $_POST['veliadres'];
            }
            else {
                $vadres = ''; // boş gelirse burası çalışır
            }

          $columnss[]='ogrenci_id';
          $columnss[]='veli_adi';
          $columnss[]='veli_soyadi';
          $columnss[]='veli_tc';
          $columnss[]='veli_tel';
          $columnss[]='veli_mail';
          $columnss[]='veli_adres';

          $valuess[]=$uid;
          $valuess[]=$_POST['inputveliadi'];
          $valuess[]=$_POST['inputvelisoyadi'];
          $valuess[]=$_POST['inputvelitc'];
          $valuess[]=$_POST['inputvelitel'];
          $valuess[]=$_POST['inputvelimail'];
          $valuess[]=$vadres;

          $veliResp=$Ydil->newInsert("veli",$columnss,$valuess);

          $columnsss[]='ad';
          $columnsss[]='soyad';
          $columnsss[]='mail_adres';
          $columnsss[]='telefon';
          $columnsss[]='sifre';
          $columnsss[]='kisi';
          $columnsss[]='kisi_id';

          $valuesss[]=$_POST['inputogrenciadi'];
          $valuesss[]=$_POST['inputogrencisoyadi'];
          $valuesss[]=$_POST['inputogrencimail'];
          $valuesss[]=$_POST['inputogrencitel'];
          $valuesss[]='0';
          $valuesss[]='ogrenci';
          $valuesss[]=$uid;


          $userResp=$Ydil->newInsert("kullanici_giris",$columnsss,$valuesss);


          if ($userResp['status'] == 1) {
              // Kayıt başarılı
              $_SESSION['student_id'] = $uid;
              $message = 'Kayıt başarılı, sözleşme sayfasına yönlendiriliyorsunuz.';
              $redirect = 'yenisozlesme.php'; // Yönlendirme yapılacak sayfa
          } else {
              // Hata durumu
              $message = $userResp['message']; // Hata mesajı
              $redirect = ''; // Hata durumunda yönlendirme yapılmayacak
          }
           if (isset($message)): ?>
           <script>
             // JavaScript ile mesajı ve yönlendirmeyi göster
             window.onload = function() {
                 <?php if ($userResp['status'] == 1): ?>
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

        <script>
            $(document).ready(function() {
                $('#ogrenciil').change(function() {
                    var ilId = $(this).val();

                    if (ilId) {
                        $.ajax({
                            url: 'get_ilceler.php',
                            type: 'POST',
                            data: { il_id: ilId },
                            success: function(data) {
                                console.log("AJAX Yanıtı:", data);  // Sunucudan gelen yanıtı konsola yazdır
                                $('#ogrenciilce').html(data);  // İlçe seçeneğini güncelle
                            },
                            error: function(xhr, status, error) {
                                console.log("AJAX Hatası:", error);  // Eğer AJAX isteği başarısızsa, hatayı yazdır
                            }
                        });
                    } else {
                        $('#ogrenciilce').html('<option selected disabled>İlçe Seçiniz</option>');
                    }
                });
            });
        </script>
        <script>
            function toggleVeliBilgisi() {
                var veliBilgisi = document.getElementById("veliBilgisi");
                var adresevet = document.getElementById("adresevet").checked;

                if (adresevet) {
                    veliBilgisi.style.display = "none"; // Evet seçilirse gizle
                } else {
                    veliBilgisi.style.display = "block"; // Hayır seçilirse göster
                }
            }
        </script>
        <script>
            document.getElementById("hazirtc").addEventListener("change", function() {
                let tcField = document.getElementById("inputtc");
                if (this.checked) {
                    tcField.value = "11111111111"; // Checkbox seçilirse TC alanına 11 haneli '1' yaz
                } else {
                    tcField.value = ""; // Checkbox kaldırılırsa alanı boşalt
                }
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables/datatables-simple-demo.js"></script>
    </body>
</html>
