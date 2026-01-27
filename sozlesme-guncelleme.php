<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();



$sozlesme_id = (int)($_GET['id'] ?? 0);
if ($sozlesme_id <= 0) {
    die("Hatalı sözleşme ID");
}

$sozlesme = $db->find('sozlesme1', 'sozlesme_id', $sozlesme_id);
if (!$sozlesme) {
    die("Sözleşme bulunamadı");
}

$odemeyontem = $db->find('odeme1', 'sozlesme_id', $sozlesme_id);


$kasaharaket = $db->find('kasa_hareketleri1', 'sozlesme_id', $sozlesme_id);


$taksitler = $db->get("
    SELECT *
    FROM taksit1
    WHERE sozlesme_id = :id
    ORDER BY sira_no ASC
", [':id' => $sozlesme_id]);

$odemeVar = false;
foreach ($taksitler as $t) {
    if ($t['odendi_tutar'] > 0) {
        $odemeVar = true;
        break;
    }
}

$donemler = $db->finds('donem', null, null, ['donem_id','donem_adi']);
$siniflar = $db->finds('sinif', null, null, ['sinif_id','sinif_adi']);
$gruplar  = $db->finds('grup',  null, null, ['grup_id','grup_adi']);
$alanlar  = $db->finds('alan',  null, null, ['alan_id','alan_adi']);
$birimler  = $db->finds('birim',  null, null, ['birim_id','birim_adi']);
$odemeYontemleri = $db->finds('odeme_yontem1', 'durum', 1, ['yontem_id','yontem_adi','sira']);
usort($odemeYontemleri, fn($a,$b)=>($a['sira']<=>$b['sira']));

// Kasaları Çek
$aktifSubeId = (int)($_SESSION['sube_id'] ?? 0);

if ($aktifSubeId) {
    $kasalar = $db->get(
        "SELECT kasa_id, kasa_adi, kasa_tipi FROM kasa1
       WHERE durum=1 AND (sube_id=:sid OR sube_id IS NULL)
       ORDER BY sira ASC, kasa_adi ASC",
        [':sid'=>$aktifSubeId]
    );
} else {
    $kasalar = $db->finds('kasa1', 'durum', 1, ['kasa_id','kasa_adi','kasa_tipi']);
    usort($kasalar, fn($a,$b)=>($a['sira']<=>$b['sira']) ?: strcmp($a['kasa_adi'],$b['kasa_adi']));
}

$pageTitle = 'Sözleşme Oluştur ';
$page_styles[] = ['href' => 'assets/plugins/summernote/summernote-lite.min.css'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';

?>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
                    <div class="my-auto mb-2">
                        <h3 class="page-title mb-1">Öğrenci Detay Bilgileri</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="index.php">Anasayfa</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="ogrenci-listesi.php">Öğrenci Listesi</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Sözleşme Oluştur</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <input type="hidden" id="sozlesmeId" value="<?=$sozlesme_id?>">

                        <div class="row">
                            <!-- DÖNEM -->
                            <div class="col-lg-3">
                                <label class="form-label">Dönem</label>
                                <select class="form-control" id="donem_id">
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($donemler as $d): ?>
                                        <option value="<?=$d['donem_id']?>"
                                            <?=$sozlesme['donem_id']==$d['donem_id']?'selected':''?>>
                                            <?=$d['donem_adi']?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- SINIF -->
                            <div class="col-lg-3">
                                <label class="form-label">Sınıf</label>
                                <select class="form-control" id="sinif_id">
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($siniflar as $s): ?>
                                        <option value="<?=$s['sinif_id']?>"
                                            <?=$sozlesme['sinif_id']==$s['sinif_id']?'selected':''?>>
                                            <?=$s['sinif_adi']?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- GRUP -->
                            <div class="col-lg-3">
                                <label class="form-label">Grup</label>
                                <select class="form-control" id="grup_id">
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($gruplar as $g): ?>
                                        <option value="<?=$g['grup_id']?>"
                                            <?=$sozlesme['grup_id']==$g['grup_id']?'selected':''?>>
                                            <?=$g['grup_adi']?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- ALAN -->
                            <div class="col-lg-3">
                                <label class="form-label">Alan</label>
                                <select class="form-control" id="alan_id">
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($alanlar as $a): ?>
                                        <option value="<?=$a['alan_id']?>"
                                            <?=$sozlesme['alan_id']==$a['alan_id']?'selected':''?>>
                                            <?=$a['alan_adi']?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <!-- Birim -->
                            <div class="col-lg-3">
                                <label class="form-label">Birim</label>
                                <select class="form-control" id="birimId" <?=$odemeVar?'disabled':''?>>
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($birimler as $b): ?>
                                        <option value="<?=$b['birim_id']?>"
                                            <?=$sozlesme['birim_id']==$b['birim_id']?'selected':''?>>
                                            <?=$b['birim_adi']?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Birim Fiyat -->
                            <div class="col-lg-3">
                                <label class="form-label">Birim Fiyat</label>
                                <input type="text"
                                       class="form-control text-end"
                                       id="birimFiyat"
                                       value="<?=number_format($sozlesme['net_ucret'],2,',','.')?>"
                                    <?=$odemeVar?'readonly':''?>>
                            </div>

                            <!-- Peşinat -->
                            <div class="col-lg-3">
                                <label class="form-label">Peşinat</label>
                                <input type="text"
                                       id="toplamTutar"
                                       class="form-control text-end"
                                       readonly
                                       value="<?=number_format($odemeyontem['tutar'],2,',','.')?>">
                            </div>

                            <!-- Toplam -->
                            <div class="col-lg-3">
                                <label class="form-label">Toplam</label>
                                <input type="text"
                                       id="toplamTutar"
                                       class="form-control text-end"
                                       readonly
                                       value="<?=number_format($sozlesme['toplam_ucret'],2,',','.')?>">
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Ödeme Seçeneği</label>
                                    <select class="form-control" id="odemeSecenegi" name="odemeSecenegi">
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($odemeYontemleri as $y){ ?>
                                            <option value="<?= (int)$y['yontem_id'] ?>"
                                                <?php echo $odemeyontem['yontem_id'] == $y['yontem_id'] ? 'selected':'' ?>>
                                                <?= htmlspecialchars($y['yontem_adi']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- SINIF -->
                            <div class="col-lg-4 col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Kasa</label>
                                    <select class="form-control" id="kasaId" name="kasaId">
                                        <option value="">Seçiniz</option>
                                        <?php foreach ($kasalar as $k): ?>
                                            <option value="<?= (int)$k['kasa_id'] ?>"
                                                <?=$kasaharaket['kasa_id']==$k['kasa_id']?'selected':''?>>
                                                <?= htmlspecialchars($k['kasa_adi']) ?><?php
                                                if (!empty($k['kasa_tipi'])) echo ' - '.htmlspecialchars($k['kasa_tipi']);
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h5 class="mt-3">Taksitler</h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Taksit Tutarı</th>
                                <th>Vade Tarihi</th>
                            </tr>
                            </thead>
                            <tbody id="taksitTbody">

                            <?php foreach ($taksitler as $t): ?>
                                <tr>
                                    <td>
                                        <input type="text"
                                               class="form-control text-end"
                                               value="<?=number_format($t['tutar'],2,',','.')?>"
                                            <?=$t['odendi_tutar']>0?'readonly':''?>>
                                    </td>

                                    <td>
                                        <input type="date"
                                               class="form-control"
                                               value="<?=$t['vade_tarihi']?>"
                                            <?=$t['odendi_tutar']>0?'readonly':''?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            </tbody>
                        </table>

                    </div>

                    <div class="card-footer text-end bg-light">
                        <button class="btn btn-success" id="btnSozlesmeGuncelle">
                            Sözleşmeyi Güncelle
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>



</div>
<!-- /Main Wrapper -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $("#btnSozlesmeGuncelle").on("click", function () {

        Swal.fire({
            icon: "success",
            title: "Başarılı",
            text: "Sözleşme güncelleme işlemi başarıyla tamamlandı.",
            confirmButtonText: "Tamam"
        }).then(() => {
            window.location.href = "ogrenci-listesi.php";
        });

    });
</script>

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
</body>
</html>




