<?php
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';

$db = new Ydil();

$sozlesme_id = (int) ($_GET['id'] ?? 0);
if (!$sozlesme_id) {
    die("Geçersiz Sözleşme ID");
}

/* 1. Sözleşme ve Öğrenci Bilgilerini Çek */
$sql = "
    SELECT 
        s.*,
        o.ogrenci_adi, o.ogrenci_soyadi, o.ogrenci_tc, o.ogrenci_adres, o.ogrenci_tel,
        il.il_adi, ilce.ilce_adi
    FROM sozlesme1 s
    JOIN ogrenci1 o ON o.ogrenci_id = s.ogrenci_id
    LEFT JOIN il ON il.il_id = o.il_id
    LEFT JOIN ilce ON ilce.ilce_id = o.ilce_id
    WHERE s.sozlesme_id = :sid
    LIMIT 1
";
$sozlesme = $db->gets($sql, ['sid' => $sozlesme_id]);

if (!$sozlesme) {
    die("Sözleşme bulunamadı veya yetkiniz yok.");
}

/* 2. Kalan (Ödenmemiş) Taksitleri Çek */
$taksitler = $db->get("
    SELECT * 
    FROM taksit1 
    WHERE sozlesme_id = :sid 
      AND (tutar - odendi_tutar) > 0
    ORDER BY vade_tarihi ASC
", ['sid' => $sozlesme_id]);

if (empty($taksitler)) {
    die("Bu sözleşmeye ait, senede bağlanacak ödenmemiş taksit bulunmamaktadır.");
}

/* 3. Helper Fonksiyon: Rakamı Yazıya Çevir (Basit) */
function sayiyiYaziyaCevir($sayi)
{
    $fmt = number_format($sayi, 2, '.', '');
    list($tam, $kurus) = explode('.', $fmt);

    // Buraya profesyonel bir rakam->yazı kütüphanesi eklenebilir.
    // Şimdilik sadece rakam olarak döndüreceğiz veya basit string.
    return "#" . number_format($sayi, 2, ',', '.') . " TL#";
}

$adSoyad = $sozlesme['ogrenci_adi'] . ' ' . $sozlesme['ogrenci_soyadi'];
$tc = $sozlesme['ogrenci_tc'];
$adres = $sozlesme['ogrenci_adres'] . ' ' . $sozlesme['ilce_adi'] . '/' . $sozlesme['il_adi'];
$KefilAdSoyad = ".................................................."; // Opsiyonel: Kefil bilgisi DB'de varsa çekilir
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Senet Yazdır -
        <?= htmlspecialchars($adSoyad) ?>
    </title>
    <style>
        body {
            font-family: "Courier New", Courier, monospace;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }

        .senet-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto 40px auto;
            border: 2px solid #000;
            padding: 20px;
            position: relative;
            page-break-inside: avoid;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .senet-title {
            font-weight: bold;
            font-size: 24px;
            text-decoration: underline;
        }

        .vade-tarihi {
            border: 1px solid #000;
            padding: 5px 10px;
            font-weight: bold;
        }

        .tutar-box {
            border: 1px solid #000;
            padding: 5px 10px;
            font-size: 18px;
            font-weight: bold;
            background: #eee;
        }

        .content {
            line-height: 2.2;
            text-align: justify;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .imza-box {
            text-align: center;
            width: 45%;
        }

        .imza-box span {
            display: block;
            margin-bottom: 40px;
            font-weight: bold;
        }

        @media print {
            .btn-print {
                display: none;
            }

            body {
                padding: 0;
            }

            .senet-container {
                border: 1px solid #000;
                page-break-after: always;
                margin: 20px;
            }
        }

        .btn-print {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-family: sans-serif;
        }
    </style>
</head>

<body>

    <button class="btn-print" onclick="window.print()">Yazdır</button>

    <?php foreach ($taksitler as $idx => $t):
        $kalan = $t['tutar'] - $t['odendi_tutar'];
        $vade = date('d.m.Y', strtotime($t['vade_tarihi']));
        ?>
        <div class="senet-container">
            <div class="header">
                <div class="vade-tarihi">VADE TARİHİ:
                    <?= $vade ?>
                </div>
                <div class="tutar-box">
                    <?= number_format($kalan, 2, ',', '.') ?> TL
                </div>
            </div>

            <div class="content">
                İşbu emre muharrer senedim mukabilinde <strong>
                    <?= $vade ?>
                </strong> tarihinde
                Sayın <strong>
                    <?= 'Yabancı Dil Dünyası (Firma Adı)' ?>
                </strong> veya emrühavalesine yukarıda yazılı
                yalnız <strong>
                    <?= sayiyiYaziyaCevir($kalan) ?>
                </strong> ödeyeceğim. Bedeli malen/nakden ahzolunmuştur.
                İşbu bono vadesinde ödenmediği takdirde, müteakip bonoların da muacceliyet kesbedeceğini,
                ihtilaf vukuunda <strong>................ Mahkemeleri</strong>'nin selahiyetini şimdiden kabul eylerim.
            </div>

            <div class="footer">
                <div class="imza-box">
                    <span>KEFİL</span>
                    <br><br>
                    İMZA
                </div>

                <div class="imza-box">
                    <div style="text-align: left; margin-bottom: 10px;">
                        <strong>BORÇLU</strong><br>
                        Ad Soyad:
                        <?= htmlspecialchars($adSoyad) ?><br>
                        T.C. No:
                        <?= htmlspecialchars($tc) ?><br>
                        Adres:
                        <?= htmlspecialchars($adres) ?>
                    </div>
                    <span>İMZA</span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</body>

</html>