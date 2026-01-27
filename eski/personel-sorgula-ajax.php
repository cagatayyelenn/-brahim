<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc = $_POST["tc"] ?? null;
    $tel = $_POST["tel"] ?? null;
    $mail = $_POST["mail"] ?? null;

    $varmisorgusu = "SELECT personel_tel FROM personel WHERE personel_tc = '$tc' OR personel_tel = '$tel' OR personel_mail = '$mail'";
    $varmi=$Ydil->getone($varmisorgusu);
    print_r($varmi);
    if (empty($varmi['personel_tel'])) {
      echo $boş;
    }else {
      echo $dolu;
    }
    echo 'de';
    die;

    $query = "SELECT COUNT(*) FROM personel WHERE tc = :tc OR telefon = :tel OR email = :mail";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ":tc" => $tc,
        ":tel" => $tel,
        ":mail" => $mail
    ]);

    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "exists"; // Kayıt varsa AJAX tarafına "exists" döndür
    } else {
        echo "not_exists"; // Kayıt yoksa "not_exists" döndür
    }
}

?>
