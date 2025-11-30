<?php
// 🔹 İlk adım — Kurs Bilgisi LOCK/UNLOCK
// Doğrudan kopyalayabiliriz, sadece dosya adını değiştiriyoruz

ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../dosyalar/config.php';
require_once '../dosyalar/Ydil.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ogrenci_id = (int)($_POST['ogrenci_id'] ?? $_GET['ogrenci_id'] ?? 0);

$KEY = 'sozlesme_wizard';

function ok($extra = []){ echo json_encode(['ok'=>true] + $extra, JSON_UNESCAPED_UNICODE); exit; }
function bad($msg){ echo json_encode(['ok'=>false, 'msg'=>$msg], JSON_UNESCAPED_UNICODE); exit; }

if(!isset($_SESSION[$KEY])) $_SESSION[$KEY] = [];

switch ($action) {
    case 'lock':
        $donem_id  = (int)($_POST['donem_id'] ?? 0);
        $sinif_id  = (int)($_POST['sinif_id'] ?? 0);
        $grup_id   = (int)($_POST['grup_id']  ?? 0);
        $alan_id   = (int)($_POST['alan_id']  ?? 0);
        $bolum_id  = $_POST['section_id'];
        if(!$donem_id || !$alan_id) bad('Dönem ve Alan zorunlu!');
        $_SESSION[$KEY][$ogrenci_id] = [
            'locked' => true,
            'values' => [
                'donem_id' => $donem_id,
                'sinif_id' => $sinif_id,
                'grup_id'  => $grup_id,
                'alan_id'  => $alan_id,
            ],
            'ts' => time()
        ];
        ok(['state'=>$_SESSION[$KEY][$ogrenci_id]]);
        break;

    case 'unlock':
        unset($_SESSION[$KEY][$ogrenci_id]);
        ok(['state'=>['locked'=>false, 'values'=>null]]);
        break;

    case 'get':
        $state = $_SESSION[$KEY][$ogrenci_id] ?? ['locked'=>false, 'values'=>null];
        ok(['state'=>$state]);
        break;

    default:
        bad('Geçersiz işlem');
}