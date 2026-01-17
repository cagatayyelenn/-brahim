<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();

require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
$db = new Ydil();

// 1) Temp session var mı?
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: giris.php");
    exit;
}

$personel_id = $_SESSION['temp_user_id'];
$mesaj = "";

// Kullanıcıyı çek
$sqlUser = "SELECT * FROM personel1 WHERE personel_id = :id LIMIT 1";
$user = $db->gets($sqlUser, [':id' => $personel_id]);

if (!$user) {
    session_destroy();
    header("Location: giris.php");
    exit;
}

// Eğer zaten güvenlik sorusu varsa, tekrar tanımlatmayalım (veya isteğe bağlı izin verilebilir)
// Güvenlik açısından, doluysa kontrol sayfasına atmak daha doğru.
if (!empty($user['guvenlik_sorusu']) && !empty($user['guvenlik_cevap'])) {
    header("Location: guvenlik-kontrol.php");
    exit;
}

// 2) POST işlemi (Kaydetme)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $soru = trim($_POST['soru'] ?? '');
    $cevap = trim($_POST['cevap'] ?? '');
    $cevap_tekrar = trim($_POST['cevap_tekrar'] ?? '');

    if (empty($soru) || empty($cevap)) {
        $mesaj = '<div class="alert alert-danger">Lütfen soru ve cevap alanlarını doldurunuz.</div>';
    } elseif ($cevap !== $cevap_tekrar) {
        $mesaj = '<div class="alert alert-danger">Cevaplar eşleşmiyor.</div>';
    } else {
        // Hashle ve kaydet
        $hashed_cevap = password_hash($cevap, PASSWORD_DEFAULT);

        $updateSql = "UPDATE personel1 SET guvenlik_sorusu = :soru, guvenlik_cevap = :cevap WHERE personel_id = :id";
        $res = $db->conn->prepare($updateSql);
        $ok = $res->execute([
            ':soru' => $soru,
            ':cevap' => $hashed_cevap,
            ':id' => $personel_id
        ]);

        if ($ok) {
            // Başarılı olursa direkt giriş yapalım
            session_regenerate_id(true);

            // temp session sil
            unset($_SESSION['temp_user_id']);

            $_SESSION['personel_id'] = (int) $user['personel_id'];
            $_SESSION['ad'] = trim(($user['personel_adi'] ?? '') . ' ' . ($user['personel_soyadi'] ?? ''));
            $_SESSION['yetki'] = (string) $user['yetki'];
            $_SESSION['sube_id'] = $user['sube_id'] ?? null;
            $_SESSION['email'] = $user['eposta'] ?? null;

            if ((string) $user['yetki'] === '1') {
                header("Location: sube.php");
            } else {
                $row = $db->gets("SELECT sube_adi FROM sube WHERE sube_id = :sid LIMIT 1", [':sid' => $user['sube_id']]);
                if ($row && !empty($row['sube_adi'])) {
                    $_SESSION['sube_adi'] = $row['sube_adi'];
                }
                header("Location: index.php");
            }
            exit;
        } else {
            $mesaj = '<div class="alert alert-danger">Bir hata oluştu, lütfen tekrar deneyin.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Güvenlik Sorusu Belirle - Sqooler</title>
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
                                    <h2 class="mb-2">Güvenlik Sorusu Oluştur</h2>
                                    <p class="mb-0">Hesabınız için bir güvenlik sorusu belirleyiniz.</p>
                                </div>
                                <?php echo $mesaj; ?>
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Güvenlik Sorusu</label>
                                        <select name="soru" class="form-select" required>
                                            <option value="">Seçiniz...</option>
                                            <option value="İlk evcil hayvanınızın adı nedir?">İlk evcil hayvanınızın adı
                                                nedir?</option>
                                            <option value="İlkokul öğretmeninizin adı nedir?">İlkokul öğretmeninizin adı
                                                nedir?</option>
                                            <option value="Doğduğunuz şehir neresidir?">Doğduğunuz şehir neresidir?
                                            </option>
                                            <option value="En sevdiğiniz yemek nedir?">En sevdiğiniz yemek nedir?
                                            </option>
                                            <option value="Annenizin kızlık soyadı nedir?">Annenizin kızlık soyadı
                                                nedir?</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cevap</label>
                                        <input type="text" class="form-control" name="cevap" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cevap (Tekrar)</label>
                                        <input type="text" class="form-control" name="cevap_tekrar" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Kaydet ve Devam Et</button>
                                </form>
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