<?php
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';

$db = new Ydil();

echo "<h1>Debug Duyurular</h1>";

// 1. Check direct PDO
echo "<h2>1. Direct PDO Query</h2>";
try {
    $stmt = $db->conn->query("SELECT * FROM duyurular");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($rows) . "<br>";
    echo "<pre>" . print_r($rows, true) . "</pre>";
} catch (Exception $e) {
    echo "PDO Error: " . $e->getMessage();
}

// 2. Check Ydil::finds
echo "<h2>2. Ydil::finds Method</h2>";
try {
    $duyurular = $db->finds("duyurular", "durum", 1, ["id", "baslik", "icerik", "tarih"]);
    echo "Count: " . count($duyurular) . "<br>";
    echo "<pre>" . print_r($duyurular, true) . "</pre>";
} catch (Exception $e) {
    echo "Ydil Error: " . $e->getMessage();
}
?>