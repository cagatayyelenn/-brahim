<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

header('Content-Type: text/html; charset=utf-8');

/* Yardımcılar */
function tl($n){ return number_format((float)$n, 2, ',', '.').' ₺'; }
function trDate($d){ if(!$d) return '-'; $t=strtotime($d); return $t?date('d.m.Y', $t):$d; }

$sozlesme_id = (int)($_GET['id'] ?? 0);
if ($sozlesme_id <= 0) {
    die('Geçersiz sözleşme numarası.');
}

/* 1) Sözleşme + Öğrenci + birimler */
$sqlSoz = "
SELECT
  s.*,
  o.ogrenci_numara, o.ogrenci_tc, o.ogrenci_adi, o.ogrenci_soyadi, o.ogrenci_tel, o.ogrenci_mail, o.ogrenci_dogumtar,o.ogrenci_adres,o.ogrenci_cinsiyet,
  b.birim_adi,
  d.donem_adi,
  si.sinif_adi,
  g.grup_adi,
  a.alan_adi,
  su.sube_adi,
  p.personel_adi, p.personel_soyadi
FROM sozlesme1 s
JOIN ogrenci1 o    ON o.ogrenci_id = s.ogrenci_id
LEFT JOIN birim   b ON b.birim_id  = s.birim_id
LEFT JOIN donem   d ON d.donem_id  = s.donem_id
LEFT JOIN sinif   si ON si.sinif_id= s.sinif_id
LEFT JOIN grup    g ON g.grup_id   = s.grup_id
LEFT JOIN alan    a ON a.alan_id   = s.alan_id
LEFT JOIN sube    su ON su.sube_id = s.sube_id
LEFT JOIN personel p ON p.personel_id = s.per_id
WHERE s.sozlesme_id = :id
LIMIT 1
";
$S = $db->gets($sqlSoz, [':id'=>$sozlesme_id]);
if (!$S) {
    die('Sözleşme bulunamadı.');
}

/* 2) Veliler (birden fazla olabilir) */
$veliler = $db->get("
  SELECT v.* 
  FROM veli1 v 
  WHERE v.ogrenci_id = :oid
  ORDER BY v.veli_id ASC
", [':oid' => (int)$S['ogrenci_id']]);

/* 3) Taksitler */
$taksitler = $db->get("
  SELECT t.taksit_id, t.sira_no, t.vade_tarihi, t.tutar, t.odendi_tutar, t.durum
  FROM taksit1 t
  WHERE t.sozlesme_id = :sid
  ORDER BY t.sira_no ASC, t.vade_tarihi ASC
", [':sid' => $sozlesme_id]);

/* (Opsiyonel) Ödemeler – belgeye eklemek istersen
$odemeler = $db->get("
  SELECT od.odeme_id, od.odeme_no, od.tutar, od.odeme_tarihi
  FROM odeme1 od
  WHERE od.sozlesme_id = :sid
  ORDER BY od.odeme_tarihi ASC
", [':sid'=>$sozlesme_id]);
*/

/* Basit toplamlar */
$toplamTaksit = 0.0;
$toplamOdendi = 0.0;
foreach ($taksitler as $t) {
    $toplamTaksit += (float)$t['tutar'];
    $toplamOdendi += (float)$t['odendi_tutar'];
}
$kalan = max(0.0, $toplamTaksit - $toplamOdendi);

/* ————— Aşağıdan sonrası: Görsel çıktı (örnek) ————— */
?>

<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Öğrenci Kayıt Sözleşmesi — <?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root{
            --fg:#111; --muted:#666; --line:#ddd;
            --fs-xxs:15px; --fs-xs:15px; --fs-s:15px; --fs:15px; --fs-l:16px;
        }

        @page {
            size: A4;
            /* üst–alt 15 mm, sağ–sol 18 mm boşluk */
            margin-top: 15mm;
            margin-bottom: 15mm;
            margin-left: 18mm;
            margin-right: 18mm;
        }
        *{box-sizing:border-box}
        html,body{margin:0;color:var(--fg);font:400 var(--fs)/1.45 system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial}

        /* SAYFA YAPISI */
        .page{
            width:210mm; min-height:297mm; margin:0 auto;
            padding:15mm 15mm 14mm; border:1px solid var(--line);
            display:flex; flex-direction:column;
        }
        .sheet{max-width:800px; margin:0 auto; width:100%}
        .spacer{flex:1 1 auto}

        /* ÜST BANT */
        .head{display:grid;grid-template-columns:120px 1fr 120px;align-items:center;gap:10px}
        .head .brand{text-align:center}
        .head img{max-height:64px;max-width:100%;object-fit:contain}
        h1{margin:6px 0 2px;font-size:18px;letter-spacing:.3px}
        .brand small{color:var(--muted);font-size:var(--fs-xs)}

        /* BLOKLAR */
        .meta{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:10px}
        .kv{border:1px solid var(--line);padding:8px;text-align: center;}
        .kv b{display:block;font-size:var(--fs-xs);color:var(--muted)}
        .kv span{font-size:var(--fs)}
        .section{margin-top:14px}
        .section > .title{font-weight:600;border-bottom:1px solid var(--line);padding-bottom:6px;margin-bottom:8px;text-align: center;}
        .twocol{display:grid;grid-template-columns:1.2fr .8fr;gap:12px}

        /* TABLOLAR */
        table{width:100%;border-collapse:collapse;font-size:var(--fs-xs)}
        th,td{border:1px solid var(--line);padding:6px 8px;vertical-align:top}
        th{background:#f7f7f7;text-align:left;font-weight:600}
        .mini td{padding:4px 6px}
        .legal{font-size:var(--fs-xxs);color:#222}
        ol.articles{margin:8px 0 0 18px;padding:0}
        ol.articles li{margin:6px 0}

        /* İMZA ALANLARI (her sayfa altı) */
        .signs{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:12px}
        .signsn{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-top:12px}
        .sign{border:1px dashed var(--line);padding:10px; display:flex;flex-direction:column;justify-content:space-between}
        .signn{padding:10px; display:flex;flex-direction:column;justify-content:space-between}
        .sign b{font-size:var(--fs-xs)}
        .foot{margin-top:10px;padding-top:8px;border-top:1px solid var(--line);font-size:var(--fs-xxs);color:var(--muted);text-align:center}
        .son{text-align: right;}
        .onuc{font-size: 15px;}

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            padding: 12px;
            display: flex;
            justify-content: center; /* ORTALAR */
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
            .toolbar { display: none !important; }

            /* her .page tam kağıda otursun */
            .page {
                min-height: auto !important;   /* fazla yüksekliği kaldır */
                padding: 0 !important;         /* iç boşlukları iptal et */
                border: 0 !important;          /* kenarlığı yazdırmada kapat */
                break-after: page;
                page-break-after: always;
            }

            /* tablo ve bölümlerin bölünmesini engelle */
            .sheet, table, tr, td, th, .section {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            /* ekran boşluğunu sıfırla */
            #doc { margin-top: 0 !important; }
        }

        /* === Ekran Görüntüsü (kenarlık ve padding yalnızca ekranda) === */
        @media screen {
            .page {
                padding: 15mm 18mm 14mm;        /* ekranda görünüm için boşluk */
                border: 1px solid var(--line);  /* yalnız ekranda kenarlık */
            }
        }
    </style>

</head>
<body>
<div class="toolbar no-print">
    <button id="btnPrint" type="button">
        <i class="fa-solid fa-print"></i> Yazdır
    </button>
</div>
<div id="doc">
    <section class="page">
        <div class="sheet">

            <!-- Üst bant -->
            <div class="head">
                <div><img src="assets/logo/meblogo.png" alt="Sol Logo"></div>
                <div class="brand">
                    <h1>NGLS YABANCI DİL DÜNYASI EĞİTİM HİZMETLERİ LTD. ŞTİ.</h1>
                    <small>ÖĞRENCİ KAYIT SÖZLEŞMESİ (MUHTELİF KURSLAR) — No: <b><?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?></b></small>
                </div>
                <div style="text-align:right"><img src="assets/logo/nglslogo.png" alt="Sağ Logo"></div>
            </div>

            <!-- Üst meta -->
            <div class="meta">
                <div class="kv"><b>Kayıt Tarihi</b><span><?= trDate($S['sozlesme_tarihi']) ?></span></div>
                <div class="kv"><b>Kayıtlı Olduğu Dönem</b><span><?= htmlspecialchars($S['donem_adi'] ?? '-') ?></span></div>
                <div class="kv"><b>Toplam Sözleşme Tutarı</b><span><?= tl($S['toplam_ucret'] ?? 0) ?></span></div>
            </div>

            <!-- Öğrenci / Veli -->
            <div class="section">
                <div class="title">Öğrenci ve Veli Bilgileri</div>
                <table class="mini">
                    <tr>
                        <th style="width:22%">Öğrenci Adı Soyadı</th><td><?= htmlspecialchars(($S['ogrenci_adi'] ?? '').' '.($S['ogrenci_soyadi'] ?? '')) ?></td>
                        <th style="width:18%">TC</th><td><?= htmlspecialchars($S['ogrenci_tc'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Doğum Tarihi  </th><td><?= trDate($S['ogrenci_dogumtar'] ?? null) ?></td>
                        <th>Cinsiyet</th><td><?= $cinsiyet = ($S['ogrenci_cinsiyet'] == 1) ? 'Erkek' : 'Kadın'; ?></td>
                    </tr>
                    <tr>
                        <th>Okulu</th><td><?= htmlspecialchars($S['sube_adi'] ?? '-') ?> Dil Kursu</td>
                        <th>Kurs / Sınıf</th><td><?= htmlspecialchars(($S['sinif_adi'] ?? '-'). ' / '.($S['grup_adi'] ?? '-')) ?></td>
                    </tr>
                    <?php if (!empty($veliler)): ?>
                            <?php foreach ($veliler as $v): ?>
                                <tr>
                                    <th>Veli Adı Soyadı</th><td><?= htmlspecialchars(($v['veli_adi'] ?? '').' '.($v['veli_soyadi'] ?? '')) ?></td>
                                    <th>Veli Tel</th><td><?= htmlspecialchars($v['veli_tel'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Veli Adresi</th><td colspan="3"><?= nl2br(htmlspecialchars($v['veli_adres'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <th>Veli Durumu</th><td>Kendisi</td>
                            <th>Telefon</th><td><?= htmlspecialchars($S['ogrenci_tel'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Adresi</th><td colspan="3"><?= htmlspecialchars($S['ogrenci_adres'] ?? '-') ?></td>
                        </tr>
                    <?php endif; ?>


                </table>
            </div>

            <!-- Taksit & Sözleşme özet -->
           <?php if (!function_exists('tl')) {
            function tl($n){ return number_format((float)$n, 2, ',', '.'); } // "12.345,67"
            }
            if (!function_exists('dtr')) {
            function dtr($date){ return $date ? date('d.m.Y', strtotime($date)) : ''; }
            }

            // Taksitleri kronolojik sırada alındı varsayıyoruz.
            // $taksitler: [ ['sira_no'=>1,'vade_tarihi'=>'YYYY-MM-DD','tutar'=>123.45], ... ]
            $taksitler = $taksitler ?? [];
            usort($taksitler, fn($a,$b) => ($a['sira_no']??0) <=> ($b['sira_no']??0));

            // Özet hesaplar
            $sumTaksit   = 0.0;
            $firstVade   = null;
            $lastVade    = null;
            foreach ($taksitler as $t) {
            $sumTaksit += (float)($t['tutar'] ?? 0);
            $v = $t['vade_tarihi'] ?? null;
            if ($v) {
            if ($firstVade === null || $v < $firstVade) $firstVade = $v;
            if ($lastVade  === null || $v > $lastVade)  $lastVade  = $v;
            }
            }

            // Sağ tarafta kullanılacak alanlar
            $sozlesmeNo   = $S['sozlesme_no']    ?? '-';
            $taksitSayisi = (int)($S['taksit_sayisi'] ?? count($taksitler));
            $taksitBas    = $firstVade ?: ($S['baslangic_tarihi'] ?? null);
            $taksitBit    = $lastVade  ?: ($S['bitis_tarihi']     ?? null);

            // Toplam & Birim Ücret
            $toplamUcret  = (float)($S['net_ucret'] ?? $S['toplam_ucret'] ?? $sumTaksit);
            $urunAdedi    = 1; // elinde “miktar” yoksa 1 yazıyoruz
            $birimUcret   = $toplamUcret / max(1,$urunAdedi);

            // 12 satıra tamamlamak için taksit dizisini dolduralım
            $rows = [];
               for ($i=0; $i<12; $i++) {
                   if (isset($taksitler[$i])) {
                       $r = $taksitler[$i];
                       $rows[] = [
                           'no'    => (int)($r['sira_no'] ?? ($i+1)),
                           'tarih' => dtr($r['vade_tarihi'] ?? ''),
                           'tutar' => tl($r['tutar'] ?? 0),
                       ];
                   } else {
                       // boş satırlar için &nbsp; koyuyoruz
                       $rows[] = ['no'=>'&nbsp;','tarih'=>'&nbsp;','tutar'=>'&nbsp;'];
                   }
               }
            ?>
            <div class="section">
                <div class="title">Ödeme Planı ve Sözleşme Özeti</div>
                <div class="twocol">
                    <!-- Sol: Taksit Tablosu -->
                    <div>
                        <table>
                            <thead>
                            <tr>
                                <th style="width:12%">No</th>
                                <th style="width:28%">Tarih</th>
                                <th style="width:22%">Tutar (₺)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= $r['no'] === '&nbsp;' ? '&nbsp;' : htmlspecialchars($r['no']) ?></td>
                                    <td><?= $r['tarih'] === '&nbsp;' ? '&nbsp;' : htmlspecialchars($r['tarih']) ?></td>
                                    <td><?= $r['tutar'] === '&nbsp;' ? '&nbsp;' : htmlspecialchars($r['tutar']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="2" style="text-align:right">Toplam</th>
                                <th><?= tl($toplamUcret) ?>₺</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Sağ: Sözleşme bilgileri -->
                    <div>
                        <table class="mini">
                            <tr><th>Sözleşme No</th><td><?= htmlspecialchars($sozlesmeNo) ?></td></tr>
                            <tr><th>Toplam Taksit Sayısı</th><td><?= (int)$taksitSayisi ?></td></tr>
                            <tr><th>Taksit Başlama</th><td><?= htmlspecialchars(dtr($taksitBas)) ?></td></tr>
                            <tr><th>Taksit Bitiş</th><td><?= htmlspecialchars(dtr($taksitBit)) ?></td></tr>
                            <tr><th>Ürün Adedi</th><td><?= (int)$urunAdedi ?></td></tr>
                            <tr><th>Birim Ücret</th><td><?= tl($birimUcret) ?></td></tr>
                        </table>
                        <div class="kv" style="margin-top:8px">
                            <b>Açıklama</b>
                            <span>Yukarıdaki ödeme planına uygun olarak ödemeler yapılacaktır.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kısa konu metni -->
            <div class="section">
                <div class="title">Sözleşmenin Konusu</div>
                <div class="legal onuc">
                    İş bu sözleşmenin konusu, yukarıda ödeme planı yapılmış <b><?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?></b> sözleşme numaralı ve toplam kurs ücreti <b><?= tl($S['toplam_ucret'] ?? 0) ?></b> olan Kursiyer Kayıt Sözleşmesine istinaden,
                    NGLS YABANCI DİL DÜNYASI EĞİTİM HİZMETLERİ LTD. ŞTİ. ile (veli/öğrenci) arasındaki esasların belirlenmesidir.
                </div>
            </div>

            <div class="spacer"></div>

            <!-- Sayfa 1 İmzalar -->
            <div class="signsn">
                <div class="signn">
                    Adı Soyadı : ____________________
                </div>

                <div class="signn son"  >
                    İmza: ____________________
                </div>
            </div>
            <div class="foot">Sözleşme No: <?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?></div>
        </div>
    </section>
    <section class="page">
        <div class="sheet">
            <div class="section">
                <ol class="articles legal onuc" start="1">
                    <li>
                        <b>Milli Eğitim Bakanlığı Özel Öğretim Kurumları Yönetmeliği’ne göre uyulması gereken kurallar;</b><br>
                        a) Kuruma; radyo, TV, teyp, gazete, broşür, gayri ahlaki veya siyasi muhtevalı yayınlar getirilemez, yayın yapabilecek ya da kayıt yapabilecek cihazları kurumun imzalı- yazılı izni olmadan kullanamaz izinsiz video kaydı ya da ses kaydı yapamaz.<br>
                        b) Ders içinde veya dışında hiçbir siyasi ve ideolojik konuşma yapılamaz.<br>
                        c) Kuruma alkollü gelinemez, bina içinde, bahçede ve okul müştemilatında sigara içilemez.<br>
                        d) Öğrenci, kurum demirbaşlarına zarar veremez, verirse bedeli öğrenci ve velisinden alınır.
                    </li>
                    <li>
                        <b>PDonem</b> Eğitim - Öğretim Dönemi verilecek eğitim hizmetinin bedeli <b><?= tl($S['toplam_ucret'] ?? 0) ?></b>’dir. İş bu eğitim hizmeti bedeli, kuruma, ödeme planına uygun olarak ödenecek olup, üst üste iki taksiti vadesinde ödenmediği takdirde ayrıca ihtar ve ihbara gerek olmaksızın eğitim hizmet bedelinin tamamının muacceliyet kesbedeceğini ve bu tarihten itibaren geciken taksitlere ait kanuni gecikme zammının veli tarafına ait olacağı kabul ve taahhüt edilmiştir.
                    </li>
                    <li>
                        Milli Eğitim Bakanlığı Özel Öğretim Kurumları Yönetmeliği’ne göre, herhangi bir sebeple kurumdan ayrılmak isteyen öğrencilerden ;<br>
                        a) Öğretim yılı başlamadan ayrılanlara, yıllık eğitim bedelinin yüzde onu (%10) kesildikten sonraki kısmı iade edilir.<br>
                        b) Öğretim yılı başladıktan sonra ayrılanlara, iş bu sözleşmede belirtilen yıllık eğitim bedelinin yüzde onu (%10) ile öğrenim gördüğü günlere göre hesaplanan miktar kesildikten sonraki kısmı iade edilir. Kursiyerin-Öğrencinin öğrenim gördüğü günler hesaplanırken, nakil talebinden önce yapılan mazeretli ve mazeretsiz devamsızlıklar da öğrencinin öğrenim gördüğü günler içinde sayılacaktır. (Resmi Gazete Tarihi: 20.03.2012 Resmi Gazete Sayısı: 28239 ile Değişik RG¬21/7/2012¬28360)<br>
                        c) Öğrencilere, MEB yayınları ve Ek yayınlar ise ücretsiz verilir. Öğrenci kayıt iptalinde veya farklı bir kuruma naklinde yayın fiyatını ödemek zorundadır. Bu bedel <b><?= htmlspecialchars($S['donem_adi'] ?? '-') ?></b> Eğitim–Öğretim Döneminde verilen kaynaklar için <b>1500₺</b>’dir.(BinBeşyüzTürkLirası).
                    </li>
                    <li>
                        Yukarıdaki hesaplamalara göre: Eğitim ücretinin faturası, verilen hizmetin ifasından sonra düzenlenir.
                    </li>
                    <li>Eğitim ücreti ödemesinin banka aracılığıyla yapılması durumunda (Kredi Kartı, Otomatik Ödeme, MailOrder) banka ile veli arasındaki uyuşmazlıklar kurumu bağlamaz. Kurumun alacağının kalması durumunda ise bu alacak miktarı veliden tahsil edilir.</li>
                    <li>Teşviğe hak kazanan öğrencinin, hak kazandığı teşvik bedeli eğitim bedelinden düşülür.</li>
                    <li>NGLS YABANCI DİL DÜNYASI EĞİTİM HİZMETLERİ LTD. ŞTİ. ŞİRKETİ’nin öğrenciye vereceği eğitim - öğretim ve etkinlik programı, karşılıklı yükümlülükleri belirleyen bu sözleşme karşılıklı olarak okunarak kabul edilmiştir.</li>
                    <li>İş bu sözleşmeden meydana gelecek ihtilaflarda <b>İSTANBUL ANADOLU</b> Adliyesi Mahkeme ve İcra Müdürlüklerinin yetkili olacağı taraflarca kabul edilmiştir. Taraflar iş bu sözleşmede belirttikleri adreslerinin yasal tebligat ve ikametgâh adresi olduğunu, bu adreslerinin değişmesi durumunda bu değişikliği karşı tarafa 15(Onbeş) iş günü içerisinde bildireceğini, aksi takdirde sözleşmede yazılı adreslere yapılacak tebligat ve bildirimlerin kendilerine yapılmış sayılacağını kabul ve taahhüt etmişlerdir. İş bu sözleşme taraflarca okunarak iki nüsha ve iki sayfa şeklinde düzenlenerek imzalanmış olup birer nüsha taraflara teslim edilmiştir.</li>
                    <li>
                        Grup(sınıf) derslerinde İlk öğretim Birinci Kademe, İlköğretim ikinci kademe ve Orta Öğretim Öğrencilerinin tamamında, Liseye hazırlık ve Lise gruplarında ders saati(40dk) ücreti: <b>500₺</b> olarak kurum maliyeti hesaplanmış ve belirlenmiştir. Kişisel Gelişim Kurs Gruplarında(A Grubu KPSS Alan Bilgisi Hazırlık Kurs Programı, B Grubu KPSS Eğitim Bilimleri Hazırlık Kursu, Almanca Kursu, Çocuklar için İngilizce Erken Dil Öğretimi Kursu, İngilizce Kurs Programı, İngilizce Yabancı Dil Bilgisi Seviye tespit Sınavına(YDS) Hazırlık Kurs Programı, KPSS Genel Kültür Genel Yetenek Hazırlık Kurs Programı; Ders Ücreti Milli eğitim Müdürlüğüne Bildirilen ders ücret bildirgesi temel alınarak kurum maliyeti hesaplanarak belirlenmiştir. Özel Ders Gruplarında 1 Ders saati Ücreti <b>1500₺</b> olarak kurum maliyeti hesaplanarak belirlenmiştir. Kurum bu tutar üzerinden %50’yi geçmeyecek şekilde indirim yapabilir. İptal hesaplamaları kurum muhasebe birimi tarafından bu değerler üzerinden kursiyerin aldığı ders sayısına göre hesaplanacaktır. Öğrencinin aldığı ders sayısı ile birim fiyat çarpılarak hesaplama yapılır ve sözleşmedeki diğer giderler eklenerek(kırtasiye ücreti, dosya ücreti, kitap ve kaynak ücreti), sözleşme iptal bedeli hesaplanmış olur.

                    </li>
                </ol>
            </div>

            <div class="spacer"></div>

            <!-- Sayfa 2 İmzalar -->
            <div class="signsn">
                <div class="signn">
                    Adı Soyadı : ____________________
                </div>

                <div class="signn son"  >
                    İmza: ____________________
                </div>
            </div>
            <div class="foot">Sözleşme No: <?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?></div>
        </div>
    </section>
    <section class="page">
        <div class="sheet">
            <div class="section">
                <ol class="articles legal onuc" start="5">

                    <li>
                        <b>Ders İptalleri ve Devamsızlık:</b><br>
                        a-) Ders iptalleri ve devamsızlık konusunda, kursiyer hassas davranmak zorundadır. Eğitimin verimliliği, kalitesinin korunması her iki tarafında yükümlülüğüdür. Bu durum çerçevesinde kurumun zarara uğramamasının ve kursiyerin eğitiminin aksamamasının gereğidir. Kursiyer giremeyeceği dersi/dersleri ders gününden bir gün önce saat:18.00’a kadar kuruma yazılı olarak bildirmek zorundadır; aksi durumda kursiyerin giremediği ders, toplam kalan ders sayısından düşülerek ders işlenmiş kabul edilir.<br>
                        b-) Kursiyerin toplam devamsızlığı ve ders iptal sınırı %20’yi aşamaz. Bu durum: Kursiyerin planlanmış aylık ders saatinin ancak %20’sinin mazaretli olarak iptal edilebileceğini ifade eder. Mazaret belirtmeden yapılan iptaller ders saatinin işlenmiş kabul edilmesi ile sonuçlanır.<br>
                        c-) Kursiyerin bu sözleşmeye konu olan eğitimde özel ders şeklinde alacağı eğitimlerde, sözleşme ödeme planı ile eğitim planı parelel oluşturulmuştur. Bu durumda; taraflar, ödeme planı ödenerek tamamladığında, eğitiminde son bulmasında mutabık kalmışlardır. Devamsızlık durumu/hakkı göz önüne alınarak kursiyer ödeme planındaki son vade tarihi bittikten sonra en geç 30 gün içerisinde alamadığı derslerin planlanması ve derslerini almaya başlaması için kurum ile iletişime geçerek dersleri 30 gün içerisinde alabilir. Kurumdan kaynaklı bir problem olmadığı sürece bu durum 30 gün ile sınırlandırılmıştır. 30 günün sonunda alınmayan dersler; işlenmiş kabul edilir. Hakkaniyet çerçevesinde, alternatif olarak kurum tarafından belirlenen günün güncel fiyatı ile eski birim fiyatı üzerinden kıyaslama yapılıp güncel fiyattan kalan dersler için ödeme planı oluşturulur. Önceki birim fiyatı ile kalan dersler hesaplanarak güncel ücretten çıkarılır ve bu şekilde alınamayan derslerin fiyat güncellemesi yapılabilir.
                    </li>
                    <li>Kişisel Verilerin İşlenmesi ve Kullanılması Hakkında: <div class="legal onuc">
                            Kişisel verilerin işlenmesi aydınlatma metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında gerçekleştirilecek aydınlatma yükümlülüğü sırasında uyulacak usul ve esasları belirleyen “Aydınlatma Yükümlülüğünün Yerine Getirilmesinde Uygulanacak Usul ve Esaslar Hakkında Tebliğ” kapsamında hazırlanmıştır.<br><br>

                            Şirketimiz, 6698 Sayılı Kanun’da tanımlandığı şekli ile “Veri Sorumlusu” sıfatını haizdir. Şirket ile ilişkili tüm şahıslara ait her türlü kişisel verilerin 6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVK Kanunu”)’na uygun olarak işlenerek, muhafaza edilmesine özen göstermekteyiz. Kişisel verileriniz aşağıdaki kapsamda işlenmektedir.<br><br>

                            <b>Veri Sorumlusu ve Temsilcisi</b><br>
                            Şirketimiz, 6698 Sayılı Kişisel Verilerin Korunması Kanunu kapsamında “Veri Sorumlusudur”.<br><br>

                            <b>Kişisel Verilerin Hangi Amaçla İşleneceği:</b><br>
                            Kişisel verileriniz Şirketimiz tarafından sağlanan ürünler, hizmetler, ticari faaliyetler, insan kaynakları faaliyetleri ve tesis /bina/saha güvenlik önlemleri kapsamında toplanıp saklanmaktadır. Güvenlik amacıyla bina girişlerinde ziyaretçi kaydı alınıp açık ve kapalı alanlar güvenlik kamerası ile izlenmektedir.<br>
                            Toplanan bütünün kişisel veriler hukuki ve ticari gereklerden dolayı şirketimiz veri envanterinde belirlenen süreler doğrultusunda işlenmeye devam etmektedir.<br><br>

                            <b>Kişisel Verilerin Kimlere ve Hangi Amaçla Aktarılabileceği:</b><br>
                            Kişisel Verileriniz, ilgili mevzuat kapsamında işveren olarak; dolaylı/doğrudan yurtiçi ve yurtdışı iştiraklerimiz ya da bağlı ortaklıklarımız, şirketimizce hizmet/destek/danışmanlık alınan ya da işbirliği yapılan ya da proje/program/finansman ortağı olunan yurt içi/yurt dışı/uluslararası, kamu/özel kurum ve kuruluşlar, şirketler ve sair 3. kişi ya da kuruluşlara aktarılabilecektir.<br><br>

                            <b>Kişisel Veri Toplamanın Yöntemi ve Hukuki Sebebi:</b><br>
                            Kişisel verileriniz sözlü, yazılı ve elektronik ortamlarda otomatik veya otomatik olmayan yöntemlerle toplanıp saklanmaktadır.<br><br>


                        </div></li>
                </ol>
            </div>

            <div class="spacer"></div>

            <!-- Sayfa 3 İmzalar -->
            <div class="signsn">
                <div class="signn">
                    Adı Soyadı : ____________________
                </div>

                <div class="signn son"  >
                    İmza: ____________________
                </div>
            </div>
            <div class="foot">Sözleşme No: <?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?></div>
        </div>
    </section>
    <section class="page">
        <div class="sheet">
            <div class="section">
                <ol class="articles legal onuc" start="5">

                    <li><b>Kişisel Veri Sahibinin 6698 sayılı Kanun’un 11. maddesinde Sayılan Hakları:</b> <div class="legal onuc">

                            <br>
                            Şirketimize müracaat ederek 6698 Sayılı Kanun’un 11. Maddesi uyarınca; kişisel verilerinizin işlenip islenmediğini, şayet islenmişse, buna ilişkin bilgileri, islenme amacını ve bu amaca uygun kullanılıp kullanılmadığını ve söz konusu verilerin aktarıldığı yurt içinde veya yurt dışındaki 3. kişileri öğrenme, kişisel verileriniz eksik ya da yanlış̧ islenmişse bunların düzeltilmesini, kişisel verilerinizin Kanunun 7. maddesinde öngörülen şartlar çerçevesinde silinmesini ya da yok edilmesini ve bu kapsamda şirketimizce yapılan işlemlerin bilgilerin aktarıldığı üçüncü kişilere bildirilmesini talep etme, kişisel verilerinizin münhasıran otomatik sistemler ile analiz edilmesi nedeniyle aleyhinize bir sonucun ortaya çıkması halinde buna itiraz etme ve kanuna aykırı olarak işlenmesi sebebiyle zarara uğramanız halinde zararın giderilmesini talep etme haklarına sahip  bulunmaktasınız.<br><br>



                            6698 Sayılı Kanun’da yer aldığı şekli ile burada belirtilen haklarınızı kullanmanız mümkün olacaktır. Ancak taleplerinizin yerine getirilmesi için ek bir maliyet gerektirmesi halinde Şirketimizin, 6698 Sayılı Kanun’un “Veri Sorumlusuna Başvuru” başlıklı 13. maddesinde belirtilen esaslar uyarınca Kişisel Verileri Koruma Kurulu’nca belirlenen tarifesine göre ücret talep etme hakkımız saklıdır.<br><br>

                            Sözleşme bir bütün olup yukarıda/önceki sayfalarda belirlenmiş maddeler taraflarca sözleşme tarihinden itibaren geçerli kabul edilir. Ödeme planı ve sözleşme tutarının karşılığında taahhüt edilen eğitim hizmeti  NGLS YABANCI DİL DÜNYASI EĞİTİM HİZMETLERİ LTD. ŞTİ. ŞİRKETİ’nde Eğitim Öğretim hizmeti alacak olan (veli/öğrenci) ile NGLS YABANCI DİL DÜNYASI EĞİTİM HİZMETLERİ LTD. ŞTİ. ŞİRKETİ arasındaki esasların kararlaştırılarak düzenlenmesini konu almıştır.<br><br>

                            İş bu sözleşme taraflarca okunarak dört sayfa ve iki nüsha şeklinde düzenlenerek imzalanmış olup birer nüsha taraflara teslim edilmiştir.
                        </div></li>
                </ol>
            </div>

            <div class="spacer"></div>

            <!-- Sayfa 3 İmzalar -->
            <?php
            // Öğrenci ve veli adlarını belirle
            $ogrAdSoyad  = trim(($S['ogrenci_adi'] ?? '').' '.($S['ogrenci_soyadi'] ?? ''));
            $veliAdSoyad = !empty($veliler)
                ? trim(($veliler[0]['veli_adi'] ?? '') . ' ' . ($veliler[0]['veli_soyadi'] ?? ''))
                : $ogrAdSoyad;
            ?>

            <div class="signs">
                <div class="sign">
                    <b>Kurum Yetkilisi</b>
                    <div>&nbsp;<?= htmlspecialchars(trim(($S['personel_adi'] ?? '').' '.($S['personel_soyadi'] ?? '')) ?: '-') ?></div>
                    <div>İMZA</div>
                </div>

                <div class="sign">
                    <b>Kursiyer</b>
                    <div><?= htmlspecialchars($ogrAdSoyad ?: '-') ?></div>
                    <div>İMZA</div>
                </div>

                <div class="sign">
                    <b>Veli</b>
                    <div><?= htmlspecialchars($veliAdSoyad ?: '-') ?></div>
                    <div>İMZA</div>
                </div>
            </div>
            <div class="foot">Sözleşme No: <?= htmlspecialchars($S['sozlesme_no'] ?? '-') ?></div>
        </div>
    </section>
</div>

<!-- Yazdırma JS (Chrome uyumlu: gizli iframe ile) -->
<script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('btnPrint');
        if(!btn) return;

        btn.addEventListener('click', function(e){
            e.preventDefault();

            var src = document.getElementById('doc');
            if(!src) return;

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
            doc.write('<!doctype html><html lang="tr"><head><meta charset="utf-8"><title>Yazdır</title>'+
                styles + '</head><body>' + src.innerHTML + '</body></html>');
            doc.close();

            iframe.onload = function(){
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                setTimeout(() => document.body.removeChild(iframe), 500);
            };
        });
    });
</script>
</body>
</html>
