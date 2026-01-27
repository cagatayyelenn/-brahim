<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$alanlar="SELECT * FROM `alan` ORDER BY `alan`.`alan_id` ASC";
$alans=$Ydil->get($alanlar);

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Öğretmen Kayıt Alanı - NGLS Yabancı Dil Dünyası</title>
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
                                             Öğretmen Kayıt
                                        </h1>
                                    </div>
                                    <div class="col-12 col-xl-auto mb-3">
                                        <a class="btn btn-sm btn-light text-primary" href="ogretmen-listesi.php">
                                            <i class="me-1" data-feather="arrow-left"></i>
                                            Öğretmen Listesine Geri Dön
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
                                    <div class="card-header">Öğretmen Bilgileri</div>
                                    <div class="card-body">
                                      <div class="row gx-3 mb-3">
                                           <div class="col-md-8">
                                              <label class="small mb-1" for="inputtc">Öğretmen Tc Numarası</label>
                                              <input class="form-control" id="inputtc" name="inputtc" type="text" placeholder="Lütfen Tc Kimlik Numarası Giriniz" maxlength="11" pattern="[0-9]{11}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                                          </div>
                                      </div>
                                      <div class="row gx-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputogretmenadi">Öğretmen Adı</label>
                                                <input class="form-control" id="inputogretmenadi" name="inputogretmenadi" type="text" placeholder="Lütfen Adınızı Giriniz" value="" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="inputogretmensoyadi">Öğretmen Soyadı</label>
                                                <input class="form-control" id="inputogretmensoyadi" name="inputogretmensoyadi" type="text" placeholder="Lütfen Soyadınızı Giriniz" value="" />
                                            </div>
                                      </div>
                                      <div class="mb-3">
                                          <label class="small mb-1" for="inputogretmentel">Öğretmen Telefonu</label>
                                          <input class="form-control" id="inputogretmentel" name="inputogretmentel" type="text" placeholder="Lütfen Telefonunu Giriniz" value="" />
                                      </div>
                                      <div class="mb-3">
                                            <label class="small mb-1" for="inputogretmenmail">Öğretmen Mail Adresi</label>
                                            <input class="form-control" id="inputogretmenmail" name="inputogretmenmail" type="email" placeholder="Lütfen Mail Adresi Giriniz" value="" />
                                        </div>
                                      <div class="row gx-3 mb-3">
                                          <div class="col-md-6">
                                            <label class="small mb-1">Cinsiyet</label>
                                            <select class="form-select" name="ogretmencinsiyet" aria-label="Cinsiyet Seçimi"  >
                                              <option selected disabled value="">Cinsiyet Seçiniz</option>
                                              <option value="1">Erkek</option>
                                              <option value="0">Kadın</option>
                                            </select>
                                          </div>
                                          <div class="col-md-6">
                                              <label class="small mb-1" for="inputogretmendogumtar">Öğretmen Doğum Tarihi</label>
                                              <input class="form-control" id="inputogretmendogumtar" name="inputogretmendogumtar" type="date" value="" />
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">Durum Bilgileri</div>
                                    <div class="card-body">
                                      <div class="mb-3">
                                          <label class="small mb-1">Alan</label>
                                          <select class="form-select" name="ogretmenalan" aria-label="Alan Seçimi">
                                              <option selected disabled value="">Alan Seçiniz</option>
                                              <?php foreach ($alans as $alanss): ?>
                                                  <option value="<?= $alanss['alan_id']; ?>"><?= htmlspecialchars($alanss['alan_adi']); ?></option>
                                              <?php endforeach; ?>
                                          </select>
                                      </div>
                                      <div class="mb-3">
                                            <label class="small mb-1" for="inputEmailAddress">Adres</label>
                                            <textarea class="form-control" id="inputAdres" name="ogretmenadres" placeholder="Lütfen Adres Giriniz" rows="8"  ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4  ">
                                 <div class="card mb-4 mb-xl-0">
                                  <div class="card-header">Yetki Bilgisi</div>
                                  <div class="card-body">
                                    <div class="mb-3">
                                        <label class="small mb-1">Açıklama</label>
                                        <textarea class="form-control" id="inputAdres" name="ogretmenadres" placeholder="Lütfen Adres Giriniz" rows="8"  ></textarea>
                                    </div>
                                      <!-- Eğitim Türü + Ücretler -->
                                      <div class="mb-3">
                                          <label class="small mb-1 d-block">Eğitim Türü</label>

                                          <!-- ONLINE -->
                                          <div class="form-check mb-2">
                                              <input class="form-check-input" type="checkbox"
                                                     id="etOnline" name="egitim_turu[]" value="online"
                                                     data-target="#grpOnline" data-input="#ucretOnline">
                                              <label class="form-check-label" for="etOnline">Online</label>
                                          </div>
                                          <div id="grpOnline" class="row g-2 align-items-center d-none">
                                              <div class="col-auto">
                                                  <label for="ucretOnline" class="col-form-label">Ücret</label>
                                              </div>
                                              <div class="col">
                                                  <div class="input-group">
                                                      <span class="input-group-text">₺</span>
                                                      <input type="number" step="0.01" min="0" class="form-control"
                                                             id="ucretOnline" name="ucret_online" placeholder="Online ücret"
                                                             inputmode="decimal" disabled>
                                                  </div>
                                              </div>
                                          </div>

                                          <!-- YÜZYÜZE -->
                                          <div class="form-check mt-3 mb-2">
                                              <input class="form-check-input" type="checkbox"
                                                     id="etYuzYuze" name="egitim_turu[]" value="yuz_yuze"
                                                     data-target="#grpYuzYuze" data-input="#ucretYuzYuze">
                                              <label class="form-check-label" for="etYuzYuze">Yüzyüze</label>
                                          </div>
                                          <div id="grpYuzYuze" class="row g-2 align-items-center d-none">
                                              <div class="col-auto">
                                                  <label for="ucretYuzYuze" class="col-form-label">Ücret</label>
                                              </div>
                                              <div class="col">
                                                  <div class="input-group">
                                                      <span class="input-group-text">₺</span>
                                                      <input type="number" step="0.01" min="0" class="form-control"
                                                             id="ucretYuzYuze" name="ucret_yuz_yuze" placeholder="Yüzyüze ücret"
                                                             inputmode="decimal" disabled>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                 <div class="card mb-4 mb-xl-0">
                                  <div class="card-header"> Kayıt İşlemi</div>
                                  <div class="card-body">
                                      <input id="action_type" type="hidden" name="action_type" value="add"/>
                                     <button class="btn btn-primary"  type="submit">Öğretmeni Kaydet</button>
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


          $columns[]='ogretmen_tc';
          $columns[]='ogretmen_adi';
          $columns[]='ogretmen_soyadi';
          $columns[]='ogretmen_tel';
          $columns[]='ogretmen_mail';
          $columns[]='ogretmen_cinsiyet';
          $columns[]='ogretmen_dogumtar';
          $columns[]='alan_id';
          $columns[]='ogretmen_adres';


          $values[]=$_POST['inputtc'];
          $values[]=$_POST['inputogretmenadi'];
          $values[]=$_POST['inputogretmensoyadi'];
          $values[]=$_POST['inputogretmentel'];
          $values[]=$_POST['inputogretmenmail'];
          $values[]=$_POST['ogretmencinsiyet'];
          $values[]=$_POST['inputogretmendogumtar'];
          $values[]=$_POST['ogretmenalan'];
          $values[]=$_POST['ogretmenadres'];

          $response=$Ydil->newInsert("ogretmen",$columns,$values);



          $columnsss[]='ad';
          $columnsss[]='soyad';
          $columnsss[]='mail_adres';
          $columnsss[]='telefon';
          $columnsss[]='sifre';
          $columnsss[]='kisi';

          $valuesss[]=$_POST['inputogretmenadi'];
          $valuesss[]=$_POST['inputogretmensoyadi'];
          $valuesss[]=$_POST['inputogretmenmail'];
          $valuesss[]=$_POST['inputogretmentel'];
          $valuesss[]='0';
          $valuesss[]='ogretmen';


          $response=$Ydil->newInsert("kullanici_giris",$columnsss,$valuesss);


          if ($response['status'] == 1) {
              // Kayıt başarılı
              $_SESSION['student_id'] = $uid;
              $message = 'Kayıt başarılı, öğretmen listesine sayfasına yönlendiriliyorsunuz.';
              $redirect = 'ogretmen-listesi.php'; // Yönlendirme yapılacak sayfa
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
        <script>
            function toggleFee(checkbox) {
                const groupSel = checkbox.getAttribute('data-target');
                const inputSel = checkbox.getAttribute('data-input');
                const group = document.querySelector(groupSel);
                const input = document.querySelector(inputSel);

                if (!group || !input) return;

                if (checkbox.checked) {
                    group.classList.remove('d-none');
                    input.disabled = false;
                    input.focus();
                } else {
                    group.classList.add('d-none');
                    input.value = '';
                    input.disabled = true;
                }
            }

            // Başlatma ve event binding
            document.querySelectorAll('input.form-check-input[type="checkbox"][data-target][data-input]')
                .forEach(cb => {
                    cb.addEventListener('change', () => toggleFee(cb));
                    // Sayfa yüklenince mevcut durumu uygula (örn. POST sonrası geri dönüş)
                    toggleFee(cb);
                });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script src="js/scripts.js"></script>
    </body>
 

</html>
