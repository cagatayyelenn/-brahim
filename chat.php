<?php
// db_audit.php — Şema Denetimi (SÖZLEŞME + TAKSİT + ÖDEME + KASA için gerekenler)
// Kullanım: dosyayı köke koy, tarayıcıdan çalıştır. (config & Ydil'i kendi yollarına göre ayarla)

require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

// ------ Yardımcılar ------
function hasTable($pdo, $table) {
    $q = $pdo->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t LIMIT 1");
    $q->execute([':t'=>$table]);
    return (bool)$q->fetchColumn();
}
function getColumns($pdo, $table) {
    $q = $pdo->prepare("
        SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t
    ");
    $q->execute([':t'=>$table]);
    $cols = [];
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $cols[$r['COLUMN_NAME']] = $r;
    }
    return $cols;
}
function ok($b){ return $b ? '✅' : '❌'; }
function pre($s){ echo '<pre style="white-space:pre-wrap;background:#111;color:#0f0;padding:12px;border-radius:8px;">'.htmlspecialchars($s).'</pre>'; }

// ------ Gerekli Şema (şimdiki adım: sözleşme + taksit + ödeme + kasa) ------
$need = [

    // Öğrenci (senin verdiğin tabloya göre)
    'ogrenci' => [
        'ogrenci_id','ogrenci_numara','ogrenci_tc','ogrenci_adi','ogrenci_soyadi',
        'ogrenci_tel','ogrenci_mail','ogrenci_cinsiyet','ogrenci_dogumtar',
        'donem_id','sinif_id','grup_id','alan_id','il_id','ilce_id',
        'ogrenci_adres','per_id','sube_id'
    ],

    // Sözleşme (senin verdiğin yapıya göre)
    'sozlesme' => [
        'id','ogrenci_id','kurs_adi','birim_id','miktar','birim_fiyat',
        'toplam_tutar','pesinat_tutari','kalan_tutar','taksit_sayisi','satis_tarihi'
    ],

    // Taksitler
    'taksitler' => [
        'id','ogrenci_id','satis_id','taksit_tutari','taksit_tarihi',
        'odeme_tur_id','odendi','odeme_tarihi'
    ],

    // Ödemeler (senin verdiğin tam sütun listesi)
    'odemeler' => [
        'id','ogrenci_id','satis_id','taksit_id','tutar','odeme_tur_id',
        'kasa_id','odeme_tarihi','aciklama','per_id','created_at'
    ],

    // Kasa hareketleri (senin verdiğin + konuştuğumuz iki alan)
    'kasa_gir_cik' => [
        'id','sube_id','personel_id','islem_tipi','tur_id','tutar','tarih','aciklama',
        // konuştuklarımız:
        'baglanti_id','odeme_tur_id'
    ],

    // Şube / Personel
    'sube' => ['sube_id','sube_adi','sube_durum'],
    'personel' => ['personel_id','ad','sube_id','yetki'],

    // Ödeme türleri (sende tablo adı “odeme_turleri” ise bu)
    // yoksa “var/yok” raporlasın diye buraya ekliyorum
    'odeme_turleri' => ['id','ad'],

    // İdari yardımcı tablolar (öğrenci listelerinde kullanıyoruz)
    'alan'  => ['alan_id','alan_adi'],
    'sinif' => ['sinif_id','sinif_adi'],
    'grup'  => ['grup_id','grup_adi'],
    'donem' => ['donem_id','donem_adi'],
    'il'    => ['il_id','il_adi'],
    'ilce'  => ['ilce_id','ilce_adi','il_id'],
];

// ------ Denetim ------
$pdo = $db->conn;
$report = [];
$ddl = [];

foreach ($need as $table => $columns) {
    $exists = hasTable($pdo, $table);
    $report[$table] = ['exists'=>$exists, 'missing'=>[]];

    if (!$exists) {
        $report[$table]['missing'] = $columns;

        // Öneri DDL (basitleştirilmiş; tipler projene göre düzenlenmeli)
        $ddl[$table] = "-- CREATE önerisi (tipleri/proxy FK'leri projene göre düzenle)\n".
            "CREATE TABLE `$table` (\n".
            "  -- TODO: sütunları uygun tiplerle tanımla\n".
            "  -- Örn:\n".
            ($table==='sozlesme' ?
                "  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n".
                "  `ogrenci_id` INT UNSIGNED NOT NULL,\n".
                "  `kurs_adi` VARCHAR(255) NOT NULL,\n".
                "  `birim_id` INT UNSIGNED DEFAULT NULL,\n".
                "  `miktar` INT DEFAULT 0,\n".
                "  `birim_fiyat` DECIMAL(12,2) DEFAULT 0,\n".
                "  `toplam_tutar` DECIMAL(12,2) NOT NULL,\n".
                "  `pesinat_tutari` DECIMAL(12,2) DEFAULT 0,\n".
                "  `kalan_tutar` DECIMAL(12,2) DEFAULT 0,\n".
                "  `taksit_sayisi` INT DEFAULT 0,\n".
                "  `satis_tarihi` DATE NOT NULL\n"
                : "  -- $table sütunları\n").
            ");\n";
        continue;
    }

    // Tablo varsa sütunlara bak
    $cols = getColumns($pdo, $table);
    foreach ($columns as $c) {
        if (!isset($cols[$c])) {
            $report[$table]['missing'][] = $c;
        }
    }

    // Eksik sütun varsa ALTER önerisi
    if (!empty($report[$table]['missing'])) {
        $lines = [];
        foreach ($report[$table]['missing'] as $c) {
            // Tip önerilerini kaba veriyorum; projene göre değiştir
            $type = "VARCHAR(255) NULL";
            if (preg_match('/_id$/', $c)) $type = "INT UNSIGNED NULL";
            if (in_array($c, ['id']))    $type = "INT UNSIGNED NOT NULL AUTO_INCREMENT";
            if (in_array($c, ['tutar','toplam_tutar','pesinat_tutari','kalan_tutar','birim_fiyat','taksit_tutari'])) $type = "DECIMAL(12,2) DEFAULT 0";
            if (preg_match('/tarih$/', $c) || in_array($c,['odeme_tarihi','satis_tarihi','taksit_tarihi','created_at'])) $type = "DATE NULL";
            if (in_array($c, ['odendi'])) $type = "TINYINT(1) DEFAULT 0";
            if ($c==='islem_tipi') $type = "ENUM('GIRIS','CIKIS') NOT NULL DEFAULT 'GIRIS'";
            if ($c==='aciklama')   $type = "TEXT NULL";
            if ($c==='ad')         $type = "VARCHAR(150) NOT NULL";
            if ($c==='ogrenci_numara') $type="VARCHAR(50) NULL";

            $lines[] = "ADD COLUMN `$c` $type";
        }
        $ddl[$table] =
            "ALTER TABLE `$table`\n  ".implode(",\n  ", $lines).";\n";
    }
}

// Basit çıktı
echo '<h2>Şema Denetimi</h2>';
echo '<table border="1" cellpadding="6" cellspacing="0">';
echo '<tr><th>Tablo</th><th>Durum</th><th>Eksik Sütunlar</th></tr>';
foreach ($report as $table=>$r) {
    echo '<tr>';
    echo '<td>'.htmlspecialchars($table).'</td>';
    echo '<td>'.ok($r['exists'] && empty($r['missing'])).'</td>';
    echo '<td>';
    if (!$r['exists']) {
        echo 'Tablo yok';
    } elseif (!empty($r['missing'])) {
        echo implode(', ', array_map('htmlspecialchars', $r['missing']));
    } else {
        echo '-';
    }
    echo '</td>';
    echo '</tr>';
}
echo '</table>';

echo '<h3>Önerilen DDL (varsa)</h3>';
if (empty($ddl)) {
    echo '<p>Eksik yok gibi görünüyor. ✅</p>';
} else {
    foreach ($ddl as $table=>$sql) {
        echo '<h4>'.htmlspecialchars($table).'</h4>';
        pre($sql);
    }
}