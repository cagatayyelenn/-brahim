<?php
// taksit-makbuz.php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include "c/fonk.php";
include "c/config.php";
if (session_status() === PHP_SESSION_NONE) session_start();

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function tl($n){
    $v = (float)$n;
    return number_format($v, 2, ',', '.');
}
function dmy($ts){
    if(!$ts) return '—';
    $t = strtotime($ts);
    return $t ? date('d.m.Y', $t) : '—';
}

$taksit_id = isset($_GET['taksit_id']) ? (int)$_GET['taksit_id'] : 0;
if ($taksit_id <= 0) { http_response_code(400); exit('Geçersiz taksit_id'); }

// 1) TAKSİT + ÖĞRENCİ + SATIŞ + ÖDEME TÜRÜ
$t = $Ydil->getone("
  SELECT 
    t.id              AS taksit_id,
    t.ogrenci_id,
    t.satis_id,
    t.taksit_tutari,
    t.taksit_tarihi,
    t.odendi,
    t.odeme_tur_id,
    t.odeme_tarihi,
    o.ogrenci_adi,
    o.ogrenci_soyadi,
    o.ogrenci_tel,
    ks.id             AS sozlesme_no,
    ks.toplam_tutar,
    ks.kalan_tutar,
    ot.ad             AS odeme_turu
  FROM taksitler t
  LEFT JOIN ogrenci o         ON o.ogrenci_id = t.ogrenci_id
  LEFT JOIN kurs_satislari ks ON ks.id        = t.satis_id
  LEFT JOIN odeme_turleri ot  ON ot.id        = t.odeme_tur_id
  WHERE t.id = {$taksit_id}
  LIMIT 1
");
if (!$t) { http_response_code(404); exit('Taksit bulunamadı'); }

// 2) ÖDEME KAYDI (son ödeme) ve önceki ödeme toplamı
$odeme = $Ydil->getone("SELECT * FROM odemeler WHERE taksit_id = {$taksit_id} ORDER BY id DESC LIMIT 1");
$odeme_id   = $odeme['id']            ?? null;
$odeme_tutar= (float)($odeme['tutar'] ?? 0);
$odeme_dt   = $odeme['odeme_tarihi']  ?? null;

// Önceki ödeme toplamını hesapla (son ödemeden hariç)
if ($odeme_id) {
    $onceki = $Ydil->getone("
    SELECT COALESCE(SUM(tutar),0) AS toplam
    FROM odemeler
    WHERE taksit_id = {$taksit_id} AND id <> {$odeme_id}
  ");
    $onceki_odeme = (float)($onceki['toplam'] ?? 0);
} else {
    $onceki = $Ydil->getone("SELECT COALESCE(SUM(tutar),0) AS toplam FROM odemeler WHERE taksit_id = {$taksit_id}");
    $onceki_odeme = (float)($onceki['toplam'] ?? 0);
}
$yeni_odeme  = $odeme_tutar;
$kalan_taksit= max(0, (float)$t['taksit_tutari'] - $onceki_odeme - $yeni_odeme);

// 3) Şube / Personel (opsiyonel)
$sube_adi   = '—';
if (!empty($_SESSION['subedurum'])) {
    $sid = (int)$_SESSION['subedurum'];
    $s   = $Ydil->getone("SELECT sube_adi FROM sube WHERE sube_id = {$sid}");
    if ($s) $sube_adi = $s['sube_adi'];
}
$personel_ad = '—';
if (!empty($odeme['created_by'])) {
    $kb = (int)$odeme['created_by'];
    $u  = $Ydil->getone("SELECT CONCAT(ad,' ',soyad) AS adsoyad FROM kullanici_giris WHERE id = {$kb}");
    if ($u && !empty($u['adsoyad'])) $personel_ad = $u['adsoyad'];
}

// 4) Makbuz no: odemeler.id (yoksa taksit_id)
$makbuz_no = $odeme_id ?: $taksit_id;
$makbuz_tarih = $odeme_dt ?: date('Y-m-d');

// 5) Kalan toplam borç (bu sözleşmedeki ödenmemiş taksit toplamı)
$kalan_borc_row = $Ydil->getone("
  SELECT COALESCE(SUM(taksit_tutari - COALESCE(odm.odenen,0)),0) AS kalan_borc
  FROM taksitler tk
  LEFT JOIN (
    SELECT taksit_id, SUM(tutar) AS odenen
    FROM odemeler
    GROUP BY taksit_id
  ) odm ON odm.taksit_id = tk.id
  WHERE tk.satis_id = ".(int)$t['satis_id']."
");
$kalan_borc = (float)($kalan_borc_row['kalan_borc'] ?? 0);

// 6) Eğitim dönemi bilgisi (yoksa çizgi)
$donem = '—'; // dilersen donem tablosundan çekebilirsin

// 7) Telefon format
$tel = $t['ogrenci_tel'] ?: '—';

// 8) Tekrarlı içerik için fonksiyon
function render_copy($copy, $t, $donem, $makbuz_no, $makbuz_tarih, $personel_ad, $sube_adi, $onceki_odeme, $yeni_odeme, $kalan_taksit, $kalan_borc){
    ?>
    <section class="receipt">
        <div class="stamp">TAHSİLAT MAKBUZU</div>

        <div class="head">
            <div>
                <div class="brand">NGLS YABANCI DİL DÜNYASI <?= e($sube_adi ?: 'NGLS YABANCI DİL DÜNYASI') ?> Şubesi</div>
                <div class="subtitle">Tahsilat Makbuzu (<?= e($copy) ?>)</div>
            </div>
        </div>
        <div class="grid">
            <div class="field"><div class="label">Eğitim Dönemi :</div><div class="value"><?= e($donem) ?></div></div>
            <div class="field"><div class="label">Adı Soyadı :</div><div class="value"><?= e($t['ogrenci_adi'].' '.$t['ogrenci_soyadi']) ?></div></div>

            <div class="field"><div class="label">Telefon :</div><div class="value"><?= e($t['ogrenci_tel'] ?: '—') ?></div></div>
            <div class="field"><div class="label">Tarih :</div><div class="value"><?= e(dmy($makbuz_tarih)) ?></div></div>

            <div class="field"><div class="label">Makbuz No :</div><div class="value"><?= e($makbuz_no) ?></div></div>
            <div class="field"><div class="label">Ödeme Tutarı :</div><div class="value"><?= tl($yeni_odeme) ?> TL</div></div>
        </div>

        <div class="note">
            Sn. <b><?= e($t['ogrenci_adi'].' '.$t['ogrenci_soyadi']) ?></b>'dan,
            <b><?= e(dmy($makbuz_tarih)) ?></b> tarihinde
            <b><?= e($t['odeme_turu'] ?: '—') ?></b> ile
            <b><?= tl($yeni_odeme) ?> TL</b> tahsil edilmiştir.
        </div>

        <table>
            <thead>
            <tr>
                <th>TAKSİT NO</th>
                <th>TAKSİT TARİHİ</th>
                <th>TAKSİT TUTARI (TL)</th>
                <th>ÖNCEKİ ÖDEME (TL)</th>
                <th>YENİ ÖDEME (TL)</th>
                <th>TAKSİTTEN KALAN</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?= (int)$t['taksit_id'] ?></td>
                <td><?= e(dmy($t['taksit_tarihi'])) ?></td>
                <td><?= tl($t['taksit_tutari']) ?></td>
                <td><?= tl($onceki_odeme) ?></td>
                <td><?= tl($yeni_odeme) ?></td>
                <td><?= tl($kalan_taksit) ?></td>
            </tr>
            </tbody>
        </table>

        <div class="info">
            <b><?= e($t['sozlesme_no']) ?></b> numaralı sözleşme için
            <b><?= e(dmy($makbuz_tarih)) ?></b> tarih ve
            <b><?= e($makbuz_no) ?></b> makbuz no ile yaptığınız
            <b><?= tl($yeni_odeme) ?> TL</b> (<?= e($t['odeme_turu'] ?: '—') ?>) ödemesinden sonra;
            taksitlerinizden kalan toplam borç tutarı <b><?= tl($kalan_borc) ?> TL</b>'dir.
        </div>

    </section>
    <?php
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Tahsilat Makbuzu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* ---------- A5 Sayfa Ayarı ---------- */
        @page { size: A5; margin: 10mm; }
        @media print { html, body { width: 148mm; height: 210mm; } }

        :root { --brand:#0a5efb; --ink:#111; --muted:#6c757d; --border:#dee2e6; }
        * { box-sizing:border-box; }
        html,body { margin:0; padding:0; color:var(--ink); font:10px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
        .page { width:128mm; margin:10mm auto; }

        .receipt { position:relative; border:.22mm solid var(--border); border-radius:2mm; padding:4mm; margin-bottom:5mm; page-break-inside:avoid; }
        .head { display:flex; align-items:center; margin-bottom:2mm; }
        .brand { font-weight:800; font-size:3.6mm; color:var(--brand); }
        .subtitle { color:var(--muted); font-size:2.6mm; }
        .title { margin:2mm 0; font-weight:700; text-align:center; font-size:3.8mm; letter-spacing:.2px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:1.6mm 4mm; margin:2mm 0; font-size:2.8mm; }
        .field { display:flex; gap:1.6mm; }
        .label { min-width:21mm; color:#333; font-weight:600; font-size:2.4mm; text-transform:uppercase; }
        .value { font-weight:600; font-size:2.9mm; }
        .note { margin:2mm 0; padding:2mm; border:.22mm dashed var(--border); border-radius:2mm; background:#fafbff; font-size:2.8mm; }
        table { width:100%; border-collapse:collapse; margin-top:2mm; font-size:2.8mm; }
        thead th { background:#f6f8ff; border:.22mm solid var(--border); padding:1.6mm 2mm; font-weight:700; text-align:left; }
        tbody td { border:.22mm solid var(--border); padding:1.6mm 2mm; }
        .info { margin-top:2mm; padding:2mm; border-radius:2mm; background:#f8f9fa; border:.22mm solid var(--border); font-size:2.8mm; }
        .stamp { position:absolute; right:3mm; top:3mm; padding:.8mm 2.4mm; border:.22mm solid var(--border); border-radius:999px; color:#2b7a0b; background:#ecfdf3; font-size:2.5mm; }
        @media print{ .page{ width:128mm; margin:0 auto; } .receipt{ page-break-inside:avoid } }
    </style>
</head>
<body>
<div class="page">
    <?php
    // Nüsha 1
    render_copy('1. Nüsha', $t, $donem, $makbuz_no, $makbuz_tarih, $personel_ad, $sube_adi, $onceki_odeme, $yeni_odeme, $kalan_taksit, $kalan_borc);
    // Nüsha 2
    render_copy('2. Nüsha', $t, $donem, $makbuz_no, $makbuz_tarih, $personel_ad, $sube_adi, $onceki_odeme, $yeni_odeme, $kalan_taksit, $kalan_borc);
    ?>
</div>
</body>
</html>