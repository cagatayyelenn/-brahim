<?php
// dosya: ajax/kasa-ozet.php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';

try {
    $db = new Ydil();

    // ---- giriş/filtreler
    $sube_id = (int)($_SESSION['sube_id'] ?? 0);
    if ($sube_id <= 0) { echo json_encode(['ok'=>false,'msg'=>'Şube bulunamadı']); exit; }

    $start   = trim($_POST['start']   ?? '');   // YYYY-MM-DD
    $end     = trim($_POST['end']     ?? '');   // YYYY-MM-DD
    $kasa_id = (int)($_POST['kasa_id'] ?? 0);
    $debug   = (isset($_GET['debug']) && $_GET['debug']=='1');

    $money_tr = function($v){ return number_format((float)$v, 2, ',', '.').' ₺'; };

    // ---- ON koşulu güvenli kuruluyor
    $onParts = ["kh.kasa_id = k.kasa_id"];
    $params  = [':sube_id' => $sube_id];

    if ($start !== '' && $end !== '') {
        $onParts[]     = "DATE(kh.hareket_tarihi) BETWEEN :d1 AND :d2";
        $params[':d1'] = $start;
        $params[':d2'] = $end;
    } elseif ($start !== '') {
        $onParts[]     = "DATE(kh.hareket_tarihi) >= :d1";
        $params[':d1'] = $start;
    } elseif ($end !== '') {
        $onParts[]     = "DATE(kh.hareket_tarihi) <= :d2";
        $params[':d2'] = $end;
    }
    $onSql = implode(' AND ', $onParts); // burada asla 'AND' ile bitmez

    // ---- WHERE (kasa tarafı)
    $whereK = ["k.sube_id = :sube_id"];
    if ($kasa_id > 0) {
        $whereK[] = "k.kasa_id = :kasa_id";
        $params[':kasa_id'] = $kasa_id;
    }
    $whereSql = implode(' AND ', $whereK);

    // ---- Sorgu (kasa_tipi normalize: POS/BANKA/NAKIT)
    $sql = "
        SELECT
            CASE
                WHEN UPPER(k.kasa_tipi) LIKE '%POS%'   THEN 'POS'
                WHEN UPPER(k.kasa_tipi) LIKE '%BANKA%' THEN 'BANKA'
                WHEN UPPER(k.kasa_tipi) LIKE '%NAKIT%' OR UPPER(k.kasa_tipi) LIKE '%NAKİT%' THEN 'NAKIT'
                ELSE 'DIGER'
            END AS tip,
            COALESCE(SUM(CASE WHEN kh.yon = 'GIRIS' THEN kh.tutar ELSE 0 END), 0) AS giris,
            COALESCE(SUM(CASE WHEN kh.yon = 'CIKIS' THEN kh.tutar ELSE 0 END), 0) AS cikis
        FROM kasa1 k
        LEFT JOIN kasa_hareketleri1 kh
               ON {$onSql}
        WHERE {$whereSql}
        GROUP BY tip
    ";

    $rows = $db->gets($sql, $params);

    // ---- toplama
    $sum = [
        'NAKIT' => ['g'=>0.0,'c'=>0.0,'t'=>0.0],
        'POS'   => ['g'=>0.0,'c'=>0.0,'t'=>0.0],
        'BANKA' => ['g'=>0.0,'c'=>0.0,'t'=>0.0],
    ];
    foreach ($rows as $r) {
        $tip = (string)($r['tip'] ?? '');
        if (!isset($sum[$tip])) continue; // DIGER'i atla
        $g = (float)$r['giris'];
        $c = (float)$r['cikis'];
        $sum[$tip]['g'] += $g;
        $sum[$tip]['c'] += $c;
        $sum[$tip]['t'] += ($g - $c);
    }

    $top_g = $sum['NAKIT']['g'] + $sum['POS']['g'] + $sum['BANKA']['g'];
    $top_c = $sum['NAKIT']['c'] + $sum['POS']['c'] + $sum['BANKA']['c'];
    $top_t = $sum['NAKIT']['t'] + $sum['POS']['t'] + $sum['BANKA']['t'];

    $out = [
        'ok'    => true,
        'nakit' => ['g'=>$money_tr($sum['NAKIT']['g']), 'c'=>$money_tr($sum['NAKIT']['c']), 't'=>$money_tr($sum['NAKIT']['t']), 'traw'=>$sum['NAKIT']['t']],
        'pos'   => ['g'=>$money_tr($sum['POS']['g']),   'c'=>$money_tr($sum['POS']['c']),   't'=>$money_tr($sum['POS']['t']),   'traw'=>$sum['POS']['t']],
        'banka' => ['g'=>$money_tr($sum['BANKA']['g']), 'c'=>$money_tr($sum['BANKA']['c']), 't'=>$money_tr($sum['BANKA']['t']), 'traw'=>$sum['BANKA']['t']],
        'toplam'=> ['g'=>$money_tr($top_g),              'c'=>$money_tr($top_c),              't'=>$money_tr($top_t),              'traw'=>$top_t],
    ];

    if ($debug) {
        $out['_debug'] = [
            'sql'    => $sql,
            'params' => $params,
            'start'  => $start,
            'end'    => $end,
            'kasa_id'=> $kasa_id,
            'sube_id'=> $sube_id
        ];
    }

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('KH AJAX OZET ERROR: '.$e->getMessage());
    echo json_encode(['ok'=>false,'msg'=>'Hata: '.$e->getMessage()]);
}