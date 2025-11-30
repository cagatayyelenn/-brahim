<?php
error_reporting(E_ALL);

// Aynı zamanda php.ini dosyasındaki ayarları da kod içinde geçersiz kılmak için:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
session_start();
$db = new Ydil();


$mesaj  = "";
$mesaj1 = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email_or_phone = trim($_POST['email_or_phone'] ?? '');
    $password       = $_POST['password'] ?? '';

    // 1) Mail mi, telefon mu?
    $isEmail = (bool)preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email_or_phone);
    $isPhone = (bool)preg_match('/^[0-9]{10,15}$/', $email_or_phone);

    if (!$isEmail && !$isPhone) {
        $mesaj = '<small style="color: red;">Lütfen geçerli bir e-posta veya telefon numarası giriniz.</small>';
    } else {
        $sutun  = $isEmail ? 'eposta' : 'telefon';
        $quoted = $db->conn->quote($email_or_phone); // injection riskini azalt

        // 2) Kullanıcıyı çek (ad & soyad dahil!)
        $sqlUser = " SELECT k.eposta, k.kisi_turu, k.sifre, k.telefon, p.personel_id, p.personel_adi, p.personel_soyadi, p.durum, p.sube_id, p.yetki FROM kullanici_giris1 k INNER JOIN personel1 p ON k.kisi_id = p.personel_id  WHERE {$sutun} = {$quoted}  LIMIT 1 ";
        $user = $db->gets($sqlUser);

        
        if (!$user) {
            // 1) Kullanıcı yok
            $mesaj1 = '<small style="color: red;">Giriş bilgileri yanlış.</small>';
        }
        elseif ($user['sifre'] === '0' || $user['sifre'] === '') {
            // 2) Sistemde şifre boş/0 -> şifre oluşturma
            $_SESSION['kisi_id'] = (int)$user['personel_id'];
            header("Location: sifre-olusturma.php");
            exit;
        }
        elseif ($password === '' || !password_verify($password, $user['sifre'])) {
            // 3) Şifre girilmedi ya da hatalı
            $mesaj1 = '<small style="color: red;">Giriş bilgileri yanlış.</small>';
        }
        elseif ((string)$user['durum'] !== '1') {
            // 4) Personel pasif
            $mesaj1 = '<small style="color: red;">Girmiş olduğunuz personel durumu pasiftir.</small>';
        }
        else {
            // 5) Başarılı giriş
            session_regenerate_id(true);

            $_SESSION['personel_id'] = (int)$user['personel_id'];
            $_SESSION['ad']         = trim(($user['personel_adi'] ?? '').' '.($user['personel_soyadi'] ?? ''));
            $_SESSION['yetki']      = (string)$user['yetki'];      // '1' admin, '2' yönetici
            $_SESSION['sube_id']    = $user['sube_id'] ?? null;    // admin için null/boş olabilir
            $_SESSION['email']      = $user['eposta'] ?? null;

            if ((string)$user['yetki'] === '1') {
                // admin -> şube seçimi
                header("Location: sube.php");
            } else {

                $row = $db->gets("SELECT sube_adi FROM sube WHERE sube_id = :sid LIMIT 1", [':sid' => $user['sube_id']]);
                if ($row && !empty($row['sube_adi'])) {
                    $user['sube_adi'] = $row['sube_adi'];
                    // eğer istersen session'a da yazabiliriz:
                    $_SESSION['sube_adi'] = $row['sube_adi'];
                }
                // yönetici -> anasayfa
                header("Location: index.php");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Sqooler eğitim sistemi">
    <meta name="author" content="Yabancı Dil Dünyası">
    <meta name="robots" content="noindex, nofollow">
    <title>Sqooler Eğitim Sistemi</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <script src="assets/js/theme-script.js" type="text/javascript"></script>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/plugins/tabler-icons/tabler-icons.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/feather.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .pass-group .pass-input { padding-right: 40px; }
        .pass-group .toggle-password { z-index: 3; }
    </style>
</head>

<body class="account-page">

<div class="main-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 mx-auto">
                    <div class="d-flex flex-column justify-content-between vh-100">
                        <div class=" mx-auto p-4 text-center">
                            <img src="assets/img/authentication/authentication-logo.svg" class="img-fluid" alt="Logo">
                        </div>
                        <form id="loginForm" action="giris.php" method="POST" novalidate>
                            <input type="hidden" name="action" value="login">
                            <div class="card">
                                <div class="card-body p-4">
                                    <div class="mb-4">
                                        <h2 class="mb-2">Hoşgeldiniz</h2>
                                        <p class="mb-0">Lütfen giriş yapmak için bilgilerinizi girin</p>
                                    </div>

                                    <div class="mt-4">
                                        <div class="alert alert-primary d-flex align-items-center" role="alert">
                                            <i class="feather-info flex-shrink-0 me-2"></i>
                                            İlk defa sisteme girişte şifre alanını boş bırakınız!
                                        </div>
                                    </div>

                                    <div class="login-or">
                                        <span class="span-or">Or</span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Mail adresin veya telefon numarası</label>
                                        <div class="input-icon mb-3 position-relative">
                                              <span class="input-icon-addon">
                                                <i class="ti ti-mail"></i>
                                              </span>
                                            <input type="text" class="form-control" id="inputEmailPhone" name="email_or_phone" placeholder="ornek@site.com veya 5XXXXXXXXX" required>
                                        </div>
                                        <div id="validationMessage" class="mt-1 text-danger small"><?php echo $mesaj; ?></div>


                                        <label class="form-label">Şifreniz</label>
                                        <div class="pass-group position-relative">
                                            <input type="password" class="pass-input form-control" id="password" name="password"
                                                   placeholder="Şifrenizi girin (ilk girişte boş bırakın)">
                                            <span class="ti toggle-password ti-eye-off"
                                                  data-target="#password"
                                                  style="cursor:pointer; position:absolute; right:3px; top:18px;"></span>
                                        </div>
                                        <?php echo $mesaj1; ?>
                                    </div>

                                    <div class="form-wrap form-wrap-checkbox mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="form-check form-check-md mb-0">
                                                <input class="form-check-input mt-0" type="checkbox" id="remember" name="remember" value="1">
                                            </div>
                                            <label for="remember" class="ms-2 mb-0">Beni Hatırla</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                                    </div>

                                    <div class="text-center">
                                        <h6 class="fw-normal text-dark mb-0">
                                            Şifrenizi unuttuysanız <span class="fw-normal text-success mb-0">Ngls Yabancı Dil Dünyası</span> ile iletişime geçin
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="p-4 text-center">
                            <p class="mb-0 ">Copyright &copy; 2025 - Sqooler Okul Yönetim Sistemi </p>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

<script data-cfasync="false" src="assets/js/jquery-3.7.1.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap.bundle.min.js"></script>
 <script data-cfasync="false" src="assets/js/moment.js"></script>
<script data-cfasync="false" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/tr.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap-datetimepicker.min.js"></script>
<script data-cfasync="false" src="assets/plugins/select2/js/select2.min.js"></script>
<script data-cfasync="false" src="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
<script data-cfasync="false" src="assets/js/feather.min.js"></script>
<script data-cfasync="false" src="assets/js/jquery.slimscroll.min.js"></script>
 <script data-cfasync="false" src="assets/js/script.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Basit istemci tarafı kontrolü (mail veya telefon) — form varsa bağla
        const form = document.getElementById('loginForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                const input   = document.getElementById('inputEmailPhone').value.trim();
                const message = document.getElementById('validationMessage');

                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const phonePattern = /^(?:\+?90\s?0?|0)?\d{10}$/;

                if (!emailPattern.test(input) && !phonePattern.test(input)) {
                    if (message) message.textContent = "Lütfen geçerli bir e-posta veya telefon numarası giriniz.";
                    event.preventDefault();
                } else {
                    if (message) message.textContent = "";
                }
            });
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.toggle-password');
            if (!btn) return; // tıklanan element göz ikonu değil
            const targetSel = btn.getAttribute('data-target');
            const input = document.querySelector(targetSel);
            if (!input) return; // input bulunamadıysa çık
            const icon = btn.querySelector('i');
            if (!icon) return; // ikon bulunamadıysa çık

            // toggle işlemi
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });
</script>
</body>

</html>