<?php
// sozlesme-yazdir.php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$satis_id = (int)($_GET['satis_id'] ?? 0);
if ($satis_id <= 0) { http_response_code(400); exit('Geçersiz talep'); }

/* ---- Veriler ---- */
$satis = $Ydil->getone("
  SELECT
    ks.*,
    o.ogrenci_id, o.ogrenci_adi, o.ogrenci_soyadi, o.ogrenci_tc,
    o.ogrenci_tel, o.ogrenci_mail, o.ogrenci_dogumtar,
    o.ogrenci_adres, o.donem_id, o.sinif_id, o.sube_id,
    v.veli_adi, v.veli_soyadi, v.veli_tc, v.veli_tel, v.veli_mail, v.veli_adres
  FROM kurs_satislari ks
  JOIN ogrenci o ON o.ogrenci_id = ks.ogrenci_id
  LEFT JOIN veli v ON v.ogrenci_id = ks.ogrenci_id
  WHERE ks.id = {$satis_id}
  LIMIT 1
");
if (!$satis) { http_response_code(404); exit('Sözleşme bulunamadı'); }

$taksitler = $Ydil->get("
  SELECT t.*,
         DATE_FORMAT(t.taksit_tarihi,'%d.%m.%Y') AS taksit_tarihi_fmt,
         CASE t.odeme_tur_id
           WHEN 3 THEN 'NAKİT'
           WHEN 4 THEN 'KREDİ KARTI'
           WHEN 1 THEN 'HAVALE/EFT'
           WHEN 2 THEN 'ÇEK-SENET'
           ELSE ''
         END AS odeme_turu
  FROM taksitler t
  WHERE t.satis_id = {$satis_id}
  ORDER BY t.taksit_tarihi ASC, t.id ASC
") ?: [];

/* ---- Yardımcılar ---- */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($v){ $f = is_numeric($v)?(float)$v:0.0; return number_format($f,2,',','.'); }
function date_tr($v){ if(!$v) return ''; $ts=strtotime($v); return $ts>0?date('d.m.Y',$ts):h($v); }
function val($a,$k,$d=''){ return isset($a[$k]) ? $a[$k] : $d; }

/* ---- Alan eşleştirmeleri ---- */
$sozlesmeNo     = val($satis,'id','');
$sozlesmeTarih  = date_tr(val($satis,'satis_tarihi')) ?: date('d.m.Y');

$kurum = [
    'adi'        => 'NGLS YABANCI DİL DÜNYASI  ',
    'unvan'      => 'NGLS YABANCI DİL DÜNYASI EĞİTİM HİZMETLERİ LTD. ŞTİ.',
    'adres'      => 'Haraparası Mah. Türkiye Yüzyılı AVM, Cemil Meriç Sk. No:105-F5 Antakya / Hatay',
    'vergi_daire'=> 'Küçükyalı Vergi Dairesi Md.',
    'vergi_no'   => '6311374761',
];

$ogrenciAdSoyad = trim(val($satis,'ogrenci_adi').' '.val($satis,'ogrenci_soyadi'));
$ogrenci = [
    'adsoyad'    => $ogrenciAdSoyad ?: '……………………………………',
    'tc'         => val($satis,'ogrenci_tc',''),
    'sinif_sube' => (val($satis,'sinif_id','') ?: ''). (val($satis,'sube_id','') ? ' / Şube: '.val($satis,'sube_id','') : ''),
    'donem'      => val($satis,'donem_id',''),
    'adres'      => val($satis,'ogrenci_adres',''),
    'tel'        => val($satis,'ogrenci_tel',''),
    'mail'       => val($satis,'ogrenci_mail',''),
    'dogum'      => date_tr(val($satis,'ogrenci_dogumtar')),
];

$veliAdSoyad = trim(val($satis,'veli_adi').' '.val($satis,'veli_soyadi'));
$veli = [
    'adsoyad' => ($veliAdSoyad!=='' ? $veliAdSoyad : ''),
    'tc'      => val($satis,'veli_tc',''),
    'tel'     => val($satis,'veli_tel',''),
    'mail'    => val($satis,'veli_mail',''),
    'adres'   => val($satis,'veli_adres',''),
];

/* Ücretler */
$kursAdi      = val($satis,'kurs_adi','');
$miktar       = val($satis,'miktar',1);
$birimFiyat   = val($satis,'birim_fiyat',0);
$toplam       = val($satis,'toplam_tutar',0);
$pesinat      = val($satis,'pesinat_tutari',0);
$kalan        = val($satis,'kalan_tutar',0);

$autoPrint = (isset($_GET['print']) && $_GET['print']=='1');
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>NGLS — Kursiyer Kayıt Sözleşmesi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { size: A4; margin: 16mm; }
        @media print { html, body { width: 210mm; height: 297mm; } .no-print{display:none!important} }
        :root{ --brand:#0a5efb; --ink:#111; --muted:#6c757d; --border:#dee2e6; }
        *{ box-sizing:border-box }
        html,body{ margin:0; padding:0; color:var(--ink); font:12.5px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
        .page{ max-width: 920px; margin: 18px auto; padding: 0 8px; }
        .header{ display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .header .brandblock{ text-align:center; flex:1; }
        .logo{ height:58px; object-fit:contain; }
        .org{ font-weight:800; color:var(--brand); letter-spacing:.2px; font-size:18px; }
        .subtitle{ color:var(--muted); font-size:12px; margin-top:2px; }
        .title{ margin:14px 0 10px; text-align:center; font-weight:800; letter-spacing:.3px; font-size:20px; }
        .section-title{ margin:16px 0 8px; font-weight:800; letter-spacing:.2px; border-left:4px solid var(--brand); padding-left:8px; }
        .grid{ display:grid; grid-template-columns:1fr 1fr; gap:8px 16px; }
        .field{ display:flex; gap:10px; align-items:baseline; }
        .label{ min-width:160px; color:#333; font-weight:600; font-size:12px; text-transform:uppercase; }
        .value{ font-weight:600; }
        table{ width:100%; border-collapse:collapse; margin:8px 0; font-size:12px; }
        thead th{ background:#f6f8ff; border:1px solid var(--border); padding:8px 10px; text-align:left; font-weight:700; }
        tbody td{ border:1px solid var(--border); padding:8px 10px; }
        .note{ margin:10px 0; padding:10px 12px; border:1px dashed var(--border); border-radius:8px; background:#fafbff; }
        .info{ margin:10px 0; padding:10px 12px; border:1px solid var(--border); border-radius:8px; background:#f8f9fa; }
        .signs{ display:flex; gap:14px; margin:14px 0 0; }
        .sign{ flex:1; border:1px solid var(--border); border-radius:8px; padding:12px; }
        .sign h4{ margin:0 0 8px; font-size:13px }
        .line{ margin-top:34px; height:1px; background:var(--border); }
        .sm{ font-size:12px; color:var(--muted); }
        .legal{ margin-top:8px; font-size:12.5px; }
        .legal p{ margin:8px 0; text-align:justify; }
        .stamp{
            float:right; margin-top:6px; padding:4px 10px; border:1px solid var(--border);
            border-radius:999px; color:#2b7a0b; background:#ecfdf3; font-size:12px
        }
        .break{ page-break-before:always; margin-top:18px; }
    </style>
</head>
<body>
<div class="page">

    <!-- ÜST LOGO ŞERİDİ (ekranda görünür, yazdırırken istersen kaldırabilirsin) -->
    <div class="header no-print">
        <img class="logo" src="assets/img/logo-left.png" alt="Sol Logo">
        <div class="brandblock">
            <div class="org"><?= h($kurum['adi']) ?></div>
            <div class="subtitle">Kursiyer Kayıt Sözleşmesi</div>
        </div>
        <img class="logo" src="assets/img/logo-right.png" alt="Sağ Logo">
    </div>

    <div class="stamp">SÖZLEŞME NO: <b><?= h($sozlesmeNo) ?></b></div>
    <h1 class="title">KURSİYER KAYIT SÖZLEŞMESİ</h1>

    <!-- SÖZLEŞME / KURSİYER BİLGİLERİ -->
    <h3 class="section-title">Sözleşme / Kursiyer Bilgileri</h3>
    <div class="grid">
        <div class="field"><div class="label">Sözleşme Tarihi</div><div class="value"><?= h($sozlesmeTarih) ?></div></div>
        <div class="field"><div class="label">Eğitim Dönemi</div><div class="value"><?= h($ogrenci['donem']) ?></div></div>

        <div class="field"><div class="label">Kursiyer (Ad-Soyad)</div><div class="value"><?= h($ogrenci['adsoyad']) ?></div></div>
        <div class="field"><div class="label">Telefon</div><div class="value"><?= h($ogrenci['tel']) ?></div></div>

        <div class="field"><div class="label">Veli (Ad-Soyad)</div><div class="value"><?= h($veli['adsoyad'] ?: '—') ?></div></div>
        <div class="field"><div class="label">TC / VKN</div><div class="value"><?= h($ogrenci['tc'] ?: $veli['tc']) ?></div></div>

        <div class="field"><div class="label">Adres</div><div class="value"><?= h($ogrenci['adres'] ?: $veli['adres']) ?></div></div>
        <div class="field"><div class="label">Toplam Kurs Ücreti</div><div class="value"><b><?= money($toplam) ?> TL</b></div></div>
    </div>

    <!-- ÖDEME PLANI -->
    <h3 class="section-title">Ödeme Planı</h3>
    <table aria-label="Ödeme Planı">
        <thead>
        <tr>
            <th>#</th>
            <th>Vade Tarihi</th>
            <th>Taksit Tutarı (TL)</th>
            <th>Ödeme Türü</th>
            <th>Açıklama</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $satir = 0; $sum = 0.0;
        if ((float)$pesinat > 0) {
            $satir++; $sum += (float)$pesinat;
            echo '<tr>
          <td>'.$satir.'</td>
          <td>'.h($sozlesmeTarih).'</td>
          <td>'.money($pesinat).'</td>
          <td>NAKİT / KART / EFT</td>
          <td>Peşinat</td>
        </tr>';
        }
        foreach ($taksitler as $i => $t) {
            $satir++; $sum += (float)$t['taksit_tutari'];
            echo '<tr>
          <td>'.($satir).'</td>
          <td>'.h($t['taksit_tarihi_fmt']).'</td>
          <td>'.money($t['taksit_tutari']).'</td>
          <td>'.h($t['odeme_turu']).'</td>
          <td></td>
        </tr>';
        }
        echo '<tr>
        <td colspan="2" style="text-align:right"><b>TOPLAM</b></td>
        <td><b>'.money($sum).'</b></td>
        <td colspan="2"></td>
      </tr>';
        ?>
        </tbody>
    </table>

    <div class="note">
        Bu sözleşmede belirtilen toplam bedel ve ödeme planı taraflarca kabul edilmiştir. Ödemeler, planlanan vadelerde yapılacaktır.
    </div>

    <!-- HUKUK METNİ — TAM -->
    <h3 class="section-title">Sözleşme Hükümleri</h3>
    <div class="legal">
        <p><strong>İş bu sözleşmenin konusu,</strong> Yukarıda ödeme planı yapılmış <strong><?= h($sozlesmeNo) ?></strong> sözleşme numaralı ve <strong>Toplam Kurs Ücreti <?= money($toplam) ?> TL</strong> olan Kursiyer Kayıt Sözleşmesine istinaden <strong><?= h($kurum['unvan']) ?></strong>’nden Eğitim Öğretim hizmeti alacak olan (veli / öğrenci) ile <strong><?= h($kurum['unvan']) ?></strong> arasındaki esasların kararlaştırılarak düzenlenmesidir.</p>


        <p><strong>1-)</strong> Milli Eğitim Bakanlığı Özel Öğretim Kurumları Yönetmeliği’ne göre uyulması gereken kurallar;</p>
        <p>a) Kuruma; radyo, TV, teyp, gazete, broşür, gayri ahlaki veya siyasi muhtevalı yayınlar getirilemez, yayın yapabilecek ya da kayıt yapabilecek cihazları kurumun imzalı- yazılı izni olmadan kullanamaz, izinsiz video kaydı ya da ses kaydı yapamaz.</p>
        <p>b) Ders içinde veya dışında hiçbir siyasi ve ideolojik konuşma yapılamaz.</p>
        <p>c) Kuruma alkollü gelinemez, bina içinde, bahçede ve okul müştemilatında sigara içilemez.</p>
        <p>d) Öğrenci, kurum demirbaşlarına zarar veremez, verirse bedeli öğrenci ve velisinden alınır.</p>

        <p><strong>2-)</strong> <strong><?= h($ogrenci['donem']) ?></strong> Eğitim - Öğretim Dönemi verilecek eğitim hizmetinin bedeli <strong><?= money($toplam) ?> TL</strong>’dir. İş bu eğitim hizmeti bedeli, kuruma, ödeme planına uygun olarak ödenecek olup, üst üste iki taksiti vadesinde ödenmediği takdirde ayrıca ihtar ve ihbara gerek olmaksızın eğitim hizmet bedelinin tamamının muacceliyet kesbedeceğini ve bu tarihten itibaren geciken taksitlere ait kanuni gecikme zammının veli tarafına ait olacağı kabul ve taahhüt edilmiştir.</p>

        <p><strong>3-)</strong> Milli Eğitim Bakanlığı Özel Öğretim Kurumları Yönetmeliği’ne göre, herhangi bir sebeple kurumdan ayrılmak isteyen öğrencilerden;</p>
        <p>a) Öğretim yılı başlamadan ayrılanlara, yıllık eğitim bedelinin yüzde onu (%10) kesildikten sonraki kısmı iade edilir.</p>
        <p>b) Öğretim yılı başladıktan sonra ayrılanlara, iş bu sözleşmede belirtilen yıllık eğitim bedelinin yüzde onu (%10) ile öğrenim gördüğü günlere göre hesaplanan miktar kesildikten sonraki kısmı iade edilir. Kursiyerin-Öğrencinin öğrenim gördüğü günler hesaplanırken, nakil talebinden önce yapılan mazeretli ve mazeretsiz devamsızlıklar da öğrencinin öğrenim gördüğü günler içinde sayılacaktır. (Resmi Gazete Tarihi: 20.03.2012 Resmi Gazete Sayısı: 28239 ile Değişik RG 21/7/2012 28360)</p>
        <p>c) Öğrencilere, MEB yayınları ve Ek yayınlar ise ücretsiz verilir. Öğrenci kayıt iptalinde veya farklı bir kuruma naklinde yayın fiyatını ödemek zorundadır. Bu bedel <strong><?= h($ogrenci['donem']) ?></strong> Eğitim–Öğretim Döneminde verilen kaynaklar için <strong>1500₺</strong>’dir. (BinBeşyüzTürkLirası).</p>

        <p><strong>4-)</strong> Yukarıdaki hesaplamalara göre: Eğitim ücretinin faturası, verilen hizmetin ifasından sonra düzenlenir.</p>

        <p><strong>5-)</strong> Eğitim ücreti ödemesinin banka aracılığıyla yapılması durumunda (Kredi Kartı, Otomatik Ödeme, MailOrder) banka ile veli arasındaki uyuşmazlıklar kurumu bağlamaz. Kurumun alacağının kalması durumunda ise bu alacak miktarı veliden tahsil edilir.</p>

        <p><strong>6-)</strong> Teşviğe hak kazanan öğrencinin, hak kazandığı teşvik bedeli eğitim bedelinden düşülür.</p>

        <p><strong>7-)</strong> <?= h($kurum['unvan']) ?>’nin öğrenciye vereceği eğitim - öğretim ve etkinlik programı, karşılıklı yükümlülükleri belirleyen bu sözleşme karşılıklı olarak okunarak kabul edilmiştir.</p>

        <p><strong>8-)</strong> İş bu sözleşmeden meydana gelecek ihtilaflarda <strong>İSTANBUL ANADOLU</strong> Adliyesi Mahkeme ve İcra Müdürlüklerinin yetkili olacağı taraflarca kabul edilmiştir. Taraflar iş bu sözleşmede belirttikleri adreslerinin yasal tebligat ve ikametgâh adresi olduğunu, bu adreslerinin değişmesi durumunda bu değişikliği karşı tarafa 15 (Onbeş) iş günü içerisinde bildireceğini, aksi takdirde sözleşmede yazılı adreslere yapılacak tebligat ve bildirimlerin kendilerine yapılmış sayılacağını kabul ve taahhüt etmişlerdir.</p>

        <p><em>İş bu sözleşme taraflarca okunarak iki nüsha ve iki sayfa şeklinde düzenlenerek imzalanmış olup birer nüsha taraflara teslim edilmiştir.</em></p>

        <p><strong>9-)</strong> Grup (sınıf) derslerinde İlköğretim Birinci Kademe, İlköğretim ikinci kademe ve Ortaöğretim Öğrencilerinin tamamında, Liseye hazırlık ve Lise gruplarında ders saati (40 dk) ücreti: <strong>500₺</strong> olarak kurum maliyeti hesaplanmış ve belirlenmiştir. Kişisel Gelişim Kurs Gruplarında (A Grubu KPSS Alan Bilgisi Hazırlık Kurs Programı, B Grubu KPSS Eğitim Bilimleri Hazırlık Kursu, Almanca Kursu, Çocuklar için İngilizce Erken Dil Öğretimi Kursu, İngilizce Kurs Programı, İngilizce Yabancı Dil Bilgisi Seviye tespit Sınavına (YDS) Hazırlık Kurs Programı, KPSS Genel Kültür Genel Yetenek Hazırlık Kurs Programı); Ders Ücreti Milli eğitim Müdürlüğüne Bildirilen ders ücret bildirgesi temel alınarak kurum maliyeti hesaplanarak belirlenmiştir. Özel Ders Gruplarında 1 Ders saati Ücreti <strong>1500₺</strong> olarak kurum maliyeti hesaplanarak belirlenmiştir. Kurum bu tutar üzerinden %50’yi geçmeyecek şekilde indirim yapabilir. İptal hesaplamaları kurum muhasebe birimi tarafından bu değerler üzerinden kursiyerin aldığı ders sayısına göre hesaplanacaktır. Öğrencinin aldığı ders sayısı ile birim fiyat çarpılarak hesaplama yapılır ve sözleşmedeki diğer giderler eklenerek (kırtasiye ücreti, dosya ücreti, kitap ve kaynak ücreti), sözleşme iptal bedeli hesaplanmış olur.</p>


        <p><strong>10-)</strong> <u>Ders İptalleri ve Devamsızlık:</u><br>
            a-) Ders iptalleri ve devamsızlık konusunda, kursiyer hassas davranmak zorundadır. Eğitimin verimliliği, kalitesinin korunması her iki tarafın da yükümlülüğüdür. Bu durum çerçevesinde kurumun zarara uğramamasının ve kursiyerin eğitiminin aksamamasının gereğidir. Kursiyer giremeyeceği dersi/dersleri ders gününden bir gün önce saat 18.00’a kadar kuruma <strong>yazılı</strong> olarak bildirmek zorundadır; aksi durumda kursiyerin giremediği ders, toplam kalan ders sayısından düşülerek <strong>ders işlenmiş kabul edilir</strong>.<br>
            b-) Kursiyerin toplam devamsızlığı ve ders iptal sınırı %20’yi aşamaz. Bu durum: Kursiyerin planlanmış aylık ders saatinin ancak %20’sinin mazeretli olarak iptal edilebileceğini ifade eder. Mazeret belirtmeden yapılan iptaller, ders saatinin işlenmiş kabul edilmesi ile sonuçlanır.<br>
            c-) Kursiyerin bu sözleşmeye konu olan eğitimde <strong>özel ders</strong> şeklinde alacağı eğitimlerde, sözleşme ödeme planı ile eğitim planı paralel oluşturulmuştur. Bu durumda; taraflar, <strong>ödeme planı ödenerek tamamlandığında eğitimin de son bulmasında</strong> mutabık kalmışlardır. Devamsızlık durumu/hakkı göz önüne alınarak kursiyer, ödeme planındaki <strong>son vade tarihi bittikten sonra en geç 30 gün</strong> içerisinde alamadığı derslerin planlanması ve derslerini almaya başlaması için kurum ile iletişime geçerek dersleri 30 gün içerisinde alabilir. Kurumdan kaynaklı bir problem olmadığı sürece bu durum 30 gün ile sınırlandırılmıştır. <strong>30 günün sonunda</strong> alınmayan dersler; <strong>işlenmiş kabul edilir</strong>.<br>
            Hakkaniyet çerçevesinde, alternatif olarak kurum tarafından belirlenen günün güncel fiyatı ile eski birim fiyatı üzerinden kıyaslama yapılıp <strong>güncel fiyattan</strong> kalan dersler için ödeme planı oluşturulur. Önceki birim fiyatı ile kalan dersler hesaplanarak güncel ücretten çıkarılır ve bu şekilde alınamayan derslerin <strong>fiyat güncellemesi</strong> yapılabilir.
        </p>

        <p><strong>11-)</strong> <u>Kişisel Verilerin İşlenmesi ve Kullanılması Hakkında;</u> Kişisel verilerin işlenmesi aydınlatma metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında gerçekleştirilecek aydınlatma yükümlülüğü sırasında uyulacak usul ve esasları belirleyen “Aydınlatma Yükümlülüğünün Yerine Getirilmesinde Uygulanacak Usul ve Esaslar Hakkında Tebliğ” kapsamında hazırlanmıştır.</p>

        <p><strong>Veri Sorumlusu ve Temsilcisi:</strong> Şirketimiz, 6698 Sayılı Kişisel Verilerin Korunması Kanunu kapsamında “Veri Sorumlusudur”.</p>

        <p><strong>Kişisel Verilerin Hangi Amaçla İşleneceği:</strong> Kişisel verileriniz Şirketimiz tarafından sağlanan ürünler, hizmetler, ticari faaliyetler, insan kaynakları faaliyetleri ve tesis/bina/saha güvenlik önlemleri kapsamında toplanıp saklanmaktadır. Güvenlik amacıyla bina girişlerinde ziyaretçi kaydı alınıp açık ve kapalı alanlar güvenlik kamerası ile izlenmektedir. Toplanan bütün kişisel veriler hukuki ve ticari gereklerden dolayı şirketimiz veri envanterinde belirlenen süreler doğrultusunda işlenmeye devam etmektedir.</p>

        <p><strong>Kişisel Verilerin Kimlere ve Hangi Amaçla Aktarılabileceği:</strong> Kişisel verileriniz, ilgili mevzuat kapsamında işveren olarak; dolaylı/doğrudan yurtiçi ve yurtdışı iştiraklerimiz ya da bağlı ortaklıklarımız, şirketimizce hizmet/destek/danışmanlık alınan ya da işbirliği yapılan ya da proje/program/finansman ortağı olunan yurt içi/yurt dışı/uluslararası, kamu/özel kurum ve kuruluşlar, şirketler ve sair 3. kişi ya da kuruluşlara aktarılabilecektir.</p>

        <p><strong>Kişisel Veri Toplamanın Yöntemi ve Hukuki Sebebi:</strong> Kişisel verileriniz sözlü, yazılı ve elektronik ortamlarda otomatik veya otomatik olmayan yöntemlerle toplanıp saklanmaktadır.</p>

        <p><strong>Kişisel Veri Sahibinin 6698 sayılı Kanun’un 11. maddesinde Sayılan Hakları:</strong> Şirketimize müracaat ederek 6698 Sayılı Kanun’un 11. maddesi uyarınca; kişisel verilerinizin işlenip işlenmediğini, şayet işlenmişse, buna ilişkin bilgileri, işlenme amacını ve bu amaca uygun kullanılıp kullanılmadığını ve söz konusu verilerin aktarıldığı yurt içinde veya yurt dışındaki 3. kişileri öğrenme, kişisel verileriniz eksik ya da yanlış işlenmişse bunların düzeltilmesini, kişisel verilerinizin Kanunun 7. maddesinde öngörülen şartlar çerçevesinde silinmesini ya da yok edilmesini ve bu kapsamda şirketimizce yapılan işlemlerin bilgilerin aktarıldığı üçüncü kişilere bildirilmesini talep etme, kişisel verilerinizin münhasıran otomatik sistemler ile analiz edilmesi nedeniyle aleyhinize bir sonucun ortaya çıkması halinde buna itiraz etme ve kanuna aykırı olarak işlenmesi sebebiyle zarara uğramanız halinde zararın giderilmesini talep etme haklarına sahipsiniz.</p>



        <p>6698 Sayılı Kanun’da yer aldığı şekli ile burada belirtilen haklarınızı kullanmanız mümkün olacaktır. Ancak taleplerinizin yerine getirilmesi için ek bir maliyet gerektirmesi halinde Şirketimizin, 6698 Sayılı Kanun’un “Veri Sorumlusuna Başvuru” başlıklı 13. maddesinde belirtilen esaslar uyarınca Kişisel Verileri Koruma Kurulu’nca belirlenen tarifesine göre ücret talep etme hakkı saklıdır.</p>

        <p><strong>Sözleşme bir bütün</strong> olup yukarıda/önceki sayfalarda belirlenmiş maddeler taraflarca sözleşme tarihinden itibaren geçerli kabul edilir. Ödeme planı ve sözleşme tutarının karşılığında taahhüt edilen eğitim hizmeti <?= h($kurum['unvan']) ?>’nde Eğitim Öğretim hizmeti alacak olan (veli/öğrenci) ile <?= h($kurum['unvan']) ?> arasındaki esasların kararlaştırılarak düzenlenmesini konu almıştır.</p>

        <p><em>İş bu sözleşme taraflarca okunarak dört sayfa ve iki nüsha şeklinde düzenlenerek imzalanmış olup birer nüsha taraflara teslim edilmiştir.</em></p>
     </div>

    <!-- İMZALAR -->
    <h3 class="section-title">İmzalar</h3>
    <div class="signs">
        <div class="sign">
            <h4>Kurum</h4>
            <div><b><?= h($kurum['adi']) ?></b></div>
            <div class="line"></div>
            <div class="sm" style="text-align:right">İmza / Kaşe</div>
        </div>
        <div class="sign">
            <h4>Kursiyer / Veli</h4>
            <div><b><?= h($ogrenci['adsoyad']) ?><?= $veli['adsoyad'] ? ' / '.h($veli['adsoyad']) : '' ?></b></div>
            <div class="line"></div>
            <div class="sm" style="text-align:right">İmza</div>
        </div>
    </div>

</div>

<script>
    (function(){
        var p = new URLSearchParams(location.search);
        if (p.get('print') === '1') {
            window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 150); });
        }
    })();
</script>
</body>
</html>