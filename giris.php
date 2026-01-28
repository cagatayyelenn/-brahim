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


$mesaj = "";
$mesaj1 = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email_or_phone = trim($_POST['email_or_phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1) Mail mi, telefon mu?
    $isEmail = (bool) preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email_or_phone);
    $isPhone = (bool) preg_match('/^[0-9]{10,15}$/', $email_or_phone);

    if (!$isEmail && !$isPhone) {
        $mesaj = '<small style="color: red;">Lütfen geçerli bir e-posta veya telefon numarası giriniz.</small>';
    } else {
        $sutun = $isEmail ? 'eposta' : 'telefon';
        $quoted = $db->conn->quote($email_or_phone); // injection riskini azalt

        // 2) Kullanıcıyı çek (ad & soyad dahil!)
        $sqlUser = " SELECT k.eposta, k.kisi_turu, k.sifre, k.telefon, p.personel_id, p.personel_adi, p.personel_soyadi, p.durum, p.sube_id, p.yetki FROM kullanici_giris1 k INNER JOIN personel1 p ON k.kisi_id = p.personel_id  WHERE {$sutun} = {$quoted}  LIMIT 1 ";
        $user = $db->gets($sqlUser);


        if (!$user) {
            // 1) Kullanıcı yok
            $mesaj1 = '<small style="color: red;">Giriş bilgileri yanlış.</small>';
        } elseif ($user['sifre'] === '0' || $user['sifre'] === '') {
            // 2) Sistemde şifre boş/0 -> şifre oluşturma
            $_SESSION['kisi_id'] = (int) $user['personel_id'];
            header("Location: sifre-olusturma.php");
            exit;
        } elseif ($password === '' || !password_verify($password, $user['sifre'])) {
            // 3) Şifre girilmedi ya da hatalı
            $mesaj1 = '<small style="color: red;">Giriş bilgileri yanlış.</small>';
        } elseif ((string) $user['durum'] !== '1') {
            // 4) Personel pasif
            $mesaj1 = '<small style="color: red;">Girmiş olduğunuz personel durumu pasiftir.</small>';
        } else {
            // 5) Başarılı giriş (ŞİFRE DOĞRU)
            // Şimdi güvenlik sorusu kontrolüne yönlendiriyoruz.
            session_regenerate_id(true);

            // Henüz tam oturum açmıyoruz, sadece kim olduğunu biliyoruz.
            $_SESSION['temp_user_id'] = (int) $user['personel_id'];

            // Eğer "Beni Hatırla" seçildiyse bunu da taşıyabiliriz ama şimdilik gerek yok,
            // güvenlik sorusunu geçince tekrar bakabiliriz veya orada cookie set ederiz.

            header("Location: guvenlik-kontrol.php");
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
    <meta name="description" content="Preskool - Bootstrap Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, html5, responsive, Projects">
    <meta name="author" content="Dreams technologies - Bootstrap Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <title>Sqooler Eğitim Sistemi</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <!-- Feather CSS -->
    <link rel="stylesheet" href="assets/plugins/icons/feather/feather.css">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="assets/plugins/tabler-icons/tabler-icons.css">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="account-page">

    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <div class="container-fuild">
            <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
                <div class="row">
                    <div class="col-lg-6">
                        <div
                            class="login-background position-relative d-lg-flex align-items-center justify-content-center d-lg-block d-none flex-wrap vh-100 overflowy-auto">
                            <div>
                                <img src="assets/img/authentication/authentication-02.jpg" alt="Img">
                            </div>
                            <div class="authen-overlay-item  w-100 p-4">
                                <h4 class="text-white mb-3">Sqooler'da Yenilikler</h4>
                                <?php
                                // Tüm aktif duyuruları çek
                                $duyurular = $db->finds("duyurular", "durum", 1, ["id", "baslik", "icerik", "tarih"]);

                                if ($duyurular && is_array($duyurular)) {
                                    // Tarihe göre sırala (Yeni tarih en üstte)
                                    usort($duyurular, function ($a, $b) {
                                        return strtotime($b['tarih']) - strtotime($a['tarih']);
                                    });
                                    // İlk 5 tanesini al
                                    $duyurular = array_slice($duyurular, 0, 5);
                                } else {
                                    $duyurular = [];
                                }
                                ?>

                                <?php foreach ($duyurular as $duyuru): ?>
                                    <div
                                        class="d-flex align-items-center flex-row mb-3 justify-content-between p-3 br-5 gap-3 card">
                                        <div>
                                            <h6><?= htmlspecialchars($duyuru['baslik']) ?></h6>
                                            <p class="mb-0 text-truncate"
                                                title="<?= htmlspecialchars($duyuru['icerik']) ?>">
                                                <?= $duyuru['icerik'] ?>
                                            </p>
                                        </div>
                                        <a href="javascript:void(0);"><i class="ti ti-chevrons-right"></i></a>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (empty($duyurular)): ?>
                                    <div class="alert alert-info">Henüz duyuru bulunmamaktadır.</div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                            <div class="col-md-8 mx-auto p-4">
                                <form id="loginForm" action="giris.php" method="POST" novalidate>
                                    <!-- action="giris.php" olarak güncellendi -->
                                    <div>
                                        <div class=" mx-auto mb-5 text-center">
                                            <img src="assets/img/logo.svg" class="img-fluid" alt="Logo">
                                        </div>
                                        <div class="card">
                                            <div class="card-body p-4">
                                                <div class=" mb-4">
                                                    <h2 class="mb-2">Hoşgeldiniz</h2>
                                                    <p class="mb-0">Lütfen giriş yapmak için bilgilerinizi girin</p>
                                                </div>
                                                <div class="mt-4">
                                                    <?php if ($mesaj1): ?>
                                                        <div class="alert alert-danger d-flex align-items-center"
                                                            role="alert">
                                                            <i class="feather-alert-octagon flex-shrink-0 me-2"></i>
                                                            <?= $mesaj1 ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="login-or">
                                                    <span class="span-or">Giriş Yap</span>
                                                </div>
                                                <div class="mb-3 ">
                                                    <label class="form-label">Mail adresi veya telefon</label>
                                                    <div class="input-icon mb-3 position-relative">
                                                        <span class="input-icon-addon">
                                                            <i class="ti ti-mail"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="email_or_phone"
                                                            id="inputEmailPhone"
                                                            placeholder="ornek@site.com veya 5XXXXXXXXX" required>
                                                    </div>
                                                    <div id="validationMessage" class="mt-1 pb-2 text-danger small">
                                                        <?php echo $mesaj; ?>
                                                    </div>

                                                    <label class="form-label">Şifre</label>
                                                    <div class="pass-group">
                                                        <input type="password" class="pass-input form-control"
                                                            name="password" id="password"
                                                            placeholder="Şifrenizi giriniz">
                                                        <span class="ti toggle-password ti-eye-off"
                                                            data-target="#password"></span>
                                                    </div>
                                                </div>

                                                <div class="form-wrap form-wrap-checkbox mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check form-check-md mb-0">
                                                            <input class="form-check-input mt-0" type="checkbox"
                                                                name="remember" id="remember" value="1">
                                                        </div>
                                                        <p class="ms-1 mb-0 ">Beni Hatırla</p>
                                                    </div>
                                                    <div class="text-end ">
                                                        <a href="forgot-password.html" class="link-danger">Şifreni mi
                                                            unuttun?</a>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <button type="submit" class="btn btn-primary w-100">Giriş
                                                        Yap</button>
                                                </div>
                                                <div class="text-center">
                                                    <h6 class="fw-normal text-dark mb-0">Hesabınız yok mu? <a
                                                            href="register.html" class="hover-a "> Kayıt Ol</a>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-5 text-center">
                                            <p class="mb-0 ">Copyright &copy; 2025 - Sqooler Okul Yönetim Sistemi</p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- /Main Wrapper -->

    <!-- jQuery -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Core JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <!-- Feather Icon JS -->
    <script src="assets/js/feather.min.js"></script>

    <!-- Slimscroll JS -->
    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <!-- Select2 JS -->
    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Basit istemci tarafı kontrolü (mail veya telefon)
            const form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', function (event) {
                    const input = document.getElementById('inputEmailPhone').value.trim();
                    const message = document.getElementById('validationMessage');

                    // Basit regex
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const phonePattern = /^(?:\+?90\s?0?|0)?\d{10,15}$/;

                    if (!emailPattern.test(input) && !phonePattern.test(input)) {
                        if (message) message.textContent = "Lütfen geçerli bir e-posta veya telefon numarası giriniz.";
                        // event.preventDefault(); // İsteğe bağlı: sunucu taraflı kontrol de var
                    } else {
                        if (message) message.textContent = "";
                    }
                });
            }

            // Şifre Göster/Gizle (Tabler Icon uyumlu)
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.toggle-password');
                if (!btn) return;

                const targetSel = btn.getAttribute('data-target');
                const input = document.querySelector(targetSel);
                if (!input) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    btn.classList.remove('ti-eye-off');
                    btn.classList.add('ti-eye');
                } else {
                    input.type = 'password';
                    btn.classList.remove('ti-eye');
                    btn.classList.add('ti-eye-off');
                }
            });
        });
    </script>

</body>

</html>