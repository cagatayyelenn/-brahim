<?php
session_start();

// 1. Oturumu temizle
$_SESSION = [];

// 2. Cookie varsa (Remember Me gibi), onu da temizle
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Oturumu tamamen sonlandır
session_destroy();

// 4. Tarayıcı önbelleğini devre dışı bırak
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// 5. LocalStorage temizliği için küçük bir HTML çıktısı ve JS ile yönlendirme
?>
<!DOCTYPE html>
<html>

<head>
    <script>
        localStorage.removeItem('session_expiry_time');
        // Eğer v2 kullanırsak onu da sileriz
        window.location.href = 'giris.php';
    </script>
</head>

<body></body>

</html>
<?php
exit;