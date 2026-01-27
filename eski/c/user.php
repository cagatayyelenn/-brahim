<?php
// Eğer oturum açılmamışsa giriş sayfasına yönlendir
if (empty($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}