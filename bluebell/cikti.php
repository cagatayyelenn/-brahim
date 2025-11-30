<?php
// 1. Composer ile yüklediğiniz mPDF kütüphanesini projenize dahil edin
require_once __DIR__ . '/vendor/autoload.php';

// --- VERİTABANINDAN GELECEK ÖRNEK VERİLER ---
// Normalde bu verileri veritabanından bir sorgu ile çekersiniz.
$sozlesme_verileri = [
    'PSozlesmeNo'           => '2025-10-001',
    'PSozlesmeTarihi'       => '22.10.2025',
    'PDonem'                => '2025-2026 Güz Dönemi',
    'PSozlesmeTutari'       => '24.000,00',
    'POgrAdSoyad'           => 'Zeynep Kaya',
    'PKimlikNo'             => '12345678901',
    'PDogumTarihi'          => '15.05.2010',
    'PDogumYeri'            => 'İstanbul',
    'PCinsiyet'             => 'Kadın',
    'POkulAdi'              => 'Atatürk İlköğretim Okulu',
    'PSinif'                => 'İngilizce A2',
    'PSube'                 => 'Hafta Sonu',
    'PVeliAdSoyad'          => 'Ahmet Kaya',
    'PVeliCepTel'           => '0555 123 45 67',
    'PVeliAdres'            => 'Örnek Mah. Cumhuriyet Cad. No:12 D:3 Maltepe / İstanbul',
    'PTaksitSayisi'         => 12,
    'PTaksitBaslamaTarihi'  => '01.11.2025',
    'PTaksitBitisTarihi'    => '01.10.2026',
    'PUrunAdet1'            => '1',
    'PUrunBirimFiyat1'      => '24.000,00',
];

// Taksitler için örnek bir döngü. Bu da normalde veritabanından gelir.
$taksitler_html = '';
for ($i = 1; $i <= 12; $i++) {
    // Örnek taksit verileri oluşturuluyor
    $taksit_no = 'PTaksitNo' . $i;
    $taksit_tarihi = 'PTaksitTarihi' . $i;
    $tutar = 'PTaksitTutari' . $i;

    // Her bir satır için HTML oluşturuluyor
    $taksitler_html .= "
        <tr>
            <td>{$i}</td>
            <td>01." . (($i + 10) % 12 + 1) . "." . (2025 + floor(($i+9)/12)) ."</td>
             
            <td style='text-align:right;'>2.000,00</td>
        </tr>
    ";
}


// --- mPDF OLUŞTURMA İŞLEMİ ---

try {
    // 2. mPDF nesnesini oluşturun. Font ayarı Türkçe karakterler için kritiktir.
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'dejavusans',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'margin_header' => 0,
        'margin_footer' => 0,
    ]);

    // 3. HTML şablon dosyasını oku
    $html = file_get_contents('sozlesmeler/sozlesme.html');
    if ($html === false) {
        throw new Exception("Sözleşme şablonu (sozlesme.html) okunamadı.");
    }

    // 4. Şablondaki yer tutucuları gerçek verilerle değiştir
    foreach ($sozlesme_verileri as $key => $value) {
        // #PSozlesmeTutari#¬TL gibi özel durumlar için de replace yapılıyor
        $html = str_replace($key, $value, $html);
        $html = str_replace('#'.$key.'#¬TL', number_format(floatval(str_replace(',', '', $value)), 2, ',', '.') . ' ₺', $html);
        $html = str_replace('#'.$key.'#', $value, $html);
    }

    // 5. Taksit tablosunu dinamik olarak oluşturulan HTML ile değiştir
    // Şablondaki <tbody>...</tbody> arasını bu blokla değiştireceğiz.
    // Bu yöntem, statik satırları silip yerine dinamik olanları koyar.
    $pattern = '/<tbody>(.*?)<\/tbody>/s';
    $replacement = '<tbody>' . $taksitler_html . '</tbody>';
    $html = preg_replace($pattern, $replacement, $html, 1); // Sadece ilk tabloyu etkilemesi için limit 1

    // 6. PDF'i oluştur
    $mpdf->WriteHTML($html);

    // 7. PDF'i tarayıcıda göstermek için çıktıyı gönder
    // 'I' -> Inline (Tarayıcıda göster), 'D' -> Download (İndir), 'F' -> File (Dosyaya kaydet)
    $fileName = 'sozlesme-' . $sozlesme_verileri['PSozlesmeNo'] . '.pdf';
    $mpdf->Output($fileName, 'I');

} catch (\Mpdf\MpdfException $e) {
    // mPDF'e özgü hataları yakala
    echo "mPDF Hatası: " . $e->getMessage();
} catch (Exception $e) {
    // Genel hataları yakala (örn: dosya okunamadı)
    echo "Bir hata oluştu: " . $e->getMessage();
}

?>