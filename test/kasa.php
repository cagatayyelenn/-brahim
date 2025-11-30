<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

// gelir ekeleme modal için
$hareketTurleri = $db->get("SELECT tur_id, tur_adi FROM kasa_hareket_turleri ORDER BY tur_adi");

$odemeYontemleri = $db->finds('odeme_yontem1', 'durum', 1, ['yontem_id','yontem_adi','sira']);
usort($odemeYontemleri, fn($a,$b)=>($a['sira']<=>$b['sira']) ?: strcmp($a['yontem_adi'],$b['yontem_adi']));

$aktifSubeId = (int)($_SESSION['sube_id'] ?? 0);
if ($aktifSubeId) {
    $kasalar = $db->get("SELECT kasa_id,kasa_adi FROM kasa1 WHERE durum=1 AND (sube_id=:sid OR sube_id IS NULL) ORDER BY sira,kasa_adi", [':sid'=>$aktifSubeId]);
} else {
    $kasalar = $db->finds('kasa1','durum',1,['kasa_id','kasa_adi','sira']);
    usort($kasalar, fn($a,$b)=>($a['sira']<=>$b['sira']) ?: strcmp($a['kasa_adi'],$b['kasa_adi']));
}
$turler = $db->get("SELECT tur_id, tur_adi, islem_tipi FROM kasa_hareket_turleri WHERE aktif=1 ORDER BY islem_tipi DESC, tur_adi ASC");
// gelir ekeleme modal için

$sube_id = (int)($_SESSION['sube_id'] ?? 0);

// Temel SQL (şube filtresi zorunlu)
$sql = "
SELECT
    kh.hareket_id,
    kh.kasa_id,
    k.kasa_adi,
    k.kasa_tipi,

    kh.yon,                         -- GIRIS / CIKIS
    kh.hareket_tipi,                -- TAHSILAT / TRANSFER_GELIR / TRANSFER_GIDER ...
    kh.hareket_tur_id,
    tur.tur_adi,                    -- kategori adı (varsa)

    kh.tutar,
    kh.aciklama,
    kh.hareket_tarihi,

    -- Bağlantı (öğrenci)
    kh.ogrenci_id,
    o.ogrenci_numara,
    o.ogrenci_adi,
    o.ogrenci_soyadi,

    -- Ödeme bilgisi
    kh.odeme_id,
    odm.odeme_no,
    y.yontem_adi AS odeme_yontemi,

    -- İşlemi yapan
    kh.created_by,
    p.personel_adi,
    p.personel_soyadi

FROM kasa_hareketleri1 kh
JOIN kasa1 k                 ON k.kasa_id = kh.kasa_id
LEFT JOIN kasa_hareket_turleri tur ON tur.tur_id = kh.hareket_tur_id
LEFT JOIN ogrenci1 o         ON o.ogrenci_id = kh.ogrenci_id
LEFT JOIN odeme1 odm         ON odm.odeme_id = kh.odeme_id
LEFT JOIN odeme_yontem1 y    ON y.yontem_id = odm.yontem_id
LEFT JOIN personel1 p         ON p.personel_id = kh.created_by

WHERE k.sube_id = :sube_id   -- ŞUBE KISITI (sadece Maltepe gibi)
ORDER BY kh.hareket_tarihi DESC, kh.hareket_id DESC
";

$rows = $db->get($sql, [':sube_id' => $sube_id]);

// Görsel format yardımcıları
function money_tr($v){
    return number_format((float)$v, 2, ',', '.') . ' ₺'; // 5.000,00 ₺
}
function tarih_tr($dt){ // 'Y-m-d H:i:s' -> 'd.m.Y H:i'
    if (!$dt) return '';
    $ts = strtotime($dt);
    return date('d.m.Y H:i', $ts);
}
// Kasa özet kartlar için yardımcı


// Kasa tipi bazında (Nakit / POS / Banka / Diğer) GİRİŞ-ÇIKIŞ toplamları
$sqlTip = "
SELECT 
  k.kasa_tipi,
  SUM(CASE WHEN kh.yon='GIRIS' THEN kh.tutar ELSE 0 END) AS giris,
  SUM(CASE WHEN kh.yon='CIKIS' THEN kh.tutar ELSE 0 END) AS cikis
FROM kasa1 k
LEFT JOIN kasa_hareketleri1 kh ON kh.kasa_id = k.kasa_id
WHERE k.sube_id = :sube
GROUP BY k.kasa_tipi
";
$tipRows = $db->get($sqlTip, [':sube'=>$sube_id]);


$ozet = [
    'Nakit' => ['giris'=>0,'cikis'=>0],
    'POS'   => ['giris'=>0,'cikis'=>0],
    'Banka' => ['giris'=>0,'cikis'=>0],
    'Diğer' => ['giris'=>0,'cikis'=>0],
];
foreach ($tipRows as $r){
    $t = $r['kasa_tipi'] ?? 'Diğer';
    if (!isset($ozet[$t])) $ozet[$t] = ['giris'=>0,'cikis'=>0];
    $ozet[$t]['giris'] += (float)$r['giris'];
    $ozet[$t]['cikis'] += (float)$r['cikis'];
}

function kart_sayilar($arr){
    $giris = (float)($arr['giris'] ?? 0);
    $cikis = (float)($arr['cikis'] ?? 0);
    $net   = $giris - $cikis; // = Gelir - Gider
    return [$giris, $cikis, $net];
}


// Kasa özet kartlar için yardımcı
$pageTitle = "Kasa";
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
$page_styles[] = ['href' => 'assets/plugins/icons/themify/themify.css'];
$page_styles[] = ['href' => 'assets/plugins/daterangepicker/daterangepicker.css'];

require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto  ">
                <h3 class="page-title mb-1">Kasa</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kasa İşlemleri</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="col-xl-12 d-flex">
                    <div class="row flex-fill">
                        <div class="col-xl-6 col-md-12 d-flex">
                            <a href="javascript:void(0);" class="card bg-success-transparent border border-5 border-white animate-card flex-fill " data-bs-toggle="modal"
                               data-bs-target="#modalGelirEkle">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
										<span class="avatar avatar-lg bg-success rounded flex-shrink-0 me-2">
                                            <i class="ti-stats-up"></i>
                                        </span>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-semibold text-default">Gelir Ekle</h6>
                                        </div>
                                        </div>
                                        <span class="btn btn-white success-btn-hover avatar avatar-sm p-0 flex-shrink-0 rounded-circle">
                                            <i class="ti ti-chevron-right fs-14"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-6 col-md-12 d-flex">
                            <a href="javascript:void(0);" class="card bg-success-transparent border border-5 border-white animate-card flex-fill " data-bs-toggle="modal"
                               data-bs-target="#modalGiderEkle">
                            <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
										<span class="avatar avatar-lg bg-danger rounded flex-shrink-0 me-2">
                                            <i class="ti-stats-down"></i>
                                        </span>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-semibold text-default">Gider Ekle</h6>
                                        </div>
                                        </div>
                                        <span class="btn btn-white danger-btn-hover avatar avatar-sm p-0 flex-shrink-0 rounded-circle">
                                            <i class="ti ti-chevron-right fs-14"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body pb-1">
                <div class="row">
                    <!-- Nakit -->
                    <?php list($gN,$cN,$tN) = kart_sayilar($ozet['Nakit']); ?>
                    <div class="col-xl-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">Nakit</h4>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3" id="box_nakit">
                                    <div class="row">
                                        <div class="col text-center border-end">
                                            <h5>Giriş</h5>
                                            <p class="mb-1"><span id="nakit_giris"><?= money_tr($gN) ?></span></p>
                                        </div>
                                        <div class="col text-center border-end">
                                            <h5>Çıkış</h5>
                                            <p class="mb-1"><span id="nakit_cikis"><?= money_tr($cN) ?></span></p>
                                        </div>
                                        <div class="col text-center">
                                            <h5>Toplam</h5>
                                            <p class="mb-1"><span id="nakit_toplam"><?= money_tr($tN) ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- POS -->
                    <?php list($gP,$cP,$tP) = kart_sayilar($ozet['POS']); ?>
                    <div class="col-xl-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">POS</h4>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3" id="box_pos">
                                    <div class="row">
                                        <div class="col text-center border-end">
                                            <h5>Giriş</h5>
                                            <p class="mb-1"><span id="pos_giris"><?= money_tr($gP) ?></span></p>
                                        </div>
                                        <div class="col text-center border-end">
                                            <h5>Çıkış</h5>
                                            <p class="mb-1"><span id="pos_cikis"><?= money_tr($cP) ?></span></p>
                                        </div>
                                        <div class="col text-center">
                                            <h5>Toplam</h5>
                                            <p class="mb-1"><span id="pos_toplam"><?= money_tr($tP) ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Banka -->
                    <?php list($gB,$cB,$tB) = kart_sayilar($ozet['Banka']); ?>
                    <div class="col-xl-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">Banka</h4>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3" id="box_banka">
                                    <div class="row">
                                        <div class="col text-center border-end">
                                            <h5>Giriş</h5>
                                            <p class="mb-1"><span id="banka_giris"><?= money_tr($gB) ?></span></p>
                                        </div>
                                        <div class="col text-center border-end">
                                            <h5>Çıkış</h5>
                                            <p class="mb-1"><span id="banka_cikis"><?= money_tr($cB) ?></span></p>
                                        </div>
                                        <div class="col text-center">
                                            <h5>Toplam</h5>
                                            <p class="mb-1"><span id="banka_toplam"><?= money_tr($tB) ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php list($gB,$cB,$tB) = kart_sayilar($ozet['Banka']); ?>
                    <div class="col-xl-3 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">Toplam</h4>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3" id="box_toplam">
                                    <div class="row">
                                        <div class="col text-center border-end">
                                            <h5>Giriş</h5>
                                            <p class="mb-1"><span id="toplam_giris"><?= money_tr($gTN ?? 0) ?></span></p>
                                        </div>
                                        <div class="col text-center border-end">
                                            <h5>Çıkış</h5>
                                            <p class="mb-1"><span id="toplam_cikis"><?= money_tr($cTN ?? 0) ?></span></p>
                                        </div>
                                        <div class="col text-center">
                                            <h5>Toplam</h5>
                                            <p class="mb-1"><span id="toplam_toplam"><?= money_tr($tTN ?? 0) ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- .row -->
            </div>
        </div>


        <div class="card">
            <!-- Filtreleme alanı -->
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
                <h4 class="mb-3"> </h4>
                <div class="d-flex align-items-center flex-wrap">
                    <div class="input-icon-start mb-3 me-2 position-relative">
                        <span class="icon-addon">
                            <i class="ti ti-calendar"></i>
                        </span>
                        <input type="text" class="form-control date-range bookingrange" placeholder="Select" value="Academic Year : 2024 / 2025">
                    </div>
                    <div class="dropdown mb-3">
                        <a href="javascript:void(0);" class="btn btn-outline-light bg-white dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ti ti-sort-ascending-2 me-2"></i>
                            <span class="dropdown-label">Kasa Türü</span>
                        </a>

                        <ul class="dropdown-menu p-3">
                            <li>
                                <a href="javascript:void(0);" data-kasa-id="0" class="dropdown-item rounded-1 active kasa-select">Bütün Kasalar</a>
                            </li>
                            <?php foreach ($kasalar as $k): ?>
                                <li>
                                    <a href="javascript:void(0);" data-kasa-id="<?= (int)$k['kasa_id'] ?>" class="dropdown-item rounded-1 kasa-select">
                                        <?= htmlspecialchars($k['kasa_adi']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- #Filtreleme alanı -->
            <!-- Gelen veri tablo -->
            <div class="card-body p-0 py-3">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table table-bordered mb-0"  >
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tarih</th>
                            <th>Kasa</th>
                            <th>Bağlantı</th>
                            <th>Kategori</th>
                            <th>Ödeme Türü</th>
                            <th>Açıklama</th>
                            <th>İşlemi Yapan</th>
                            <th>Yön</th>
                            <th class="text-end">Tutar</th>
                            <th>İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rows)): ?>
                            <?php foreach ($rows as $r): ?>
                                <?php
                                // Bağlantı hücresi
                                $ogr_link_text = '';
                                $ogr_link_href = '#';
                                if (!empty($r['ogrenci_id'])) {
                                    $ogr_no   = $r['ogrenci_numara'] ?: $r['ogrenci_id'];
                                    $ogr_ad   = trim(($r['ogrenci_adi'] ?? '').' '.($r['ogrenci_soyadi'] ?? ''));
                                    $ogr_link_text = $ogr_no.' - '.$ogr_ad;
                                    // Not: örnekte link paramı numara idi; sende hangi paramı bekliyorsa onu kullan
                                    $ogr_link_href = 'ogrenci-detay.php?id='.urlencode($ogr_no);
                                }

                                // Ödeme türü (öncelik yontem_adi; yoksa kasa tipinden türetilebilir)
                                $odeme_turu = $r['odeme_yontemi'] ?? '';
                                if ($odeme_turu === '' && !empty($r['kasa_tipi'])) {
                                    // ör: Banka/POS/Nakit kasadan türetme fallback’i
                                    $odeme_turu = $r['kasa_tipi'];
                                }

                                // Yön rozet & tutar işareti
                                $is_giris   = ($r['yon'] === 'GIRIS');
                                $yon_badge  = $is_giris ? '<span class="badge bg-success">GİRİŞ</span>' : '<span class="badge bg-danger">ÇIKIŞ</span>';
                                $tutar_cls  = $is_giris ? 'text-success' : 'text-danger';
                                $tutar_pref = $is_giris ? '+' : '-';
                                ?>
                                <tr
                                        data-kasa-tipi="<?= htmlspecialchars($r['kasa_tipi'] ?? '') ?>"
                                        data-odeme-no="<?= htmlspecialchars($r['odeme_no'] ?? '') ?>"
                                        data-tarih="<?= htmlspecialchars(substr($r['hareket_tarihi'],0,10)) ?>"
                                >
                                    <td><a href="kasa-hareket-detay.php?id=<?= (int)$r['hareket_id'] ?>" class="link-primary"><?= (int)$r['hareket_id'] ?></a></td>
                                    <td><?= htmlspecialchars(tarih_tr($r['hareket_tarihi'])) ?></td>
                                    <td><?= htmlspecialchars($r['kasa_adi'] ?? '') ?></td>
                                    <td>
                                        <?php if ($ogr_link_text): ?>
                                            <a href="<?= htmlspecialchars($ogr_link_href) ?>" class="link-primary"><?= htmlspecialchars($ogr_link_text) ?></a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['hareket_tipi'] ?: ($r['tur_adi'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($odeme_turu) ?></td>
                                    <td class="desc"><?= htmlspecialchars($r['aciklama'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(trim(($r['personel_adi'] ?? '').' '.($r['personel_soyadi'] ?? ''))) ?></td>
                                    <td><?= $yon_badge ?></td>
                                    <td class="text-end <?= $tutar_cls ?>"><?= $tutar_pref . money_tr($r['tutar']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="dropdown">
                                                <a href="teachers.html#"
                                                   class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
                                                   data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ti ti-dots-vertical fs-14"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right p-3">
                                                    <li> <a class="dropdown-item rounded-1"  href="#" data-bs-toggle="modal" data-bs-target="#per_duz" data-id="33"> <i class="ti ti-edit-circle me-2"></i>Düzenle </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-1" href="#"
                                                           data-bs-toggle="modal"
                                                           data-bs-target="#login_detail"
                                                           data-id="33">
                                                            <i class="ti ti-lock me-2"></i>Şifre Sıfırla
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- #Gelen veri tablo -->
        </div>

    </div>
</div>


<div class="modal fade" id="modalGelirEkle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="ti ti-cash fs-22 me-2 text-success"></i>
                    <h4 class="modal-title">Kasa Gelir / Tahsilat</h4>
                </div>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <form id="formGelirEkle" action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-lg-4">
                            <label class="form-label">Hareket Türü</label>
                            <select class="form-select" name="hareket_tur_id" id="hareketTur" required>
                                <option value="">Seçiniz</option>
                                <optgroup label="Gelir">
                                    <?php foreach ($turler as $t): ?>
                                        <?php if ($t['islem_tipi'] === 'giris'): ?>
                                            <option value="<?= (int)$t['tur_id'] ?>" data-islem="giris">
                                                <?= htmlspecialchars($t['tur_adi']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <input type="hidden" name="yon" id="hidYon" value="GIRIS">

                        <div class="col-lg-4">
                            <label class="form-label">Ödeme Türü</label>
                            <select class="form-select" name="yontem_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($odemeYontemleri as $y): ?>
                                    <option value="<?= (int)$y['yontem_id'] ?>"><?= htmlspecialchars($y['yontem_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Kasa</label>
                            <select class="form-select" name="kasa_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($kasalar as $k): ?>
                                    <option value="<?= (int)$k['kasa_id'] ?>"><?= htmlspecialchars($k['kasa_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tarih</label>
                            <div class="position-relative">
                                <input type="text" class="form-control datetimepicker" name="hareket_tarihi"  required>
                                <span class="position-absolute end-0 top-0 h-100 d-flex align-items-center px-2 text-muted">
                                  <i class="ti ti-calendar"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tutar</label>
                            <input type="text" class="form-control text-end" name="tutar" placeholder="0,00" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <input type="text" class="form-control" name="aciklama" placeholder="Örn: Ek kayıt geliri / bağış">
                        </div>

                        <input type="hidden" name="yon" value="GIRIS">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-cash me-1"></i> Kaydet
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<div class="modal fade" id="modalGiderEkle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="ti ti-credit-card-off fs-22 me-2 text-danger"></i>
                    <h4 class="modal-title">Kasa Gider / Çıkış</h4>
                </div>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <form id="formGiderEkle" action="#" method="post" autocomplete="off">
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-lg-4">
                            <label class="form-label">Hareket Türü</label>
                            <select class="form-select" name="hareket_tur_id" required>
                                <option value="">Seçiniz</option>
                                <optgroup label="Gider">
                                    <?php foreach ($turler as $t): ?>
                                        <?php if ($t['islem_tipi'] === 'cikis'): ?>
                                            <option value="<?= (int)$t['tur_id'] ?>" data-islem="cikis">
                                                <?= htmlspecialchars($t['tur_adi']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <input type="hidden" name="yon" value="CIKIS">

                        <div class="col-lg-4">
                            <label class="form-label">Ödeme Türü</label>
                            <select class="form-select" name="yontem_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($odemeYontemleri as $y): ?>
                                    <option value="<?= (int)$y['yontem_id'] ?>"><?= htmlspecialchars($y['yontem_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Kasa</label>
                            <select class="form-select" name="kasa_id" required>
                                <option value="">Seçiniz</option>
                                <?php foreach ($kasalar as $k): ?>
                                    <option value="<?= (int)$k['kasa_id'] ?>"><?= htmlspecialchars($k['kasa_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tarih</label>
                            <div class="position-relative">
                                <input type="text" class="form-control datetimepicker" name="hareket_tarihi" required>
                                <span class="position-absolute end-0 top-0 h-100 d-flex align-items-center px-2 text-muted">
                                  <i class="ti ti-calendar"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Tutar</label>
                            <input type="text" class="form-control text-end" name="tutar" placeholder="0,00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <input type="text" class="form-control" name="aciklama" placeholder="Örn: Elektrik faturası">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-credit-card-off me-1"></i> Kaydet (Gider)
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<script data-cfasync="false" src="assets/js/jquery-3.7.1.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap.bundle.min.js"></script>
<script data-cfasync="false" src="assets/js/form-validation.js" type="text/javascript"></script>
<script data-cfasync="false" src="assets/js/moment.js"></script>
<script data-cfasync="false" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/tr.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap-datetimepicker.min.js"></script>
<script data-cfasync="false" src="assets/plugins/select2/js/select2.min.js"></script>
<script data-cfasync="false" src="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
<script data-cfasync="false" src="assets/js/feather.min.js"></script>
<script data-cfasync="false" src="assets/js/jquery.slimscroll.min.js"></script>
<script data-cfasync="false" src="assets/js/checsum.js" type="text/javascript"></script>
<script data-cfasync="false" src="assets/js/script.js"></script>

<script src="assets/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>

<script>
    function refreshList(){
        setTimeout(function(){
            if (typeof window.fetchRows === 'function') {
                fetchRows(); // AJAX ile listeyi yeniler
            } else {
                location.reload(); // fallback - sayfa yenile
            }
        }, 3000); // 3 saniye bekle
    }
</script>
<script>
    $(function () {
        $('#formGelirEkle').on('submit', function (e) {
            e.preventDefault();

            const $form   = $(this);
            const postUrl = 'sozlesme-ajax/kasa-gelir-kaydet.php';

            Swal.fire({title:'İşleniyor', didOpen:()=>Swal.showLoading(), allowOutsideClick:false});

            $.ajax({
                url: postUrl,
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json'
            }).done(function (data) {
                if (data && data.ok) {
                    const modalEl = document.getElementById('modalGelirEkle');
                    const modal   = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.hide();
                    $form[0].reset();

                    Swal.fire({icon:'success', title:'Kayıt eklendi', text: data.msg || 'Gelir eklendi', timer:1000, showConfirmButton:false});
                    refreshList();  // <-- BURASI
                } else {
                    Swal.fire({icon:'error', title:'Hata', text: (data && data.msg) ? data.msg : 'Kayıt eklenemedi.'});
                }
            }).fail(function (xhr) {
                Swal.fire({icon:'error', title:'Sunucu Hatası', text: xhr.responseText || 'İstek başarısız.'});
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        const $ = window.jQuery;

        $('#formGiderEkle').on('submit', function(e){
            e.preventDefault();
            const fd = new FormData(this);

            Swal.fire({title:'İşleniyor', didOpen:()=>Swal.showLoading(), allowOutsideClick:false});

            $.ajax({
                url: 'sozlesme-ajax/kasa-gider-kaydet.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function(res){
                if (res && res.ok && Number(res.id) > 0) {
                    // modal varsa kapat
                    const modalEl = document.getElementById('modalGiderEkle');
                    if (modalEl){
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                    }

                    Swal.fire({icon:'success', title:'Başarılı', text:res.msg || 'Kayıt eklendi', timer:1000, showConfirmButton:false});
                    refreshList();  // <-- BURASI
                } else {
                    Swal.fire({icon:'error', title:'Hata', text:(res && res.msg) ? res.msg : 'Kayıt eklenemedi'});
                }
            }).fail(function(xhr){
                Swal.fire({icon:'error', title:'Hata', text:'Sunucuya ulaşılamadı'});
                console.log('AJAX FAIL:', xhr.responseText);
            });
        });
    });
</script>

<script>
    (function(){
        // Seçimler
        let currentKasaId = 0;
        let currentStart  = ''; // YYYY-MM-DD
        let currentEnd    = ''; // YYYY-MM-DD

        // Tarih aralığı input’unu dinle (ör: "01.10.2025 - 20.10.2025" veya "2025-10-01 - 2025-10-20")
        function parseToYMD(s){
            s = (s||'').trim();
            // "DD.MM.YYYY" -> "YYYY-MM-DD"
            const dmy = s.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
            if (dmy) return dmy[3] + '-' + dmy[2] + '-' + dmy[1];
            // "YYYY-MM-DD" -> olduğu gibi
            const ymd = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (ymd) return s;
            return '';
        }

        function extractRange(val){
            // beklenen: "DD.MM.YYYY - DD.MM.YYYY" ya da "YYYY-MM-DD - YYYY-MM-DD"
            const parts = (val||'').split('-');
            if (parts.length >= 2) {
                const left  = parseToYMD(parts[0].trim());
                const right = parseToYMD(parts.slice(1).join('-').trim());
                return {start:left, end:right};
            }
            return {start:'', end:''};
        }

        function fetchRows(){
            const fd = new FormData();
            if (currentStart) fd.append('start', currentStart);
            if (currentEnd)   fd.append('end', currentEnd);
            if (currentKasaId>0) fd.append('kasa_id', currentKasaId);

            fetch('sozlesme-ajax/kasa-liste.php', { method:'POST', body: fd })
                .then(r=>r.json())
                .then(res=>{
                    if(!res.ok){ console.error(res.msg||'Hata'); return; }
                    const tbody = document.querySelector('table.table tbody');
                    if (tbody) tbody.innerHTML = res.html;
                })
                .catch(err=>console.error(err));
        }

        if($('.bookingrange').length > 0) {
            var start = moment().subtract(6, 'days');
            var end = moment();

            function booking_range(start, end) {
                $('.bookingrange span').html(start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'));
            }


            $('.bookingrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Bugün': [moment(), moment()],
                    'Dün': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Son 7 Gün': [moment().subtract(6, 'days'), moment()],
                    'Son 30 Gün': [moment().subtract(29, 'days'), moment()],
                    'Bu Yıl': [moment().startOf('year'), moment().endOf('year')],
                    'Gelecek Yıl': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                },
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: ' - ',
                    applyLabel: 'Uygula',
                    cancelLabel: 'İptal',
                    fromLabel: 'Başlangıç',
                    toLabel: 'Bitiş',
                    customRangeLabel: 'Özel',
                    weekLabel: 'Hf',
                    daysOfWeek: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
                    monthNames: [
                        'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
                        'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
                    ],
                    firstDay: 1
                }
            }, booking_range);
            $('.bookingrange').on('apply.daterangepicker', function(ev, picker){
                booking_range(picker.startDate, picker.endDate);
                // change olayı + seçilen tarihleri payload olarak geç
                currentStart=moment(picker.startDate.clone()).format('YYYY-MM-DD')
                currentEnd=moment(picker.endDate.clone()).format('YYYY-MM-DD')
                /*/$(this).trigger('change', [{
                    start: picker.startDate.clone(),
                    end:   picker.endDate.clone()
                }]);/*/
                fetchRows()

            });
        }



        $('.kasa-select').on('click',function (e) {
            const kasa_id = $(this).attr('data-kasa-id');
            currentKasaId = parseInt(kasa_id)
            document.querySelectorAll('.dropdown-item[data-kasa-id]').forEach(el => el.classList.remove('active'));

            const f=$(this)
            f[0].classList.add('active');
            //e.classList.add('active');
            fetchRows();
        })



        // Tarih input değişimi
        const rng = document.querySelector('.bookingrange');
        if (rng) {

            rng.addEventListener('change', function(){
                const val = this.value;
                const r = extractRange(val);
                currentStart = r.start;
                currentEnd   = r.end;
                fetchRows();
            });
        }

        // Kasa dropdown tıklaması
        /*/document.addEventListener('click', function(e){
            alert(currentStart)
            alert(currentEnd)
            const a = e.target.closest('a.dropdown-item[data-kasa-id]');
            if (!a) return;
            currentKasaId = parseInt(a.getAttribute('data-kasa-id')) || 0;
            fetchRows();
        });/*/

        // Sayfa yüklenince ilk çekim (istersen pas geç)
        // fetchRows();
    })();
</script>

<script>
    (function() {
        // Varsa mevcut değişkenleri bozma
        window.currentKasaId = window.currentKasaId || 0;

        function fetchRows() {
            if (typeof window.fetchRows === 'function') {
                window.fetchRows();
            }
        }

        /*/document.addEventListener('click', function(e){
            const a = e.target.closest('a.dropdown-item[data-kasa-id]');
            if (!a) return;

            // Seçili kasa id
            window.currentKasaId = parseInt(a.getAttribute('data-kasa-id')) || 0;

            // Active class yönetimi
            document.querySelectorAll('.dropdown-item[data-kasa-id]').forEach(el => el.classList.remove('active'));
            a.classList.add('active');

            // Buton etiketini güncelle
            const btnLabel = document.querySelector('.dropdown .dropdown-label');
            if (btnLabel) {
                if (window.currentKasaId === 0) {
                    btnLabel.textContent = 'Kasa Türü';
                } else {
                    btnLabel.textContent = a.textContent.trim();
                }
            }

            // Listeyi yenile
            fetchRows();
        });


    })();/*/


    })

</script>
</body>
</html>