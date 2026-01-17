<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';

$db = new Ydil();

try {
    $sql = "ALTER TABLE `personel1` 
            ADD COLUMN `guvenlik_sorusu` VARCHAR(255) NULL DEFAULT NULL AFTER `durum`,
            ADD COLUMN `guvenlik_cevap` VARCHAR(255) NULL DEFAULT NULL AFTER `guvenlik_sorusu`";

    $db->conn->exec($sql);
    echo "<h1>Başarılı</h1><p>Tablo yapısı güncellendi: personel1 tablosuna guvenlik_sorusu ve guvenlik_cevap eklendi.</p>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "<h1>Bilgi</h1><p>Sütunlar zaten mevcut, işlem yapılmadı.</p>";
    } else {
        echo "<h1>Hata</h1><p>" . $e->getMessage() . "</p>";
    }
}
?>