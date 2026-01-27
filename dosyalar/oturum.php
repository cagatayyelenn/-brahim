<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['personel_id'], $_SESSION['yetki'])) {
    header("Location: cikis.php", true, 302);
    exit;
}
 

// Kolay erişim için normalize edilmiş $user
$user = [
    'kisi_id'  => $_SESSION['personel_id'],
    'kisi_yetki'     => $_SESSION['yetki'], // rol
    'sube_id'  => $_SESSION['sube_id'] ?? null,
    'sube_adi'  => $_SESSION['sube_adi'] ?? null,
    'ad'       => $_SESSION['ad'] ?? null,
    'email'    => $_SESSION['email'] ?? null,
];

if (isset($requireRole) && $_SESSION['yetki'] !== $requireRole) {
    header("Location: yetkisiz.php", true, 302);
    exit;
}