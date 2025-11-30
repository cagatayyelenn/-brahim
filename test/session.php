<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php'; 

// Sadece "oturum var mı" değil, "beklediğim anahtar set mi" diye bak
if (empty($_SESSION)) {
    header("Location: giris.php");
    exit;
}

// Debug için (geçici):
echo 'sesion verisi:';
 echo '<pre>'; print_r($_SESSION); echo '</pre>';
// Debug için (geçici):
echo 'user verisi';
echo '<pre>'; print_r($user); echo '</pre>';

$current_page = basename($_SERVER['PHP_SELF']); // örn: "personeller.php"

print_r($current_page);