<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

if (empty($_SESSION) ) {
  header("Location: giris.php");
   exit;
}elseif (!empty($_SESSION) and $_SESSION['sifrevar'] == 'var') {
  header("Location: anasayfa.php");
   exit;
}

$mesaj = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["password"]) && isset($_POST["confirm_password"])) {
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);

        // Şifreyi güvenli bir şekilde hash’leme
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Kullanıcı ID'si oturumdan alınmalı (örneğin, giriş yapmış kullanıcının ID'si)
        if (!isset($_SESSION['user_id'])) {
            $mesaj ='<small style="color: red;">Oturum açık değil!</small>';
            header("Location: giris.php");
        } else {
            $user_id = $_SESSION['user_id'];
            $idKey = 'id';
            $options = ['cost' => 11 ];
            $sifre =  password_hash($password, PASSWORD_BCRYPT, $options);
            $columns[]='sifre';
            $columns[]=$idKey;

            $values[]=$sifre;
            $values[]=$user_id;


             $kullaniciss = $Ydil->newUpdate("`kullanici_giris`",$columns,$values,$idKey);

            if ($kullaniciss['status'] == '1') {
              $mesaj ='<small style="color: green;">Şifreniz başarıyla oluşturuldu! Anasayfaya yönlendiriliyorsunuz</small>';
              echo '<meta http-equiv="refresh" content="2;url=index.php">';
            } else {
                $mesaj ='<small style="color: red;">Şifre kaydedilirken hata oluştu!</small>';
            }
        }
    }
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
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container-xl px-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-7">
                                <!-- Basic registration form-->
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header justify-content-center"><h3 class="fw-light my-4">Uygulama şifresi oluştur</h3></div>
                                    <form id="passwordForm" action="" method="POST">
                                      <div class="card-body">
                                          <div class="row gx-3">
                                              <div class="col-md-6">
                                                  <div class="mb-3">
                                                      <label class="small mb-1" for="inputFirstName">Şifre</label>
                                                      <input class="form-control" id="inputFirstName" type="password" name="password" placeholder="Şifrenizi girin" required />
                                                  </div>
                                              </div>
                                              <div class="col-md-6">
                                                  <div class="mb-3">
                                                      <label class="small mb-1" for="inputLastName">Şifre (Tekrar)</label>
                                                      <input class="form-control" id="inputLastName" type="password" name="confirm_password" placeholder="Şifrenizi tekrar girin" required />
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="mb-3">
                                              <small id="passwordMessage" style="color: red;"></small>
                                          </div>
                                      </div>
                                      <div class="card-footer text-center">
                                          <button type="submit" class="btn btn-primary btn-block">Şifre Oluştur</button>
                                      </div>
                                      <div class="mb-3">
                                            <?php echo $mesaj; ?>
                                      </div>
                                  </form>

                                  <script>
                                  document.addEventListener("DOMContentLoaded", function() {
                                      const passwordInput = document.getElementById("inputFirstName");
                                      const confirmPasswordInput = document.getElementById("inputLastName");
                                      const message = document.getElementById("passwordMessage");
                                      const form = document.getElementById("passwordForm");

                                      function checkPasswords() {
                                          if (passwordInput.value === "" || confirmPasswordInput.value === "") {
                                              message.style.color = "red";
                                              message.textContent = "Şifre alanları boş olamaz!";
                                          } else if (passwordInput.value === confirmPasswordInput.value) {
                                              message.style.color = "green";
                                              message.textContent = "Şifreler eşleşiyor!";
                                          } else {
                                              message.style.color = "red";
                                              message.textContent = "Şifreler uyuşmuyor!";
                                          }
                                      }

                                      passwordInput.addEventListener("input", checkPasswords);
                                      confirmPasswordInput.addEventListener("input", checkPasswords);

                                      form.addEventListener("submit", function(event) {
                                          if (passwordInput.value !== confirmPasswordInput.value) {
                                              event.preventDefault();
                                              alert("Şifreler uyuşmuyor, lütfen kontrol edin!");
                                          }
                                      });
                                  });
                                  </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="footer-admin mt-auto footer-dark">
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
    </body>
</html>
