<?php
session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_unset(); // Oturum değişkenlerini temizle
session_destroy(); // Oturumu tamamen sonlandır
header("location:index.php");
?>
