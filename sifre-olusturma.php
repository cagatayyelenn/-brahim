<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
session_start();
$db = new Ydil();


 if (empty($_SESSION['kisi_id'])) {
     header("Location: giris.php");
    exit;
}

$swal = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass  = trim($_POST['password'] ?? '');
    $pass2 = trim($_POST['confirm_password'] ?? '');

    if ($pass === '' || $pass2 === '') {
        $swal = ['icon'=>'error','title'=>'Eksik Alan','text'=>'Lütfen her iki şifre alanını da doldurun.'];
    } elseif ($pass !== $pass2) {
        $swal = ['icon'=>'error','title'=>'Eşleşmiyor','text'=>'Şifre ve şifre tekrarı aynı olmalı.'];
    } elseif (mb_strlen($pass) < 8) {
        $swal = ['icon'=>'warning','title'=>'Zayıf Şifre','text'=>'Şifre en az 8 karakter olmalı.'];
    } else {
        // Bcrypt ile hash
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 11]);


        $kisi_id = (int)$_SESSION['kisi_id'];
        $res = $db->update('kullanici_giris1', ['sifre'], [$hash], 'kisi_id', $kisi_id);

        if (!empty($res['status'])) {
            // İsterseniz burada login oturumunu tamamlayıp anasayfaya atabilirsiniz.
            // Örn: $_SESSION['user_id'] = $kisi_id;

            $swal = [
                'icon'  => 'success',
                'title' => 'Şifre Oluşturuldu',
                'text'  => 'Yeni şifreniz kaydedildi. Giriş sayfasına yönlendiriliyorsunuz.',
                'redirect' => 'index.php'
            ];
            // Artık bu kimlikle ilk giriş tamam; güvenlik için kisi_id’yi silelim.
            unset($_SESSION['personel_id']);
        } else {
            $swal = [
                'icon'=>'error',
                'title'=>'Hata',
                'text'=>'Şifre kaydedilirken bir hata oluştu. Lütfen tekrar deneyin.'
            ];
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
    <title>Şifre Oluşturma - Sqooler Eğitim Sistemi</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if ($swal): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                Swal.fire({
                    icon:  '<?= htmlspecialchars($swal['icon'], ENT_QUOTES) ?>',
                    title: '<?= htmlspecialchars($swal['title'], ENT_QUOTES) ?>',
                    text:  '<?= htmlspecialchars($swal['text'], ENT_QUOTES) ?>'
                }).then(function(){
                    <?php if (!empty($swal['redirect'])): ?>
                    window.location.href = '<?= htmlspecialchars($swal['redirect'], ENT_QUOTES) ?>';
                    <?php endif; ?>
                });
            });
        </script>
    <?php endif; ?>
 
	<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/plugins/icons/feather/feather.css">
	<link rel="stylesheet" href="assets/plugins/tabler-icons/tabler-icons.css">
	<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
	<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
	<link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
	<link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="account-page">


	<div class="main-wrapper">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-5 mx-auto">
                    <form method="POST" action="">
                        <div class="d-flex flex-column justify-content-between vh-100">
                            <div class="mx-auto p-4 text-center">
                                <img src="assets/img/authentication/authentication-logo.svg" class="img-fluid" alt="Logo">
                            </div>

                            <div class="card">
                                <div class="card-body p-4 pb-3">
                                    <div class="mb-4">
                                        <h2 class="mb-2">Şifre Oluşturma Alanı</h2>
                                        <p class="mb-0">Sisteme erişmek için Yeni Şifreyi Girin ve Şifreyi Onaylayın</p>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Yeni Şifre</label>
                                        <div class="pass-group position-relative">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <span class="toggle-password ti ti-eye-off"
                                                  data-target="#password"
                                                  style="cursor:pointer; position:absolute; right:3px; top:18px;"></span>
                                        </div>
                                        <small class="text-muted">En az 8 karakter olmalı.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Şifre Tekrarı</label>
                                        <div class="pass-group position-relative">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <span class="toggle-password ti ti-eye-off"
                                                  data-target="#confirm_password"
                                                  style="cursor:pointer; position:absolute; right:3px; top:18px;"></span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary w-100">Şifreyi Kaydet</button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 text-center">
                                <p class="mb-0">Copyright &copy; 2025 - Sqooler Eğitim Sistemi</p>
                            </div>
                        </div>
                    </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const targetSel = this.getAttribute('data-target');
                    const input = document.querySelector(targetSel);
                    if (!input) return;

                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.remove('ti-eye-off');
                        this.classList.add('ti-eye');
                    } else {
                        input.type = 'password';
                        this.classList.remove('ti-eye');
                        this.classList.add('ti-eye-off');
                    }
                });
            });
        });
    </script>

</body>
</html>