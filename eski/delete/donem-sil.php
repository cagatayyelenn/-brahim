<?php
include "../c/fonk.php";
include "../c/config.php";
session_start(); // Session başlat

if (isset($_GET["sil"])) {
    $id = $_GET["sil"];

    // Güvenlik için ID'yi filtrele
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

    if ($id) {
        $response = $Ydil->delete("donem", $id, "donem_id");

        if ($response) {
            $_SESSION['delete_status'] = "success";
            $_SESSION['delete_message'] = "Silme işlemi başarılı!";
        } else {
            $_SESSION['delete_status'] = "error";
            $_SESSION['delete_message'] = "Silme işlemi başarısız!";
        }
    }
}

// Parametresiz olarak yönlendirme yaparak URL'yi temiz tut
header("Location: ../donem-bilgisi.php");
exit();
