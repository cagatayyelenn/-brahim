<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

/* Giriş kontrolü: sadece login kullanıcı erişsin */
if (!isset($_SESSION['personel_id'])) {
    header("Location: giris.php", true, 302);
    exit;
}

/* Cache’i kapat (geri tuşunda RAM’den gelmesin) */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

/* Şube seçimi geldiyse doğrula ve session’a yaz */
if (isset($_GET['sec'])) {
    $sec = (int)$_GET['sec'];

    // Bu ID’ye sahip şube var mı?
    $sube = $db->find('sube', 'sube_id', $sec, ['sube_id','sube_adi','sube_durum']);
    if ($sube) {
        $_SESSION['sube_id']  = (int)$sube['sube_id'];
        $_SESSION['sube_adi'] = $sube['sube_adi'];   // ister kullan, ister kullanma

        // history replace ile yönlendir
        echo '<!doctype html><html><head><meta charset="utf-8">';
        echo '<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />';
        echo '</head><body>';
        echo '<script>location.replace("index.php");</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=index.php"></noscript>';
        echo '</body></html>';
        exit;
    } else {
        // Geçersiz id → şube sayfasında kal
        header("Location: sube.php", true, 302);
        exit;
    }
}

/* Eğer zaten şube seçiliyse, direkt anasayfa’ya atmak istersen: */
if (isset($_SESSION['sube_id']) && $_SESSION['sube_id'] > 0) {
    header("Location: index.php", true, 302);
    exit;
}

/* … buradan sonrası şube listeleme arayüzün … */
$subeler = $db->finds('sube');
?>

<?php
$pageTitle = "Şube Seçimi";
$page_styles[] = ['href' => 'assets/plugins/tabler-icons/tabler-icons.css'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
$page_inline_styles = "
body { pointer-events:none; }
.allowed { pointer-events:auto; }
";
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>


		<div class="page-wrapper">
			<div class="content bg-white">
				<div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
					<div class="my-auto mb-2">
						<h3 class="page-title mb-1">Şube Seçiniz</h3>
                    </div>
                </div>
                <div class="row mt-5">
                    <?php foreach ($subeler as $sube): ?>
                        <?php
                        $isActive = ($sube['sube_durum'] == 1);
                        $badgeText = $isActive ? 'Aktif' : 'Pasif';
                        $badgeClass = $isActive ? 'bg-transparent-success text-success' : 'bg-transparent-danger text-danger';
                        $checked = $isActive ? 'checked' : '';
                        ?>
                        <div class="col-xxl-4 col-xl-6">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                    <div class="d-flex align-items-center">
                                    <span class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-2">
                                        <img src="https://bluebellenglishacademy.com/wp-content/uploads/2025/03/cropped-2-270x270.png" alt="Img">
                                    </span>
                                        <h6><?= htmlspecialchars($sube['sube_adi']) ?></h6>
                                    </div>
                                    <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                </div>
                                <div class="card-body pt-0">
                                    <p>
                                        <?= $isActive
                                            ? 'Bu şube sisteme bağlı ve aktif durumda.'
                                            : 'Bu şube şu anda pasif veya bağlantı kesilmiş durumda.' ?>
                                    </p>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center allowed">
                                    <div>
                                        <a href="sube.php?sec=<?= (int)$sube['sube_id'] ?>" class="btn btn-outline-light">
                                            <i class="ti ti-tool me-2"></i>Şubeye Bağlan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
			</div>
		</div>


	</div>

	<script src="assets/js/jquery-3.7.1.min.js" type="birimtext/javascript"></script>
	<script src="assets/js/bootstrap.bundle.min.js" type="birimtext/javascript"></script>
	<script src="assets/js/moment.js" type="birimtext/javascript"></script>
	<script src="assets/plugins/daterangepicker/daterangepicker.js" type="birimtext/javascript"></script>
	<script src="assets/plugins/moment/moment.js" type="birimtext/javascript"></script>
	<script src="assets/js/bootstrap-datetimepicker.min.js" type="birimtext/javascript"></script>
	<script src="assets/js/feather.min.js" type="birimtext/javascript"></script>
	<script src="assets/js/jquery.slimscroll.min.js" type="birimtext/javascript"></script>
	<script src="assets/js/jquery.dataTables.min.js" type="birimtext/javascript"></script>
	<script src="assets/js/dataTables.bootstrap5.min.js" type="birimtext/javascript"></script>
	<script src="assets/js/script.js" type="birimtext/javascript"></script>

</body>
</html>