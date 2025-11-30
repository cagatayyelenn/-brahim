<?php
ob_start();
require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';
$db  = new Ydil();

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { echo json_encode([]); exit; }

$sql = "SELECT personel_id, personel_tc, personel_adi, personel_soyadi, personel_tel, personel_mail,
               personel_cinsiyet, personel_dogumtar, personel_adres, yetki, sube_id, durum
        FROM personel1
        WHERE personel_id = :id
        LIMIT 1";
$rows = $db->get($sql, [':id'=>$id]);
echo json_encode($rows[0] ?? []);