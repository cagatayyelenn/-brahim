<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

header('Content-Type: text/html; charset=utf-8');

/* Yardımcılar */
function tl($n)
{
    return number_format((float) $n, 2, ',', '.') . ' ₺';
}
function trDate($d)
{
    if (!$d)
        return '-';
    $t = strtotime($d);
    return $t ? date('d.m.Y', $t) : $d;
}

$sozlesme_id = (int) ($_GET['id'] ?? 0);
$type = $_GET['type'] ?? 'restructure'; // restructure veya terminate

if ($sozlesme_id <= 0) {
    die('Geçersiz sözleşme numarası.');
}

/* 1) Sözleşme + Öğrenci */
$sqlSoz = "
SELECT
  s.*,
  o.ogrenci_numara, o.ogrenci_tc, o.ogrenci_adi, o.ogrenci_soyadi, o.ogrenci_tel, o.ogrenci_adres,
  si.sinif_adi,
  g.grup_adi,
  su.sube_adi
FROM sozlesme1 s
JOIN ogrenci1 o    ON o.ogrenci_id = s.ogrenci_id
LEFT JOIN sinif   si ON si.sinif_id= s.sinif_id
LEFT JOIN grup    g ON g.grup_id   = s.grup_id
LEFT JOIN sube    su ON su.sube_id = s.sube_id
WHERE s.sozlesme_id = :id
LIMIT 1
";
$S = $db->gets($sqlSoz, [':id' => $sozlesme_id]);
if (!$S) {
    die('Sözleşme bulunamadı.');
}

/* 2) Taksitler (Yapılandırma ise yeni planı göstermek için) */
$taksitler = $db->get("
  SELECT t.taksit_id, t.sira_no, t.vade_tarihi, t.tutar, t.odendi_tutar, t.durum
  FROM taksit1 t
  WHERE t.sozlesme_id = :sid
  ORDER BY t.sira_no ASC, t.vade_tarihi ASC
", [':sid' => $sozlesme_id]);

/* Toplamlar */
$toplamTaksit = 0.0;
$toplamOdendi = 0.0;
foreach ($taksitler as $t) {
    $toplamTaksit += (float) $t['tutar'];
    $toplamOdendi += (float) $t['odendi_tutar'];
}
// Peşinat varsa (odeme1 tablosundan)
$pesinatSql = "SELECT tutar FROM odeme1 WHERE sozlesme_id = :id";
$pesinatRow = $db->get($pesinatSql, [':id' => $sozlesme_id]);
$pesinatTutar = (float) ($pesinatRow[0]['tutar'] ?? 0);
$toplamOdendi += $pesinatTutar;

$kalan = max(0.0, $toplamTaksit + $pesinatTutar - $toplamOdendi); // Basit mantık
$kalan = max(0.0, $S['toplam_ucret'] - $toplamOdendi); // Daha doğrusu

$documentTitle = ($type === 'terminate') ? 'SÖZLEŞME FESİH İBRANAMESİ' : 'EK ÖDEME PLANI VE YAPILANDIRMA PROTOKOLÜ';
?>

<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <title>
        <?= $documentTitle ?> —
        <?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --fg: #111;
            --muted: #666;
            --line: #ddd;
            --fs: 14px;
        }

        @page {
            size: A4;
            margin: 20mm;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font: 400 var(--fs)/1.5 system-ui, sans-serif;
            color: var(--fg);
        }

        .page {
            width: 210mm;
            margin: 0 auto;
            padding: 15mm;
            border: 1px solid var(--line);
            min-height: 297mm;
            background: #fff;
        }

        .head {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
        }

        .head h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }

        .head h2 {
            font-size: 16px;
            margin: 5px 0 0;
            font-weight: 600;
            color: #444;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .info-table th {
            background: #f9f9f9;
            width: 30%;
            font-weight: 600;
        }

        .content-text {
            text-align: justify;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .plan-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            font-size: 13px;
        }

        .plan-table th,
        .plan-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        .plan-table th {
            background: #eee;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 4rem;
        }

        .sign-box {
            width: 40%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .toolbar {
            position: sticky;
            top: 0;
            background: #f1f1f1;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }

        .btn {
            padding: 8px 16px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #0056b3;
        }

        @media print {
            .toolbar {
                display: none;
            }

            .page {
                border: none;
                width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <a href="javascript:window.close()" class="btn" style="background:#6c757d"><i class="fa fa-times"></i> Pencereyi
            Kapat</a>
        <button onclick="window.print()" class="btn"><i class="fa fa-print"></i> Belgeyi Yazdır</button>
    </div>

    <div class="page">
        <div class="head">
            <h1>NGLS YABANCI DİL DÜNYASI EĞİTİM HİZMETLERİ LTD. ŞTİ.</h1>
            <h2>
                <?= $documentTitle ?>
            </h2>
        </div>

        <table class="info-table">
            <tr>
                <th>Sözleşme No</th>
                <td>
                    <?= htmlspecialchars($S['sozlesme_no']) ?>
                </td>
            </tr>
            <tr>
                <th>Öğrenci Adı Soyadı</th>
                <td>
                    <?= htmlspecialchars($S['ogrenci_adi'] . ' ' . $S['ogrenci_soyadi']) ?>
                </td>
            </tr>
            <tr>
                <th>TC Kimlik No</th>
                <td>
                    <?= htmlspecialchars($S['ogrenci_tc']) ?>
                </td>
            </tr>
            <tr>
                <th>Sınıf / Grup</th>
                <td>
                    <?= htmlspecialchars(($S['sinif_adi'] ?? '') . ' / ' . ($S['grup_adi'] ?? '')) ?>
                </td>
            </tr>
            <tr>
                <th>İşlem Tarihi</th>
                <td>
                    <?= date('d.m.Y') ?>
                </td>
            </tr>
        </table>

        <div class="content-text">
            <?php if ($type === 'terminate'): ?>
                <p>
                    Yukarıda kimlik bilgileri yer alan öğrencinin/velinin talebi üzerine, kurumumuzla akdedilmiş olan eğitim
                    hizmet sözleşmesi,
                    tarafların karşılıklı mutabakatı ile <b>FESHEDİLMİŞTİR</b>.
                </p>
                <p>
                    İşbu fesih protokolü tarihi itibariyle, taraflar birbirlerinden (aşağıda belirtilen varsa kalan
                    bakiyeler saklı kalmak kaydıyla)
                    herhangi bir hak ve alacak talep etmeyeceklerini, sözleşmeden doğan tüm yükümlülüklerin sona erdiğini,
                    birbirlerini
                    gayrikabili rücu ibra ettiklerini kabul ve beyan ederler.
                </p>
                <p>
                    <b>Fesih Sonrası Durum:</b><br>
                    Toplam Ödenen Tutar: <b>
                        <?= tl($toplamOdendi) ?>
                    </b><br>
                    Kalan Borç / İade Durumu: <b>İlişik kesilmiştir.</b>
                </p>
            <?php else: ?>
                <p>
                    Yukarıda kimlik bilgileri yer alan öğrenci/veli ile kurumumuz arasında imzalanmış olan eğitim hizmet
                    sözleşmesinin ödeme planı,
                    tarafların karşılıklı mutabakatı ile yeniden <b>YAPILANDIRILMIŞTIR</b>.
                </p>
                <p>
                    İşbu protokol tarihi itibariyle geçerli olacak <b>YENİ ÖDEME PLANI</b> aşağıda belirtilmiştir.
                    Önceki ödeme planındaki ödenmemiş taksitler iptal edilmiş olup, kalan borç bakiyesi işbu yeni plana göre
                    tahsil edilecektir.
                    Sözleşmenin diğer hükümleri aynen geçerliliğini korumaktadır.
                </p>

                <table class="plan-table">
                    <thead>
                        <tr>
                            <th>Taksit No</th>
                            <th>Vade Tarihi</th>
                            <th>Tutar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        foreach ($taksitler as $t):
                            // Sadece ödenmemişleri veya yeni planı gösterelim, ya da hepsini gösterip durumu belirtelim.
                            // Protokolde genelde sadece KALAN (YENİ) plan istenir ama kafa karışıklığı olmasın diye hepsini döküp ödendi bilgisini verelim.
                            $style = ($t['odendi_tutar'] >= $t['tutar']) ? 'background:#f0fff0; color:#aaa' : '';
                            $status = ($t['odendi_tutar'] >= $t['tutar']) ? '(Ödendi)' : '';
                            ?>
                            <tr style="<?= $style ?>">
                                <td>
                                    <?= $counter++ ?>
                                </td>
                                <td>
                                    <?= trDate($t['vade_tarihi']) ?>
                                </td>
                                <td>
                                    <?= tl($t['tutar']) ?>
                                    <?= $status ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:right">Yeni Sözleşme Toplamı</th>
                            <th>
                                <?= tl($S['toplam_ucret']) ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>

        <div class="signatures">
            <div class="sign-box">
                <b>Öğrenci / Veli</b><br>
                (İmza)
                <br><br><br>
                <?= htmlspecialchars($S['ogrenci_adi'] . ' ' . $S['ogrenci_soyadi']) ?>
            </div>
            <div class="sign-box">
                <b>Kurum Yetkilisi</b><br>
                (İmza / Kaşe)
                <br><br><br>
                NGLS Eğitim Hizmetleri
            </div>
        </div>
    </div>

</body>

</html>