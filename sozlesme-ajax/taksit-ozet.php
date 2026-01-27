<?php
// test/ajax/taksit-ozet.php  (kendi yolunuza göre konumlandırın)
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';

$out = ['ok'=>false, 'msg'=>'Bilinmeyen hata'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(['ok'=>false,'msg'=>'Geçersiz istek']); exit;
    }

    $taksit_id   = (int)($_GET['taksit_id'] ?? 0);
    $ogrenci_id  = (int)($_GET['ogrenci_id'] ?? 0); // opsiyonel
    $sozlesme_id = (int)($_GET['sozlesme_id'] ?? $_GET['satis_id'] ?? 0);

    if (!$taksit_id || !$sozlesme_id) {
        echo json_encode(['ok'=>false,'msg'=>'Eksik parametre']); exit;
    }

    $db = new Ydil();

    // 1) Seçili taksit + temel sözleşme/öğrenci bilgileri
    $T = $db->gets("
        SELECT
            t.taksit_id, t.sozlesme_id, t.sira_no, t.vade_tarihi,
            t.tutar AS taksit_tutar,
            COALESCE(t.odendi_tutar,0) AS taksit_odendi,
            s.sozlesme_no
        FROM taksit1 t
        JOIN sozlesme1 s ON s.sozlesme_id = t.sozlesme_id
        WHERE t.taksit_id = :tid AND t.sozlesme_id = :sid
        LIMIT 1
    ", [':tid'=>$taksit_id, ':sid'=>$sozlesme_id]);

    if (!$T) {
        echo json_encode(['ok'=>false,'msg'=>'Taksit bulunamadı']); exit;
    }

    // 2) Sözleşme bazında taksit özetleri (yalnızca taksit1 üzerinden)
    $S = $db->gets("
        SELECT
          SUM(CASE WHEN (tt.tutar - COALESCE(tt.odendi_tutar,0)) <= 0 THEN 1 ELSE 0 END) AS biten_taksit,
          SUM(CASE WHEN (tt.tutar - COALESCE(tt.odendi_tutar,0))  > 0 THEN 1 ELSE 0 END) AS kalan_taksit,
          SUM(tt.tutar) AS toplam_taksit,
          SUM(COALESCE(tt.odendi_tutar,0)) AS odenen_miktar,
          SUM(GREATEST(tt.tutar - COALESCE(tt.odendi_tutar,0),0)) AS kalan_borc
        FROM taksit1 tt
        WHERE tt.sozlesme_id = :sid
    ", [':sid'=>$sozlesme_id]) ?: [];

    // 3) İsteğe bağlı: Peşinatı odeme1’den göstermek istersen (kalan borç hesabını ETKİLEMEZ)
    // Yalnızca bilgi amaçlıdır; kalan borcu taksit1’den aldığımız için double-count olmaz.
    $pesinatRow = $db->gets("
        SELECT COALESCE(SUM(od.tutar),0) AS pesinat
        FROM odeme1 od
        WHERE od.sozlesme_id = :sid
    ", [':sid'=>$sozlesme_id]);
    $pesinat = (float)($pesinatRow['pesinat'] ?? 0);

    // 4) Çıkış
    $taksit_kalan = max(0.0, (float)$T['taksit_tutar'] - (float)$T['taksit_odendi']);

    $out = [
        'ok' => true,

        // Başlık/kimlik
        'sozlesme' => [
            'id'  => (int)$T['sozlesme_id'],
            'no'  => (string)$T['sozlesme_no'],
        ],

        // Seçilen taksit
        'taksit' => [
            'id'       => (int)$T['taksit_id'],
            'sira'     => (int)$T['sira_no'],
            'vade'     => $T['vade_tarihi'],                        // Y-m-d
            'vade_tr'  => $T['vade_tarihi'] ? date('d.m.Y', strtotime($T['vade_tarihi'])) : '',
            'tutar'    => (float)$T['taksit_tutar'],
            'odendi'   => (float)$T['taksit_odendi'],
            'kalan'    => (float)$taksit_kalan,
        ],

        // Sözleşme geneli özet (salt taksit1’e göre)
        'ozet' => [
            'biten_taksit'   => (int)($S['biten_taksit']   ?? 0),
            'kalan_taksit'   => (int)($S['kalan_taksit']   ?? 0),
            'toplam_taksit'  => (float)($S['toplam_taksit']?? 0),
            'odenen_miktar'  => (float)($S['odenen_miktar']?? 0),
            'kalan_borc'     => (float)($S['kalan_borc']   ?? 0),
            // sadece gösterim amaçlı; kalan_borc bu değerden türetilmez
            'pesinat'        => $pesinat,
        ],
    ];

    echo json_encode($out);
} catch (Throwable $e) {
    error_log('taksit-ozet HATA: '.$e->getMessage());
    echo json_encode(['ok'=>false,'msg'=>'Sunucu hatası']);
}