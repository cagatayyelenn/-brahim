<?php

ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
header('Content-Type: text/html; charset=utf-8');

$taksitId = (int) ($_GET['taksit'] ?? 0);
if ($taksitId <= 0) {
    die('Geçersiz sözleşme numarası.');
}


$row = $db->gets("SELECT t.taksit_id, t.sozlesme_id, t.sira_no, t.vade_tarihi, t.odendi_tutar AS taksit_odendi, s.sozlesme_no, s.toplam_ucret, s.net_ucret, s.taksit_sayisi, d.donem_adi, o.ogrenci_id, o.ogrenci_numara, CONCAT(o.ogrenci_adi,' ',o.ogrenci_soyadi) AS ogr_adsoyad, o.ogrenci_tel, y.yontem_adi AS odeme_turu, CONCAT(p.personel_adi,' ',p.personel_soyadi) AS odemeyi_alan, od.odeme_tarihi FROM taksit1 t JOIN sozlesme1 s ON s.sozlesme_id = t.sozlesme_id JOIN ogrenci1 o ON o.ogrenci_id = s.ogrenci_id LEFT JOIN donem d ON d.donem_id = s.donem_id LEFT JOIN odeme1_taksit ot ON ot.taksit_id = t.taksit_id LEFT JOIN odeme1 od ON od.odeme_id = ot.odeme_id LEFT JOIN odeme_yontem1 y ON y.yontem_id = od.yontem_id LEFT JOIN personel p ON p.personel_id = od.personel_id WHERE t.taksit_id = :tid ORDER BY od.odeme_tarihi DESC LIMIT 1; ", [':tid' => $taksitId]);

$sid = (int) $row['sozlesme_id'];

$soz = $db->gets(" SELECT  SUM(CASE WHEN (tt.tutar - COALESCE(tt.odendi_tutar,0)) <= 0 THEN 1 ELSE 0 END) AS biten_taksit, SUM(CASE WHEN (tt.tutar - COALESCE(tt.odendi_tutar,0))  > 0 THEN 1 ELSE 0 END) AS kalan_taksit, SUM(tt.tutar) AS toplam_taksit,  SUM(COALESCE(tt.odendi_tutar,0)) AS odenen_miktar, SUM(GREATEST(tt.tutar - COALESCE(tt.odendi_tutar,0),0))   AS kalan_borc FROM taksit1 tt WHERE tt.sozlesme_id = :sid ", [':sid' => $sid]);

$taksit_id = $row['taksit_id'];
$sozlesme_id = $row['sozlesme_id'];

$egitimdonemi = $row['donem_adi'];
$ogrencino = $row['ogrenci_numara'];
$ogrenciadsoyad = $row['ogr_adsoyad'];
$ogrencitelefon = $row['ogrenci_tel'];
$sozlesmeno = $row['sozlesme_no'];
$odemetutari = $row['taksit_odendi'];
$odemeturu = $row['odeme_turu'];
$odemeyialan = $row['odemeyi_alan'];
$taksitno = $row['sira_no'];
$taksittarihi = $row['vade_tarihi'];
$taksittutari = $row['taksit_odendi'];
$kalanborc = $soz['kalan_borc'];



if (!function_exists('fmtTL')) {
    function fmtTL($n)
    {
        return number_format((float) $n, 2, ',', '.') . ' TL';
    }
}
if (!function_exists('trDate')) {
    function trDate($d)
    {
        return date('d.m.Y', strtotime($d));
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title> Tahsilat Makbuzu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            font-size: 10pt;
        }

        .page {
            background: white;
            width: 210mm;
            height: 297mm;
            padding: 4mm;
            margin: 0mm auto;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .receipt {
            border: 1px solid #ccc;
            padding: 15px;
            height: 135mm;
            /* Sayfanın yarısı eksi biraz boşluk */
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .cut-line {
            border-top: 2px dashed #999;
            margin: 20px 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: 600;
        }

        .header h2 {
            margin: 5px 0 0 0;
            font-size: 12pt;
            font-weight: normal;
            border: 1px solid #000;
            display: inline-block;
            padding: 2px 8px;
        }

        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .details-left,
        .details-right {
            width: 40%;
        }

        .details-left div,
        .details-right div {
            margin-bottom: 4px;
        }

        .details strong {
            display: inline-block;
            width: 100px;
            /* Sabit genişlik ile hizalama sağlar */
        }

        .payment-text {
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .payment-table th,
        .payment-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        .payment-table th {
            background-color: #f8f9fa;
        }

        .summary-text {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .footer {
            margin-top: auto;
            /* Altbilgiyi en alta iter */
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 8pt;
            color: #555;
        }

        .signature {
            text-align: center;
        }

        .signature-name {
            margin-top: 5px;
            font-weight: bold;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            padding: 12px;
            display: flex;
            justify-content: center;
            /* ORTALAR */
            gap: 12px;
        }

        .toolbar button {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: #007bff;
            color: white;
            transition: background 0.2s;
        }

        .toolbar button:hover {
            background: #0056b3;
        }

        /* YAZDIRMA */
        @media print {

            /* araç çubuğunu gizle */
            .toolbar {
                display: none !important;
            }

            /* her .page tam kağıda otursun */
            .page {
                min-height: auto !important;
                /* fazla yüksekliği kaldır */
                padding: 0 !important;
                /* iç boşlukları iptal et */
                border: 0 !important;
                /* kenarlığı yazdırmada kapat */
                break-after: page;
                page-break-after: always;
            }

            /* tablo ve bölümlerin bölünmesini engelle */
            .sheet,
            table,
            tr,
            td,
            th,
            .section {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            /* ekran boşluğunu sıfırla */
            #doc {
                margin-top: 0 !important;
            }
        }

        /* === Ekran Görüntüsü (kenarlık ve padding yalnızca ekranda) === */
        @media screen {
            .page {
                padding: 15mm 18mm 14mm;
                /* ekranda görünüm için boşluk */
                border: 1px solid var(--line);
                /* yalnız ekranda kenarlık */
            }
        }
    </style>
</head>

<body>
    <div class="toolbar no-print">
        <button id="btnPrint" type="button">
            <i class="fa-solid fa-print"></i> Yazdır
        </button>
        <a href="ogrenci-detay-sozlesme.php?id=<?= $ogrencino ?>" class="btn-return"
            style="text-decoration:none; display: flex; align-items: center; gap: 8px; font-size: 15px; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; background: #6c757d; color: white; transition: background 0.2s;">
            <i class="fa-solid fa-arrow-left"></i> Geri Dön
        </a>
    </div>
    <div id="doc">
        <div class="page">
            <?php for ($i = 0; $i < 2; $i++): ?>
                <div class="receipt">
                    <div class="header">
                        <h1>NGLS YABANCI DİL DÜNYASI ANTAKYA ŞUBESİ</h1>
                        <h2>TAHSİLAT MAKBUZU</h2>
                    </div>

                    <div class="details">
                        <div class="details-left">
                            <div><strong>EĞİTİM DÖNEMİ</strong>:
                                <?= htmlspecialchars($egitimdonemi ?? (date('Y') . '-' . (date('Y') + 1))) ?></div>
                            <div><strong>ÖĞRENCİ NO</strong>: <?= htmlspecialchars($ogrencino) ?></div>
                            <div><strong>ADI SOYADI</strong>: <?= htmlspecialchars($ogrenciadsoyad) ?></div>
                            <div><strong>TELEFON</strong>: <?= htmlspecialchars($ogrencitelefon) ?></div>
                        </div>
                        <div class="details-right">
                            <div><strong>TARİH</strong>: <?= htmlspecialchars(date('d.m.Y')) ?></div>
                            <div><strong>SÖZLEŞME NO</strong>: <?= htmlspecialchars($sozlesmeno) ?></div>
                            <div><strong>MAKBUZ NO</strong>: <?= date('dmy') . '-T' . $taksit_id . 'S' . $sozlesme_id; ?>
                            </div>
                            <div><strong>ÖDEME TUTARI</strong>: <?= fmtTL($odemetutari) ?></div>
                        </div>
                    </div>

                    <div class="payment-text">
                        Sn. <?= htmlspecialchars($ogrenciadsoyad) ?>’dan yukarıda bilgileri yazılı olan sözleşme taksitine
                        istinaden
                        <?= htmlspecialchars(date('d.m.Y')) ?> tarihinde <?= htmlspecialchars(mb_strtoupper($odemeturu)) ?>
                        ile
                        <?= fmtTL($odemetutari) ?> tahsil edilmiştir.
                    </div>

                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>TAKSİT SIRASI</th>
                                <th>TAKSİT TARİHİ</th>
                                <th>TAKSİT TUTARI</th>
                                <th>YENİ ÖDEME TUTARI</th>
                                <th>TAKSİTTEN KALAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= htmlspecialchars($taksitno) ?></td>
                                <td><?= trDate($taksittarihi) ?></td>
                                <td><?= fmtTL($taksittutari) ?></td>
                                <td><?= fmtTL($odemetutari) ?></td>
                                <td><?= fmtTL($kalanborc) ?> </td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align:right;font-weight:bold;">Tahsilat Tutarı :</td>
                                <td style="font-weight:bold;"><?= fmtTL($odemetutari) ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="summary-text">
                        <?= htmlspecialchars($sozlesmeno) ?> numaralı sözleşme için <?= htmlspecialchars(date('d.m.Y')) ?>
                        tarihinde
                        <?= date('dmy') . '-T' . $taksit_id . 'S' . $sozlesme_id; ?> belge numarasıyla yapılan
                        <?= fmtTL($odemetutari) ?> (<?= htmlspecialchars(mb_strtoupper($odemeturu)) ?>)
                        ödemesinden sonra kalan toplam borç tutarı <?= fmtTL($kalanborc) ?>’dir.
                    </div>

                    <div class="footer">
                        <span></span>
                        <div class="signature">
                            <div>Ödemeyi Alan</div>
                            <div class="signature-name">
                                <?= htmlspecialchars($odemeyialan ?: ($_SESSION['kisi_adi'] ?? 'NGLS Personeli')) ?></div>
                            <div>İmza / Kaşe</div>
                        </div>
                    </div>
                </div>
                <?php if ($i == 0): ?>
                    <div class="cut-line"></div><?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('btnPrint');
            if (!btn) return;

            btn.addEventListener('click', function (e) {
                e.preventDefault();

                var src = document.getElementById('doc');
                if (!src) return;

                // gizli iframe yöntemi (Chrome uyumlu)
                var styles = Array.from(document.querySelectorAll('style,link[rel="stylesheet"]'))
                    .map(el => el.outerHTML).join('');

                var iframe = document.createElement('iframe');
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);

                var doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write('<!doctype html><html lang="tr"><head><meta charset="utf-8"><title>Yazdır</title>' +
                    styles + '</head><body>' + src.innerHTML + '</body></html>');
                doc.close();

                iframe.onload = function () {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    setTimeout(() => document.body.removeChild(iframe), 500);
                };
            });
        });
    </script>
</body>

</html>