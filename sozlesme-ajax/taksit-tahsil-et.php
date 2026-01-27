<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../dosyalar/config.php';
require_once __DIR__ . '/../dosyalar/Ydil.php';
require_once __DIR__ . '/../dosyalar/oturum.php';

$db = new Ydil();
$pdo = $db->conn; // transaction için

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Geçersiz istek']);
    exit;
}

try {

    /* ====== 0. TEMEL VERİLER ====== */
    // Varsayılan tarih: şimdi
    $post_tarih = trim($_POST['odeme_tarihi'] ?? '');
    if ($post_tarih) {
        // d.m.Y -> Y-m-d H:i:s
        $dt = DateTime::createFromFormat('d.m.Y', $post_tarih);
        if ($dt) {
            $simdikitarih = $dt->format('Y-m-d') . ' ' . date('H:i:s');
        } else {
            // format hatası varsa 
            $simdikitarih = date('Y-m-d H:i:s');
        }
    } else {
        $simdikitarih = date('Y-m-d H:i:s');
    }

    $personel_id = (int) ($_SESSION['personel_id'] ?? 0) ?: null;

    $taksit_id = (int) ($_POST['taksit_id'] ?? 0);
    $sozlesme_id = (int) ($_POST['sozlesme_id'] ?? 0);
    $ogrenci_id = (int) ($_POST['ogrenci_id'] ?? 0); // kasa_hareketleri1 için lazım
    $yontem_id = (int) ($_POST['yontem_id'] ?? 0);
    $kasa_id = (int) ($_POST['kasa_id'] ?? 0);
    $taksit_tutar_raw = (float) ($_POST['taksit_tutar_raw'] ?? 0);
    $farkli_tutar_check = (int) ($_POST['farkli_tutar'] ?? 0); // 0: sadece bu taksit, 1: dağıtarak
    $odenecek_tutar = (float) ($_POST['odenecek_tutar'] ?? 0);
    $aciklama = trim($_POST['aciklama'] ?? 'Taksit tahsilatı');

    /* ====== 1. ZORUNLU ALAN KONTROLÜ ====== */
    if (!$taksit_id || !$sozlesme_id || !$yontem_id || !$kasa_id) {
        echo json_encode(['ok' => false, 'msg' => 'Eksik/hatalı veri (taksit/sözleşme/yöntem/kasa).']);
        exit;
    }
    if (empty($personel_id)) {
        echo json_encode(['ok' => false, 'msg' => 'Oturum personeli bulunamadı.']);
        exit;
    }
    if ($odenecek_tutar <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'Ödenecek tutar 0 olamaz.']);
        exit;
    }

    /* ====== 2. SEÇİLEN TAKSİDİ ÇEK ====== */
    $taksitRow = $db->gets("
        SELECT taksit_id, sozlesme_id, sira_no, vade_tarihi, tutar, odendi_tutar, durum
        FROM taksit1
        WHERE taksit_id = :id
        LIMIT 1
    ", [':id' => $taksit_id]);

    if (!$taksitRow) {
        echo json_encode(['ok' => false, 'msg' => 'Taksit kaydı bulunamadı.']);
        exit;
    }

    if ((int) $taksitRow['sozlesme_id'] !== $sozlesme_id) {
        echo json_encode(['ok' => false, 'msg' => 'Taksit ile sözleşme uyuşmuyor.']);
        exit;
    }

    $taksit_tutar_db = (float) $taksitRow['tutar'];
    $odenen_tutar_db = (float) ($taksitRow['odendi_tutar'] ?? 0);
    $kalan_db = $taksit_tutar_db - $odenen_tutar_db;

    if ($kalan_db <= 0.01) {
        echo json_encode(['ok' => false, 'msg' => 'Bu taksit zaten tamamen ödenmiş görünüyor.']);
        exit;
    }

    /* ====== 3. FARKLI-TUTAR SENARYOSUNA GÖRE ÖN KONTROL ====== */

    if ($farkli_tutar_check === 0) {
        // SADECE BU TAKSİT ÖDENECEK
        // fronttan gelen taksit tutarı ile db tutarı aynı mı
        if (abs($taksit_tutar_raw - $taksit_tutar_db) > 0.01) {
            echo json_encode(['ok' => false, 'msg' => 'Gönderilen taksit tutarı ile kayıtlı tutar uyuşmuyor.']);
            exit;
        }
        // bu taksidin kalanından fazla ödeme olamaz
        if ($odenecek_tutar > $kalan_db + 0.01) {
            echo json_encode(['ok' => false, 'msg' => 'Ödenecek tutar, taksitin kalan tutarından fazla olamaz.']);
            exit;
        }
        // bu senaryoda sorun yok → devam edeceğiz
        $dagitim = [
            [
                'taksit_id' => $taksit_id,
                'tutar' => $odenecek_tutar
            ]
        ];

    } else {
        // DAĞITARAK ÖDEME (gelen para bu taksidi aşabilir)

        // önce bu sözleşmeye ait ödenmemiş tüm taksitleri çek
        $acikTaksitler = $db->get("
            SELECT taksit_id, tutar, odendi_tutar, sira_no, vade_tarihi
            FROM taksit1
            WHERE sozlesme_id = :sid
              AND (tutar - odendi_tutar) > 0
            ORDER BY sira_no ASC, vade_tarihi ASC, taksit_id ASC
        ", [':sid' => $sozlesme_id]);

        if (!$acikTaksitler) {
            echo json_encode(['ok' => false, 'msg' => 'Bu sözleşmeye ait ödenebilir taksit yok.']);
            exit;
        }

        // toplam ödenebilir borcu bul
        $toplamKalan = 0;
        foreach ($acikTaksitler as $tk) {
            $toplamKalan += ((float) $tk['tutar'] - (float) ($tk['odendi_tutar'] ?? 0));
        }

        // gelen para toplam kalan borçtan fazla olamaz
        if ($odenecek_tutar > $toplamKalan + 0.01) {
            echo json_encode(['ok' => false, 'msg' => 'Ödeme, sözleşmenin kalan toplam borcundan fazla olamaz.']);
            exit;
        }

        // şimdi dağıtım
        $kalan_odenecek = $odenecek_tutar;
        $dagitim = [];

        foreach ($acikTaksitler as $tk) {
            if ($kalan_odenecek <= 0.01)
                break;

            $tk_id = (int) $tk['taksit_id'];
            $tk_tutar = (float) $tk['tutar'];
            $tk_odenen = (float) ($tk['odendi_tutar'] ?? 0);
            $tk_kalan = $tk_tutar - $tk_odenen;

            if ($tk_kalan <= 0)
                continue;

            if ($kalan_odenecek >= $tk_kalan - 0.01) {
                // bu taksidi tamamen kapat
                $dagitim[] = [
                    'taksit_id' => $tk_id,
                    'tutar' => $tk_kalan
                ];
                $kalan_odenecek -= $tk_kalan;
            } else {
                // bu takside kısmi ödeme
                $dagitim[] = [
                    'taksit_id' => $tk_id,
                    'tutar' => $kalan_odenecek
                ];
                $kalan_odenecek = 0;
                break;
            }
        }
    }

    /* ====== 4. ÖDEME NUMARASI HAZIRLA ====== */
    $generateOdemeNo = function (PDO $pdo) {
        $y = date('Ymd');
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM odeme1 WHERE DATE(created_at)=CURDATE()");
        $c = (int) $stmt->fetchColumn();
        return 'OD-' . $y . '-' . str_pad((string) ($c + 1), 4, '0', STR_PAD_LEFT);
    };
    $odeme_no = $generateOdemeNo($pdo);

    /* ====== 5. TRANSACTION BAŞLA ====== */
    $pdo->beginTransaction();

    /* 5.1 odeme1 INSERT */
    $insOdeme = $db->insert(
        'odeme1',
        ['odeme_no', 'sozlesme_id', 'kasa_id', 'yontem_id', 'tutar', 'odeme_tarihi', 'aciklama', 'personel_id', 'created_at'],
        [$odeme_no, $sozlesme_id, $kasa_id, $yontem_id, $odenecek_tutar, $simdikitarih, $aciklama, $personel_id, $simdikitarih]
    );
    $odeme_id = (is_array($insOdeme) && isset($insOdeme['id'])) ? (int) $insOdeme['id'] : 0;
    if (!$odeme_id) {
        throw new Exception('Ödeme kaydı oluşturulamadı.');
    }

    /* 5.2 dagitim’daki HER taksit için: odeme1_taksit INSERT + taksit1 UPDATE */
    foreach ($dagitim as $d) {
        $this_taksit_id = (int) $d['taksit_id'];
        $this_tutar = (float) $d['tutar'];

        // odeme1_taksit
        $db->insert(
            'odeme1_taksit',
            ['odeme_id', 'taksit_id', 'sozlesme_id', 'tutar'],
            [$odeme_id, $this_taksit_id, $sozlesme_id, $this_tutar]
        );

        // ilgili taksidin mevcut odendi_tutar'ını çek (transaction içindeyiz, tekrar çekmek güvenli)
        $curr = $db->gets("
            SELECT odendi_tutar, tutar
            FROM taksit1
            WHERE taksit_id = :id
            LIMIT 1
        ", [':id' => $this_taksit_id]);

        $curr_odenen = (float) ($curr['odendi_tutar'] ?? 0);
        $curr_tutar = (float) $curr['tutar'];
        $new_odenen = $curr_odenen + $this_tutar;
        $new_durum = ($new_odenen + 0.01 >= $curr_tutar) ? 1 : 0;

        // taksit1 UPDATE → senin update fonksiyonuna göre
        $db->update(
            'taksit1',
            ['odendi_tutar', 'durum'],
            [$new_odenen, $new_durum],
            'taksit_id',
            $this_taksit_id
        );
    }

    /* 5.3 kasa_hareketleri1 INSERT → tek satır, toplam tutar */
    $db->insert(
        'kasa_hareketleri1',
        ['kasa_id', 'odeme_id', 'sozlesme_id', 'ogrenci_id', 'yon', 'hareket_tipi', 'hareket_tur_id', 'tutar', 'aciklama', 'hareket_tarihi', 'created_at', 'created_by'],
        [$kasa_id, $odeme_id, $sozlesme_id, $ogrenci_id, 'GIRIS', 'TAHSILAT', $yontem_id, $odenecek_tutar, $aciklama, $simdikitarih, $simdikitarih, $personel_id]
    );

    /* 5.4 Loglama */
    $db->log('odeme1', $odeme_id, 'EKLEME', 'Taksit tahsilatı yapıldı: ' . number_format($odenecek_tutar, 2, ',', '.') . ' ₺');

    /* 5.5 commit */
    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'msg' => 'Ödeme alındı.',
        'odeme_id' => $odeme_id,
        'odeme_no' => $odeme_no,
        'tutar' => $odenecek_tutar,
        'dagilim' => $dagitim
    ]);
    exit;

} catch (Throwable $e) {
    if (!empty($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('taksit-tahsil-et ERROR: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'msg' => 'Hata: ' . $e->getMessage()]);
    exit;
}