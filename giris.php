<?php
include "c/fonk.php";
include "c/config.php";
session_start();

$mesaj  = "";
$mesaj1 = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email_or_phone = trim($_POST['email_or_phone'] ?? '');
    $password       = trim($_POST['password'] ?? '');

    // Kullanıcının e-posta mı, telefon mu girdiğini belirleme
    if (preg_match('/^[\w\.-]+@[\w\.-]+\.\w{2,}$/', $email_or_phone)) {
        $verine = "'".$email_or_phone."'"; // find helper'ınız bu formata alışık görünüyor
        $sutun  = "mail_adres";
    } elseif (preg_match('/^[0-9]{10,15}$/', $email_or_phone)) {
        $verine = $email_or_phone; // telefon sayısal
        $sutun  = "telefon";
    } else {
        $mesaj = '<small style="color: red;">Lütfen geçerli bir e-posta veya telefon numarası giriniz.</small>';
    }

    if (empty($mesaj)) {
        // Kullanıcıyı getir
        $kullaniciss = $Ydil->find("`kullanici_giris`", $sutun, "$verine");

        if (!$kullaniciss) {
            $mesaj = '<small style="color: red;">Sistemde kayıt bulunamadı.</small>';
        } elseif (($kullaniciss['sifre'] ?? '') === '0' && $password === '') {
            // İlk giriş: şifre oluşturma
            $_SESSION['user_id'] = $kullaniciss['kisi_id'];
            header("Location: sifre_olustur.php");
            exit;
        } elseif (!password_verify($password, $kullaniciss['sifre'])) {
            $mesaj1 = '<small style="color: red;">Şifreniz yanlış!</small>';
        } else {


            if ($kullaniciss['kisi'] =="personel" ) {


                $sube_id = intval($kullaniciss['kisi_id']);
                $subeler = "SELECT * FROM `personel` WHERE `personel`.`personel_id` = $sube_id";
                $subess  = $Ydil->getone($subeler);
                $_SESSION['subedurum'] = $subess['yetki'];
            }
            $_SESSION['user_id']  = $kullaniciss['kisi_id'];
            $_SESSION['kisi'] = $kullaniciss['kisi'];

           // Kullanıcı tipine göre yönlendirme
           if ($kullaniciss['kisi'] == 'admin') {
               header("Location: sube.php");
               exit;
           } //elseif ($kullaniciss['kisi'] == 'personel' || $kullaniciss['kisi'] == 'ogrenci') {
              // $mesaj = '<small style="color: red;">Geçersiz kullanıcı tipi!</small>';
              // exit;
           //}
        else {
            header("Location: anasayfa.php");
           }

        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Sqooler </title>
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
                    <div class="col-lg-5">
                        <div class="card shadow-lg border-0 rounded-lg mt-5">
                            <div class="card-header justify-content-center">
                                <h3 class="fw-light my-4">Giriş Alanı</h3>
                            </div>
                            <div class="card-body">
                                <form id="loginForm" action="" method="POST" novalidate>
                                    <div class="mb-3">
                                        <label class="small mb-1" for="inputEmailAddress">Mail Adresi veya Telefon</label>
                                        <input class="form-control" id="inputEmailAddress" type="text" name="email_or_phone" placeholder="Lütfen mail adresi veya telefon numarası giriniz" required />
                                        <div id="validationMessage" class="mt-1" style="color:red;"></div>
                                        <?php echo $mesaj; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small mb-1" for="password">Şifreniz</label>
                                        <input class="form-control" type="password" id="password" name="password" placeholder="Lütfen şifrenizi giriniz" />
                                        <?php echo $mesaj1; ?>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" id="rememberPasswordCheck" type="checkbox" name="remember" />
                                            <label class="form-check-label" for="rememberPasswordCheck">Bilgilerimi Hatırla</label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                        <button type="submit" class="btn btn-primary">Giriş Yap!</button>
                                    </div>
                                </form>
                            </div>

                            <div class="card-footer text-center">
                                <div class="small"><a href="auth-register-basic.html">Bilgilerimi Unuttum</a></div>
                            </div>

                            <div class="alert alert-secondary alert-icon m-3" role="alert">
                                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                <div class="alert-icon-aside">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="alert-icon-content">
                                    <h6 class="alert-heading" style="margin-top: 10px;">İlk defa sisteme girişte şifre alanını boş bırakınız!</h6>
                                </div>
                            </div>

                        </div><!-- card -->
                    </div><!-- col -->
                </div><!-- row -->
            </div><!-- container -->
        </main>
    </div>
    <div id="layoutAuthentication_footer">
        <footer class="footer-admin mt-auto footer-dark">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website 2025</div>
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
    // Basit istemci tarafı format kontrolü
    document.getElementById('loginForm').addEventListener('submit', function(event) {
        let input   = document.getElementById('inputEmailAddress').value.trim();
        let message = document.getElementById('validationMessage');

        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let phonePattern = /^[0-9]{10,15}$/;

        if (!emailPattern.test(input) && !phonePattern.test(input)) {
            message.textContent = "Lütfen geçerli bir e-posta veya telefon numarası giriniz.";
            event.preventDefault();
        } else {
            message.textContent = "";
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
</body>
</html>