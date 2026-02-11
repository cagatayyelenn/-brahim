<?php
header('Content-Type: application/json');
require_once '../dosyalar/config.php';
require_once '../dosyalar/Ydil.php';
require_once '../dosyalar/oturum.php';

$db = new Ydil();

// Helper Functions
function parseMoney($s)
{
    if (!$s)
        return 0;
    $clean = str_replace('.', '', $s);
    $clean = str_replace(',', '.', $clean);
    return (float) $clean;
}

function jsonResponse($ok, $msg, $data = [])
{
    echo json_encode(['ok' => $ok, 'msg' => $msg, 'data' => $data]);
    exit;
}

// Request Method Check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Geçersiz istek metodu.');
}

$action = $_POST['action'] ?? '';
$sozlesme_id = (int) ($_POST['sozlesme_id'] ?? 0);

if ($sozlesme_id <= 0) {
    jsonResponse(false, 'Geçersiz Sözleşme ID.');
}

// Get Contract
$sozlesme = $db->find('sozlesme1', 'sozlesme_id', $sozlesme_id);
if (!$sozlesme) {
    jsonResponse(false, 'Sözleşme bulunamadı.');
}

// Handler Switch
switch ($action) {
    case 'update_basic':
        handleBasicUpdate($db, $sozlesme_id);
        break;

    case 'restructure_financials':
        handleRestructure($db, $sozlesme, $sozlesme_id);
        break;

    case 'terminate_contract':
        handleTermination($db, $sozlesme_id);
        break;

    default:
        jsonResponse(false, 'Geçersiz işlem tipi.');
}

/* -------------------------------------------------------------------------- */
/*                                SUB-FUNCTIONS                               */
/* -------------------------------------------------------------------------- */

function handleBasicUpdate($db, $id)
{
    $fields = [
        'donem_id' => $_POST['donem_id'] ?? null,
        'sinif_id' => $_POST['sinif_id'] ?? null,
        'grup_id' => $_POST['grup_id'] ?? null,
        'alan_id' => $_POST['alan_id'] ?? null,
        // Not: Finansal alanlar burada güncellenmez!
    ];

    // Opsiyonel: Loglama yapılabilir

    $update = $db->update('sozlesme1', $fields, 'sozlesme_id', $id);

    if ($update) {
        jsonResponse(true, 'Temel bilgiler güncellendi.');
    } else {
        jsonResponse(false, 'Güncelleme sırasında hata oluştu veya değişiklik yapılmadı.');
    }
}

function handleRestructure($db, $sozlesme, $id)
{
    $yeniToplamTutar = parseMoney($_POST['yeni_toplam_tutar'] ?? '0');
    $taksitlerJSON = $_POST['taksitler'] ?? '[]';
    $taksitler = json_decode($taksitlerJSON, true);

    if ($yeniToplamTutar <= 0) {
        jsonResponse(false, 'Geçersiz toplam tutar.');
    }

    // 1. Ödenmiş Tutarı Hesapla (Peşinat + Taksit Ödemeleri)
    // Taksit ödemeleri
    $odenenTutarSql = "SELECT SUM(odendi_tutar) as toplam_odenen FROM taksit1 WHERE sozlesme_id = :id";
    $odenen = $db->get($odenenTutarSql, [':id' => $id]);
    $taksitOdenen = (float) ($odenen[0]['toplam_odenen'] ?? 0);

    // Peşinat (odeme1 tablosu)
    $pesinatSql = "SELECT tutar FROM odeme1 WHERE sozlesme_id = :id";
    $pesinat = $db->get($pesinatSql, [':id' => $id]);
    $pesinatTutar = (float) ($pesinat[0]['tutar'] ?? 0);

    $toplamOdenen = $taksitOdenen + $pesinatTutar;

    // 2. Güvenlik Kontrolü: Yeni tutar ödenenden az olamaz (İade yoksa)
    // İade logic'i "Fesih" kısmında. Burada sadece yapılandırma var.
    if ($yeniToplamTutar < $toplamOdenen) {
        jsonResponse(false, 'Yeni sözleşme tutarı, bugüne kadar tahsil edilen tutardan (' . number_format($toplamOdenen, 2) . ' TL) düşük olamaz. İade gerekiyorsa "Sözleşme Fesih" işlemini kullanınız.');
    }

    $db->beginTransaction();
    try {
        // 3. Sözleşme Toplamını Güncelle
        $db->update('sozlesme1', ['toplam_ucret' => $yeniToplamTutar], 'sozlesme_id', $id);

        // 4. Eski Taksitleri Temizle
        // A) Hiç Ödenmemişleri sil
        $silSql = "DELETE FROM taksit1 WHERE sozlesme_id = :id AND odendi_tutar = 0";
        $db->query($silSql, [':id' => $id]);

        // B) Kısmi Ödenmişleri "Kapandı" Olarak Güncelle
        // Eğer bir taksit kısmi ödendiyse, kalan borç zaten yeni yapılandırmanın içinde olacak.
        // Bu yüzden eski taksidin tutarını, ödenen tutara eşitleyerek borcunu sıfırlıyoruz.
        $kismiSql = "UPDATE taksit1 SET tutar = odendi_tutar WHERE sozlesme_id = :id AND odendi_tutar > 0 AND odendi_tutar < tutar";
        $db->query($kismiSql, [':id' => $id]);

        // 5. Yeni Taksitleri Ekle
        // Gelen taksitler sadece "Kalan Borç" için olmalı.
        if (!empty($taksitler)) {
            foreach ($taksitler as $t) {
                $db->insert('taksit1', [
                    'sozlesme_id' => $id,
                    'tutar' => parseMoney($t['tutar']),
                    'vade_tarihi' => $t['tarih'],
                    'odendi_tutar' => 0,
                    'durum' => 0 // Ödenmedi
                ]);
            }
        }

        $db->commit();
        jsonResponse(true, 'Sözleşme başarıyla yapılandırıldı.');

    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(false, 'Hata: ' . $e->getMessage());
    }
}

function handleTermination($db, $id)
{
    $fesihNedeni = $_POST['fesih_nedeni'] ?? '';

    // İşlemi Güvenli Yap
    $db->beginTransaction();
    try {
        // 1. Ödenmemiş (Borç) Taksitlerini Sil
        // Mantık: Ödenenler kasaya girdiği için silinemez. Kalanlar borç olmaktan çıkar.
        $silSql = "DELETE FROM taksit1 WHERE sozlesme_id = :id AND odendi_tutar = 0";
        $db->query($silSql, [':id' => $id]);

        // 2. Sözleşme Durumunu 'Pasif/İptal' Yap (durum = 0 veya -1 varsayıyoruz, genelde 0 pasiftir)
        // Ayrıca fesih nedenini bir 'aciklama' veya 'notlar' alanına ekleyebiliriz.
        // Veritabanı yapısını tam bilmediğimiz için standart 'durum' güncellemesi yapıyoruz.
        // Eğer 'aciklama' kolonu varsa orayı da güncelleyelim.

        $updateData = ['durum' => 0];

        // Tablo yapısını kontrol etme şansımız yoksa risk almayalım, sadece durum değiştirelim.
        // Ancak genelde bir açıklama istenir. Şimdilik 'iptal_nedeni' gibi bir kolon yoksa, bunu loglayabiliriz 
        // veya admin notu varsa oraya ekleyebiliriz. Biz sadece durumu güncelleyelim.

        $db->update('sozlesme1', $updateData, 'sozlesme_id', $id);

        $db->commit();
        jsonResponse(true, 'Sözleşme feshedildi. Kalan borçlar silindi, ödenmiş tutarlar korundu.');

    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(false, 'Hata: ' . $e->getMessage());
    }
}
