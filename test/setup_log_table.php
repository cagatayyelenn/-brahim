<?php
require_once 'dosyalar/config.php';

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    $sql = "CREATE TABLE IF NOT EXISTS `islem_gecmisi` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `personel_id` int(11) NOT NULL DEFAULT 0,
      `sube_id` int(11) NOT NULL DEFAULT 0,
      `tablo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
      `islem_id` int(11) NOT NULL DEFAULT 0,
      `islem_turu` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
      `aciklama` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `tarih` datetime NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "Table 'islem_gecmisi' created successfully (or already exists).\n";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}
?>