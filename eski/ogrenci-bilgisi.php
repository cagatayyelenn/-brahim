<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";
$ogrenci_id = (int)($_GET['ogrenci_id'] ?? 0);
if ($ogrenci_id <= 0) {
    header('Location: ogrenci-listesi.php');
    exit;
}
$alanlar="SELECT * FROM `alan` ORDER BY `alan`.`alan_id` ASC";
$alans=$Ydil->get($alanlar);

$donemler="SELECT * FROM `donem` ORDER BY `donem`.`donem_id` DESC";
$donems=$Ydil->get($donemler);

$gruplar="SELECT * FROM `grup` ORDER BY `grup`.`grup_id` ASC";
$grups=$Ydil->get($gruplar);

$siniflar="SELECT * FROM `sinif` ORDER BY `sinif`.`sinif_id` ASC";
$sinifs=$Ydil->get($siniflar);

$subeler="SELECT * FROM `sube` ORDER BY `sube`.`sube_id` ASC";
$subes=$Ydil->get($subeler);

$iller="SELECT * FROM `il` ORDER BY `il`.`il_id` ASC";
$ils=$Ydil->get($iller);


$ogr  = $Ydil->getone("SELECT * FROM ogrenci WHERE ogrenci_id={$ogrenci_id} LIMIT 1");
$veli = $Ydil->getone("SELECT * FROM veli    WHERE ogrenci_id={$ogrenci_id} LIMIT 1");
$ogradi = $ogr['ogrenci_adi'];
$ogrtel = $ogr['ogrenci_tel'];
$dogumTarihi = $ogr['ogrenci_dogumtar']; // "2008-03-07"
$bugun = new DateTime();
$dogum = new DateTime($dogumTarihi);
$yas = $bugun->diff($dogum)->y;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Öğrenci Detay</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
</head>
<body class="nav-fixed">
<?php include 'ekler/sidebar.php'; ?>
<div id="layoutSidenav">
    <?php include 'ekler/menu.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
                <div class="container-xl px-4">
                    <div class="page-header-content">
                        <div class="row align-items-center justify-content-between pt-3">
                            <div class="col-auto mb-3">
                                <h1 class="page-header-title">
                                    <div class="page-header-icon"><i data-feather="user"></i></div>
                                    <?= htmlspecialchars($ogradi) ?>
                                </h1>

                                <div class="text-muted small">
                                    Yaş:  <?= $yas; ?>  | Tel:  <?= $ogrtel; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="container-xl px-4 mt-4">
                <!-- Account page navigation-->
                <nav class="nav nav-borders">
                    <a class="nav-link active ms-0" >Profil</a>
                    <a class="nav-link " href="ogrenci-detay.php?ogrenci_id=<?= $ogrenci_id; ?>">Ödemeler</a>
                    <a class="nav-link" href="ogrenci-sozlesme.php?ogrenci_id=<?= $ogrenci_id; ?>">Sözleşmeler</a>
                </nav>
                <hr class="mt-0 mb-4" />
                <div class="row">

                    <div class="container-xl px-4 mt-4">
                        <form method="post" action="" id="saveStudentForm">
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card mb-4">
                                        <div class="card-header">Öğrenci Bilgileri</div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputogrencinumara">Öğrenci Numarası</label>
                                                <input class="form-control" id="inputogrencinumara" name="inputogrencinumara" type="text"
                                                       placeholder="<?= htmlspecialchars($sayi ?? '') ?>"
                                                       value="<?= htmlspecialchars(($ogr['ogrenci_numara'] ?? '') !== '' ? $ogr['ogrenci_numara'] : ($sayi ?? '')) ?>" />
                                            </div>

                                            <div class="row gx-3 mb-3">

                                                    <label class="small mb-1" for="inputtc">Öğrenci Tc Numarası</label>
                                                    <input class="form-control" id="inputtc" name="inputtc" type="text"
                                                           placeholder="Lütfen Tc Kimlik Numarası Giriniz"
                                                           maxlength="11" pattern="[0-9]{11}"
                                                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                           value="<?= htmlspecialchars($ogr['ogrenci_tc'] ?? '') ?>" required />


                                            </div>

                                            <div class="row gx-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="small mb-1" for="inputogrenciadi">Öğrenci Adı</label>
                                                    <input class="form-control" id="inputogrenciadi" name="inputogrenciadi" type="text"
                                                           placeholder="Lütfen Adınızı Giriniz"
                                                           value="<?= htmlspecialchars($ogr['ogrenci_adi'] ?? '') ?>" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small mb-1" for="inputogrencisoyadi">Öğrenci Soyadı</label>
                                                    <input class="form-control" id="inputogrencisoyadi" name="inputogrencisoyadi" type="text"
                                                           placeholder="Lütfen Soyadınızı Giriniz"
                                                           value="<?= htmlspecialchars($ogr['ogrenci_soyadi'] ?? '') ?>" />
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputogrencitel">Öğrenci Telefonu</label>
                                                <input class="form-control" id="inputogrencitel" name="inputogrencitel" type="text"
                                                       placeholder="Öğrenci Telefonunu Giriniz"
                                                       value="<?= htmlspecialchars($ogr['ogrenci_tel'] ?? '') ?>" />
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputogrencimail">Öğrenci Mail Adresi</label>
                                                <input class="form-control" id="inputogrencimail" name="inputogrencimail" type="email"
                                                       placeholder="Öğrenci Mail Adresi Giriniz"
                                                       value="<?= htmlspecialchars($ogr['ogrenci_mail'] ?? '') ?>" />
                                            </div>

                                            <div class="row gx-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="small mb-1">Cinsiyet</label>
                                                    <select class="form-select" name="ogrencicinsiyet" aria-label="Cinsiyet Seçimi">
                                                        <option disabled value="" <?= !isset($ogr['ogrenci_cinsiyet']) ? 'selected':''; ?>>Cinsiyet Seçiniz</option>
                                                        <option value="1" <?= (isset($ogr['ogrenci_cinsiyet']) && (int)$ogr['ogrenci_cinsiyet']===1)?'selected':''; ?>>Erkek</option>
                                                        <option value="0" <?= (isset($ogr['ogrenci_cinsiyet']) && (int)$ogr['ogrenci_cinsiyet']===0)?'selected':''; ?>>Kadın</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small mb-1" for="inputogrencidogumtar">Öğrenci Doğum Tarihi</label>
                                                    <input class="form-control" id="inputogrencidogumtar" name="inputogrencidogumtar" type="date"
                                                           value="<?= htmlspecialchars($ogr['ogrenci_dogumtar'] ?? '') ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4">
                                    <div class="card mb-4">
                                        <div class="card-header">Kurs Bilgileri</div>
                                        <div class="card-body">
                                            <div class="row gx-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="small mb-1">Dönem</label>
                                                    <select class="form-select" name="ogrencidonem" aria-label="Dönem Seçimi">
                                                        <option value="0" disabled <?= empty($ogr['donem_id'])?'selected':''; ?>>Dönem Seçiniz</option>
                                                        <?php foreach ($donems as $donemss): ?>
                                                            <option value="<?= $donemss['donem_id']; ?>"
                                                                <?= ((int)($ogr['donem_id'] ?? 0) === (int)$donemss['donem_id']) ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($donemss['donem_adi']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small mb-1">Sınıf</label>
                                                    <select class="form-select" name="ogrencisinif" aria-label="Sınıf Seçimi">
                                                        <option value="0" disabled <?= empty($ogr['sinif_id'])?'selected':''; ?>>Sınıf Seçiniz</option>
                                                        <?php foreach ($sinifs as $sinifss): ?>
                                                            <option value="<?= $sinifss['sinif_id']; ?>"
                                                                <?= ((int)($ogr['sinif_id'] ?? 0) === (int)$sinifss['sinif_id']) ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($sinifss['sinif_adi']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row gx-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="small mb-1">Şube</label>
                                                    <select class="form-select" name="ogrencisube" aria-label="Şube Seçimi">
                                                        <option value="" disabled <?= empty($ogr['sube_id'])?'selected':''; ?>>Şube Seçiniz</option>
                                                        <?php foreach ($subes as $subess): ?>
                                                            <option value="<?= $subess['sube_id']; ?>"
                                                                <?= ((int)($ogr['sube_id'] ?? 0) === (int)$subess['sube_id']) ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($subess['sube_adi']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small mb-1">Grup</label>
                                                    <select class="form-select" name="ogrencigrup" aria-label="Grup Seçimi">
                                                        <option value="" disabled <?= empty($ogr['grup_id'])?'selected':''; ?>>Grup Seçiniz</option>
                                                        <?php foreach ($grups as $grupss): ?>
                                                            <option value="<?= $grupss['grup_id']; ?>"
                                                                <?= ((int)($ogr['grup_id'] ?? 0) === (int)$grupss['grup_id']) ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($grupss['grup_adi']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1">Alan</label>
                                                <select class="form-select" name="ogrencialan" aria-label="Alan Seçimi">
                                                    <option value="" disabled <?= empty($ogr['alan_id'])?'selected':''; ?>>Alan Seçiniz</option>
                                                    <?php foreach ($alans as $alanss): ?>
                                                        <option value="<?= $alanss['alan_id']; ?>"
                                                            <?= ((int)($ogr['alan_id'] ?? 0) === (int)$alanss['alan_id']) ? 'selected' : ''; ?>>
                                                            <?= htmlspecialchars($alanss['alan_adi']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="row gx-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="small mb-1">İl</label>
                                                    <select class="form-select" name="ogrenciil" id="ogrenciil" aria-label="İl Seçimi">
                                                        <option value="" disabled <?= empty($ogr['il_id'])?'selected':''; ?>>İl Seçiniz</option>
                                                        <?php foreach ($ils as $ilss): ?>
                                                            <option value="<?= $ilss['il_id']; ?>"
                                                                <?= ((int)($ogr['il_id'] ?? 0) === (int)$ilss['il_id']) ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($ilss['il_adi']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small mb-1">İlçe</label>
                                                    <select class="form-select" name="ogrenciilce" id="ogrenciilce" aria-label="İlçe Seçimi">
                                                        <option value="" disabled selected>İlçe Seçiniz</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputAdres">Adres</label>
                                                <textarea class="form-control" id="inputAdres" name="ogrenciadres" placeholder="Lütfen Adres Giriniz" rows="8"><?= htmlspecialchars($ogr['ogrenci_adres'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4">
                                    <div class="card mb-4 mb-xl-0">
                                        <div class="card-header">Veli Bilgisi</div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="small mb-1">Veli Seçiniz</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="veli" id="anne" value="anne" />
                                                    <label class="form-check-label" for="anne">Anne</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="veli" id="baba" value="baba" />
                                                    <label class="form-check-label" for="baba">Baba</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputvelitc">Veli Tc Bilgisi</label>
                                                <input class="form-control" id="inputvelitc" name="inputvelitc" type="text"
                                                       placeholder="Veli Tc Giriniz"
                                                       value="<?= htmlspecialchars($veli['veli_tc'] ?? '') ?>" />
                                            </div>

                                            <div class="row gx-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="small mb-1" for="inputveliadi">Veli Adı</label>
                                                    <input class="form-control" id="inputveliadi" name="inputveliadi" type="text"
                                                           placeholder="Veli Adı Giriniz"
                                                           value="<?= htmlspecialchars($veli['veli_adi'] ?? '') ?>" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small mb-1" for="inputvelisoyadi">Veli Soyadı</label>
                                                    <input class="form-control" id="inputvelisoyadi" name="inputvelisoyadi" type="text"
                                                           placeholder="Veli Soyadı Giriniz"
                                                           value="<?= htmlspecialchars($veli['veli_soyadi'] ?? '') ?>" />
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputvelitel">Veli Telefon Bilgisi</label>
                                                <input class="form-control" id="inputvelitel" name="inputvelitel" type="text"
                                                       placeholder="Veli Telefon Giriniz"
                                                       value="<?= htmlspecialchars($veli['veli_tel'] ?? '') ?>" />
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputvelimail">Veli Mail Adresi Bilgisi</label>
                                                <input class="form-control" id="inputvelimail" name="inputvelimail" type="email"
                                                       placeholder="Veli Mail Adresi Giriniz"
                                                       value="<?= htmlspecialchars($veli['veli_mail'] ?? '') ?>" />
                                            </div>

                                            <div class="mb-3">
                                                <label class="small mb-1">Veli Adres Bilgisi Öğrenci İle Aynı</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="veliadres" id="adresevet" value="evet" onclick="toggleVeliBilgisi()" />
                                                    <label class="form-check-label" for="adresevet">Evet</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="veliadres" id="adreshayir" value="hayir" onclick="toggleVeliBilgisi()" />
                                                    <label class="form-check-label" for="adreshayir">Hayır</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gizlenecek/Açılacak Veli Bilgisi Alanı -->
                                <div class="col-xl-12 mb-3" id="veliBilgisi" style="display: none;">
                                    <div class="card mb-4 mb-xl-0">
                                        <div class="card-header">Veli Adres Bilgisi</div>
                                        <div class="card-body">
                                            <div class="row gx-3">
                                                <label class="small mb-1" for="veliAdres">Veli Adresi</label>
                                                <textarea class="form-control" id="veliAdres" name="veliAdres" placeholder="Lütfen Adres Giriniz" rows="8"><?= htmlspecialchars($veli['veli_adres'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div class="card mb-4 mb-xl-0">
                                        <div class="card-header">Kayıt İşlemi</div>
                                        <div class="card-body">
                                            <input id="action_type" type="hidden" name="action_type" value="edit"/>
                                            <button class="btn btn-primary" id="saveCustomerButton" type="submit">Öğrenciyi Kaydet</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <footer class="footer-admin mt-auto footer-light">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a>&middot;<a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
<?php
// Güvenli parametre
$ogrenci_id = (int)($_GET['ogrenci_id'] ?? 0);
if ($ogrenci_id <= 0) { header('Location: ogrenci-listesi.php'); exit; }

if ($_POST) {

    /* -------------------- ÖĞRENCİ UPDATE -------------------- */
    $idKey   = 'ogrenci_id';
    $columns = []; $values = [];

    $columns[]='ogrenci_numara';   $values[]=$_POST['inputogrencinumara'] ?? '';
    $columns[]='ogrenci_tc';       $values[]=$_POST['inputtc'] ?? '';
    $columns[]='ogrenci_adi';      $values[]=$_POST['inputogrenciadi'] ?? '';
    $columns[]='ogrenci_soyadi';   $values[]=$_POST['inputogrencisoyadi'] ?? '';
    $columns[]='ogrenci_tel';      $values[]=$_POST['inputogrencitel'] ?? '';
    $columns[]='ogrenci_mail';     $values[]=$_POST['inputogrencimail'] ?? '';
    $columns[]='ogrenci_cinsiyet'; $values[]=(($_POST['ogrencicinsiyet'] ?? '') === '') ? null : (int)$_POST['ogrencicinsiyet'];
    $columns[]='ogrenci_dogumtar'; $values[]=$_POST['inputogrencidogumtar'] ?: null;

    $columns[]='donem_id';         $values[]=(int)($_POST['ogrencidonem']  ?? 0);
    $columns[]='sinif_id';         $values[]=(int)($_POST['ogrencisinif'] ?? 0);
    $columns[]='sube_id';          $values[]=(int)($_POST['ogrencisube']  ?? 0);
    $columns[]='grup_id';          $values[]=(int)($_POST['ogrencigrup']  ?? 0);
    $columns[]='alan_id';          $values[]=(int)($_POST['ogrencialan']  ?? 0);
    $columns[]='il_id';            $values[]=(int)($_POST['ogrenciil']    ?? 0);
    $columns[]='ilce_id';          $values[]=(int)($_POST['ogrenciilce']  ?? 0);
    $columns[]='ogrenci_adres';    $values[]=$_POST['ogrenciadres'] ?? '';

    $columns[]=$idKey;             $values[]=$ogrenci_id;

    $ogrRes = $Ydil->newUpdate('ogrenci', $columns, $values, $idKey);


    /* -------------------- VELİ UPDATE / INSERT -------------------- */
    // "Veli adresi öğrenciyle aynı" seçiliyse adresi eşitle
    $veliAdres = $_POST['veliAdres'] ?? '';
    if (($_POST['veliadres'] ?? '') === 'evet') {
        $veliAdres = $_POST['ogrenciadres'] ?? '';
    }

    // Veli var mı?
    $veliRow = $Ydil->getone("SELECT veli_id FROM veli WHERE ogrenci_id={$ogrenci_id} LIMIT 1");

    if ($veliRow) {
        // UPDATE (idKey = veli_id)
        $vIdKey = 'veli_id';
        $vCols = []; $vVals = [];

        $vCols[]='veli_tc';     $vVals[]=$_POST['inputvelitc']     ?? '';
        $vCols[]='veli_adi';    $vVals[]=$_POST['inputveliadi']    ?? '';
        $vCols[]='veli_soyadi'; $vVals[]=$_POST['inputvelisoyadi'] ?? '';
        $vCols[]='veli_tel';    $vVals[]=$_POST['inputvelitel']    ?? '';
        $vCols[]='veli_mail';   $vVals[]=$_POST['inputvelimail']   ?? '';
        $vCols[]='veli_adres';  $vVals[]=$veliAdres;

        $vCols[]=$vIdKey;       $vVals[]=(int)$veliRow['veli_id'];

        $velRes = $Ydil->newUpdate('veli', $vCols, $vVals, $vIdKey);
    } else {
        // INSERT (newInsert varsa onu kullan, yoksa run)
        if (method_exists($Ydil, 'newInsert')) {
            $velRes = $Ydil->newInsert(
                'veli',
                ['ogrenci_id','veli_adi','veli_soyadi','veli_tc','veli_tel','veli_mail','veli_adres'],
                [
                    $ogrenci_id,
                    $_POST['inputveliadi']    ?? '',
                    $_POST['inputvelisoyadi'] ?? '',
                    $_POST['inputvelitc']     ?? '',
                    $_POST['inputvelitel']    ?? '',
                    $_POST['inputvelimail']   ?? '',
                    $veliAdres
                ]
            );
        } else {
            $velRes = $Ydil->run("
                INSERT INTO veli (ogrenci_id, veli_adi, veli_soyadi, veli_tc, veli_tel, veli_mail, veli_adres)
                VALUES (?,?,?,?,?,?,?)
            ", [
                $ogrenci_id,
                $_POST['inputveliadi']    ?? '',
                $_POST['inputvelisoyadi'] ?? '',
                $_POST['inputvelitc']     ?? '',
                $_POST['inputvelitel']    ?? '',
                $_POST['inputvelimail']   ?? '',
                $veliAdres
            ]) ? 1 : 0;
        }
    }

    /* -------------------- ALERT -------------------- */
    // Bazı update’ler 0 satır etkileyebilir (değer değişmemişse). Yine de success göstermek istersen koşulu yumuşat.
    $deger = ($ogrRes == 1 && $velRes == 1) ? 1 : 0;

    if ($deger == 1) {
        echo '<script>
            Swal.fire({
                title:"Başarılı",
                text:"Kayıt güncellendi",
                icon:"success",
                confirmButtonText:"Tamam"
            }).then(()=>{ window.location.href="ogrenci-bilgisi.php?ogrenci_id='.$ogrenci_id.'"; });
        </script>';
    } else {
        echo '<script>
            Swal.fire({
                title:"Bilgi",
                text:"Değişiklik yapılmadı veya bir alan güncellenemedi",
                icon:"warning",
                confirmButtonText:"Tamam"
            }).then(()=>{ window.location.href="ogrenci-bilgisi.php?ogrenci_id='.$ogrenci_id.'"; });
        </script>';
    }
}
?>
<?php
$mevcutIl   = (int)($ogr['il_id']   ?? 0);
$mevcutIlce = (int)($ogr['ilce_id'] ?? 0);
?>
<script>
    $(document).ready(function () {
        // Kayıt akışı: il değişince ilçeleri getir
        $('#ogrenciil').on('change', function () {
            var ilId = $(this).val();
            if (!ilId) {
                $('#ogrenciilce').html('<option value="" disabled selected>İlçe Seçiniz</option>');
                return;
            }
            $.ajax({
                url: 'get_ilceler.php',
                type: 'POST',
                data: { il_id: ilId },
                success: function (data) {
                    // Sunucudan sadece option’lar geliyor
                    $('#ogrenciilce').html('<option value="" disabled selected>İlçe Seçiniz</option>' + data);
                },
                error: function (xhr, status, error) {
                    console.log('AJAX Hatası:', error);
                }
            });
        });

        // EDIT akışı: sayfa açılır açılmaz mevcut il/ilçe’yi yükle ve seç
        var mevcutIl   = <?= $mevcutIl ?>;
        var mevcutIlce = <?= $mevcutIlce ?>;

        if (mevcutIl > 0) {
            // İl select’inde mevcut ili göster
            $('#ogrenciil').val(String(mevcutIl));

            // Mevcut il’e ait ilçeleri çek
            $.ajax({
                url: 'get_ilceler.php',
                type: 'POST',
                data: { il_id: mevcutIl },
                success: function (data) {
                    // Option’ları bas ve mevcut ilçeyi seç
                    $('#ogrenciilce').html('<option value="" disabled>İlçe Seçiniz</option>' + data);
                    if (mevcutIlce > 0) {
                        $('#ogrenciilce').val(String(mevcutIlce));
                    } else {
                        // İlçe yoksa placeholder seçili kalsın
                        $('#ogrenciilce option:first').prop('selected', true);
                    }
                },
                error: function (xhr, status, error) {
                    console.log('AJAX Hatası:', error);
                }
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="js/scripts.js"></script>
</body>
</html>