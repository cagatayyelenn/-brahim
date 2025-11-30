<?php

ob_start();
require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';
$db  = new Ydil();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['ok'=>false,'msg'=>'Geçersiz istek']); exit;
    }


    $kasa_id        = (int)($_POST['kasa_id'] ?? 0);
    $hareket_tur_id = (int)($_POST['hareket_tur_id'] ?? 0);     // detay tür
    $yon_raw        = strtoupper(trim($_POST['yon'] ?? 'GIRIS')); // güvenlik için tekrar set edeceğiz
    $tutar_raw      = trim($_POST['tutar'] ?? '');
    $tarih_raw      = trim($_POST['hareket_tarihi'] ?? '');
    $aciklama       = trim($_POST['aciklama'] ?? '');
    $yon       =  $_POST['yon'];
    $hareket_tipi =   'TRANSFER_GELIR';
    $created_by  = (int)($_SESSION['personel_id'] ?? 0) ?: null;

    // TRY string -> float (örn: 17.500,00 -> 17500.00)
    $tutar = 0.0;
    if ($tutar_raw !== '') {
        $clr    = preg_replace('/[^\d,.-]/', '', $tutar_raw);
        $clr    = str_replace('.', '', $clr);
        $clr    = str_replace(',', '.', $clr);
        $tutar  = (float)$clr;
    }

    // Tarih: "DD.MM.YYYY" veya "DD.MM.YYYY HH:MM" → "Y-m-d H:i:s"
    $dt = null;
    if ($tarih_raw !== '') {
        $tarih_raw = preg_replace('/\s+/', ' ', $tarih_raw);
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})(?:\s+(\d{2}):(\d{2}))?$/', $tarih_raw, $m)) {
            $dd=$m[1]; $mm=$m[2]; $yy=$m[3]; $HH=$m[4]??'00'; $II=$m[5]??'00';
            $dt = sprintf('%04d-%02d-%02d %02d:%02d:00', $yy, $mm, $dd, $HH, $II);
        }
    }
    if (!$dt) $dt = date('Y-m-d H:i:s');

    // Basit doğrulama
    if (!$kasa_id || !$hareket_tur_id || $tutar <= 0) {
        echo json_encode(['ok'=>false,'msg'=>'Eksik/hatalı veri (kasa, tür, tutar)']); exit;
    }

    $columns = [
        'kasa_id', 'odeme_id', 'sozlesme_id', 'ogrenci_id', 'yon',
        'hareket_tipi', 'hareket_tur_id', 'tutar', 'aciklama',
        'hareket_tarihi', 'created_at', 'created_by'
    ];

    $values = [
        $kasa_id,
        null, // NULL gönderirken string 'NULL' değil → null olmalı
        null,
        null,
        $yon,
        $hareket_tipi,
        $hareket_tur_id,
        $tutar,
        ($aciklama !== '' ? $aciklama : null),
        $dt,
        date('Y-m-d H:i:s'),
        $created_by
    ];

// --- BURADA INSERT YAP ---
    $insertID = $db->insert('kasa_hareketleri1', $columns, $values);
 

    if (!$insertID) {
        echo json_encode(['ok'=>false,'msg'=>'Kayıt eklenemedi']); exit;
    }

    echo json_encode([
        'ok'  => true,
        'id'  => (int)$insertID,
        'msg' => 'Kasa hareketi eklendi'
    ]);
} catch (Throwable $e) {
    error_log('KasaGelirKaydet ERROR: '.$e->getMessage());
    echo json_encode(['ok'=>false,'msg'=>'Hata: '.$e->getMessage()]);
}