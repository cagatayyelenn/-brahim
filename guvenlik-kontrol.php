<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();

require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
$db = new Ydil();

// 1) Temp session var mı? Yoksa girişe at.
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: giris.php");
    exit;
}

$personel_id = $_SESSION['temp_user_id'];
$mesaj = "";

// 2) Kullanıcıyı çek
$sqlUser = "SELECT * FROM personel1 WHERE personel_id = :id LIMIT 1";
$user = $db->gets($sqlUser, [':id' => $personel_id]);

if (!$user) {
    session_destroy();
    header("Location: giris.php");
    exit;
}

// 3) Güvenlik sorusu tanımlı mı?
if (empty($user['guvenlik_sorusu']) || empty($user['guvenlik_cevap'])) {
    // Tanımlı değil -> Tanımlama sayfasına git
    header("Location: guvenlik-tanimla.php");
    exit;
}

// 4) POST işlemi (Cevap Kontrolü)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cevap = trim($_POST['cevap'] ?? '');

    if ($cevap === '') {
        $mesaj = '<small style="color: red;">Lütfen cevabınızı giriniz.</small>';
    } else {
        // Hash kontrolü
        if (password_verify($cevap, $user['guvenlik_cevap'])) {
            // DOĞRU CEVAP -> Girişi tamamla

            // Session'ı temizle/hazırla
            // temp_user_id silebiliriz ya da kalsın, ama asıl sessionları set etmeliyiz.
            unset($_SESSION['temp_user_id']);

            session_regenerate_id(true);

            $_SESSION['personel_id'] = (int) $user['personel_id'];
            $_SESSION['ad'] = trim(($user['personel_adi'] ?? '') . ' ' . ($user['personel_soyadi'] ?? ''));
            $_SESSION['yetki'] = (string) $user['yetki'];
            $_SESSION['sube_id'] = $user['sube_id'] ?? null;
            $_SESSION['email'] = $user['eposta'] ?? null;

            if ((string) $user['yetki'] === '1') {
                // admin -> şube seçimi
                header("Location: sube.php");
            } else {
                $row = $db->gets("SELECT sube_adi FROM sube WHERE sube_id = :sid LIMIT 1", [':sid' => $user['sube_id']]);
                if ($row && !empty($row['sube_adi'])) {
                    $_SESSION['sube_adi'] = $row['sube_adi'];
                }
                // yönetici -> anasayfa
                header("Location: index.php");
            }
            exit;

        } else {
            // YANLIŞ CEVAP
            $mesaj = '<small style="color: red;">Cevap hatalı!</small>';
        }
    }
}

// Soru metni
$soru = $user['guvenlik_sorusu'];

?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Güvenlik Kontrolü - Sqooler</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="account-page">
    <div class="main-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5 mx-auto">
                    <div class="d-flex flex-column justify-content-between vh-100">
                        <div class="mx-auto p-4 text-center">
                            <img src="assets/img/authentication/authentication-logo.svg" class="img-fluid" alt="Logo">
                        </div>
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <h2 class="mb-2">Güvenlik Kontrolü</h2>
                                    <p class="mb-0">Lütfen güvenlik sorusunu yanıtlayın.</p>
                                </div>
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <?php echo htmlspecialchars($soru); ?>
                                        </label>
                                        <input type="password" class="form-control" name="cevap" placeholder="Cevabınız"
                                            required autofocus>
                                        <div class="mt-1">
                                            <?php echo $mesaj; ?>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Doğrula</button>
                                </form>
                                <div class="mt-3 text-center">
                                    <a href="giris.php" class="text-muted">Giriş Ekranına Dön</a>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 text-center">
                            <p class="mb-0">Copyright &copy; 2025 - Sqooler Okul Yönetim Sistemi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>