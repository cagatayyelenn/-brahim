<?php
// ajax/kasa-liste.php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__.'/../dosyalar/config.php';
require_once __DIR__.'/../dosyalar/Ydil.php';
require_once __DIR__.'/../dosyalar/oturum.php';

try {
    $db = new Ydil();

    $sube_id = (int)($_SESSION['sube_id'] ?? 0);
    if ($sube_id <= 0) {
        echo json_encode(['ok'=>false,'msg'=>'Şube bulunamadı']); exit;
    }

    // İstekten gelen filtreler
    $start = trim($_POST['start'] ?? '');   // beklenen: YYYY-MM-DD
    $end   = trim($_POST['end'] ?? '');     // beklenen: YYYY-MM-DD
    $kasa_id = (int)($_POST['kasa_id'] ?? 0);

    // Yardımcılar
    $money_tr = function($v){ return number_format((float)$v, 2, ',', '.').' ₺'; };
    $tarih_tr = function($dt){
        if (!$dt) return '';
        $ts = strtotime($dt);
        return date('d.m.Y H:i', $ts);
    };

    // Dinamik WHERE
    $where  = ["k.sube_id = :sube_id"];
    $params = [':sube_id'=>$sube_id];

    // Tarih sadece GÜN bazlı (saat yok) → DATE(hareket_tarihi) BETWEEN :d1 AND :d2
    if ($start !== '' && $end !== '') {
        $where[] = "DATE(kh.hareket_tarihi) BETWEEN :d1 AND :d2";
        $params[':d1'] = $start;
        $params[':d2'] = $end;
    } elseif ($start !== '') {
        $where[] = "DATE(kh.hareket_tarihi) >= :d1";
        $params[':d1'] = $start;
    } elseif ($end !== '') {
        $where[] = "DATE(kh.hareket_tarihi) <= :d2";
        $params[':d2'] = $end;
    }

    if ($kasa_id > 0) {
        $where[] = "kh.kasa_id = :kasa_id";
        $params[':kasa_id'] = $kasa_id;
    }

    $sql = "
    SELECT
        kh.hareket_id,
        kh.kasa_id,
        k.kasa_adi,
        k.kasa_tipi,

        kh.yon,
        kh.hareket_tipi,
        kh.hareket_tur_id,
        tur.tur_adi,

        kh.tutar,
        kh.aciklama,
        kh.hareket_tarihi,

        kh.ogrenci_id,
        o.ogrenci_numara,
        o.ogrenci_adi,
        o.ogrenci_soyadi,

        kh.odeme_id,
        odm.odeme_no,
        y.yontem_adi AS odeme_yontemi,

        kh.created_by,
        p.personel_adi,
        p.personel_soyadi

    FROM kasa_hareketleri1 kh
    JOIN kasa1 k                    ON k.kasa_id = kh.kasa_id
    LEFT JOIN kasa_hareket_turleri tur ON tur.tur_id = kh.hareket_tur_id
    LEFT JOIN ogrenci1 o            ON o.ogrenci_id = kh.ogrenci_id
    LEFT JOIN odeme1 odm            ON odm.odeme_id = kh.odeme_id
    LEFT JOIN odeme_yontem1 y       ON y.yontem_id = odm.yontem_id
    LEFT JOIN personel p            ON p.personel_id = kh.created_by
    WHERE ".implode(' AND ', $where)."
    ORDER BY kh.hareket_tarihi DESC, kh.hareket_id DESC
    ";

    $rows = $db->get($sql, $params);




    $xsql="DATE(kh.hareket_tarihi) BETWEEN :start AND :end";
        $totalsSql="SELECT 
        k.kasa_id,
        k.kasa_adi,
        COALESCE(SUM(CASE WHEN kh.yon = 'GIRIS' THEN kh.tutar ELSE 0 END), 0) AS total_giris,
        COALESCE(SUM(CASE WHEN kh.yon = 'CIKIS' THEN kh.tutar ELSE 0 END), 0) AS total_cikis,
        (
            COALESCE(SUM(CASE WHEN kh.yon = 'GIRIS' THEN kh.tutar ELSE 0 END), 0)
            -
            COALESCE(SUM(CASE WHEN kh.yon = 'CIKIS' THEN kh.tutar ELSE 0 END), 0)
        ) AS net_fark
    FROM kasa1 k
    LEFT JOIN kasa_hareketleri1 kh 
        ON kh.kasa_id = k.kasa_id 
        AND $xsql
    GROUP BY k.kasa_id, k.kasa_adi
    ORDER BY k.sira ASC;";



    $totals = $db->gets($totalsSql, [
        ':start' => $start,
        ':end'   => $end
    ]);


    

    // HTML satırlarını üret
    ob_clean();
    $html = '';
    $vaults = [
      'NAKİT'=>0,
      'POS'=>0,
      'BANKA'=>0,
      'TUMU'=>0
    ];
    
    if (!empty($rows)) {
        foreach ($rows as $r) {
            $ogr_link_text = '';
            $ogr_link_href = '#';
            if (!empty($r['ogrenci_id'])) {
                $ogr_no = $r['ogrenci_numara'] ?: $r['ogrenci_id'];
                $ogr_ad = trim(($r['ogrenci_adi'] ?? '').' '.($r['ogrenci_soyadi'] ?? ''));
                $ogr_link_text = $ogr_no.' - '.$ogr_ad;
                $ogr_link_href = 'ogrenci-detay.php?id='.urlencode($ogr_no);
            }

            $odeme_turu = $r['odeme_yontemi'] ?? '';
            if ($odeme_turu === '' && !empty($r['kasa_tipi'])) {
                $odeme_turu = $r['kasa_tipi'];
            }

            $is_giris   = ($r['yon'] === 'GIRIS');
            $yon_badge  = $is_giris ? '<span class="badge bg-success">GİRİŞ</span>' : '<span class="badge bg-danger">ÇIKIŞ</span>';
            $tutar_cls  = $is_giris ? 'text-success' : 'text-danger';
            $tutar_pref = $is_giris ? '+' : '-';

            $html .= '
            <tr data-kasa-tipi="'.htmlspecialchars($r['kasa_tipi'] ?? '').'"
                data-odeme-no="'.htmlspecialchars($r['odeme_no'] ?? '').'"
                data-tarih="'.htmlspecialchars(substr($r['hareket_tarihi'],0,10)).'">

              <td><a href="kasa-hareket-detay.php?id='.(int)$r['hareket_id'].'" class="link-primary">'.(int)$r['hareket_id'].'</a></td>
              <td>'.htmlspecialchars($tarih_tr($r['hareket_tarihi'])).'</td>
              <td>'.htmlspecialchars($r['kasa_adi'] ?? '').'</td>
              <td>'.
                ($ogr_link_text ? '<a href="'.htmlspecialchars($ogr_link_href).'" class="link-primary">'.htmlspecialchars($ogr_link_text).'</a>' : '')
                .'</td>
              <td>'.htmlspecialchars($r['hareket_tipi'] ?: ($r['tur_adi'] ?? '')).'</td>
              <td>'.htmlspecialchars($odeme_turu).'</td>
              <td class="desc">'.htmlspecialchars($r['aciklama'] ?? '').'</td>
              <td>'.htmlspecialchars(trim(($r['personel_adi'] ?? '').' '.($r['personel_soyadi'] ?? ''))).'</td>
              <td>'.$yon_badge.'</td>
              <td class="text-end '.$tutar_cls.'">'.$tutar_pref.$money_tr($r['tutar']).'</td>
            </tr>';
        }
    }

    echo json_encode(['ok'=>true, 'html'=>$html, 'count'=>count($rows)]);
} catch (Throwable $e) {
    error_log('KH AJAX LIST ERROR: '.$e->getMessage());
    echo json_encode(['ok'=>false,'msg'=>'Hata: '.$e->getMessage()]);
}