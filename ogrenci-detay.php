<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

if (!isset($_GET['id']) || $_GET['id'] === '') {
	header("Location: ogrenciler.php"); // listeye dön
	exit;
}
$ogr_no = $_GET['id'];

$sql = "
    SELECT 
        o.*,
        il.il_adi,
        ilce.ilce_adi
    FROM ogrenci1 o
    LEFT JOIN il ON il.il_id = o.il_id
    LEFT JOIN ilce ON ilce.ilce_id = o.ilce_id
    WHERE o.ogrenci_numara = :numara
    LIMIT 1
";

$ogrenci = $db->gets($sql, ['numara' => $ogr_no]);

$sql1 = "WITH s AS (
  SELECT s.sozlesme_id
  FROM sozlesme1 s
  JOIN ogrenci1  o ON o.ogrenci_id = s.ogrenci_id
  WHERE o.ogrenci_numara = :ogr_no
)
SELECT
  (SELECT COUNT(*) FROM s) AS sozlesme_sayisi,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id) AS toplam_taksit,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id
     WHERE COALESCE(t.odendi_tutar,0) >= t.tutar) AS odenen_taksit,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id
     WHERE COALESCE(t.odendi_tutar,0) < t.tutar AND t.vade_tarihi < CURDATE()) AS gecikmis_taksit,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id
     WHERE COALESCE(t.odendi_tutar,0) < t.tutar) AS kalan_taksit;
";

$sozlesme = $db->gets($sql1, [':ogr_no' => $ogr_no]);

// -- ÖDEME MODALI İÇİN GEREKLİ VERİLER --
$odemeYontemleri = $db->finds('odeme_yontem1', 'durum', 1, ['yontem_id', 'yontem_adi', 'sira']);
usort($odemeYontemleri, fn($a, $b) => ($a['sira'] <=> $b['sira']));

$aktifSubeId = (int) ($_SESSION['sube_id'] ?? 0);
if ($aktifSubeId) {
	$kasalar = $db->get("SELECT kasa_id, kasa_adi, kasa_tipi FROM kasa1 WHERE durum=1 AND (sube_id=:sid OR sube_id IS NULL) ORDER BY sira ASC, kasa_adi ASC", [':sid' => $aktifSubeId]);
} else {
	$kasalar = $db->finds('kasa1', 'durum', 1, ['kasa_id', 'kasa_adi', 'kasa_tipi']);
	usort($kasalar, fn($a, $b) => ($a['sira'] <=> $b['sira']) ?: strcmp($a['kasa_adi'], $b['kasa_adi']));
}
// -- ---------------------------------- --

?>

<?php
$pageTitle = $ogrenci['ogrenci_adi'] . " " . $ogrenci['ogrenci_soyadi'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
$page_styles[] = ['href' => 'assets/css/bootstrap-datetimepicker.min.css'];
$page_styles[] = ['href' => 'assets/plugins/select2/css/select2.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
require_once 'ogrenci-detay-ortak.php';
?>


<div class="col-xxl-9 col-xl-8">

	<!-- Stats Row -->
	<?php
	// Finansal Verileri Hesapla
	$studentId = $ogrenci['ogrenci_id'];
	$finansSql = "
                                SELECT 
                                    SUM(s.toplam_ucret) as toplam_borc,
                                    (SELECT SUM(t.odendi_tutar) FROM taksit1 t JOIN sozlesme1 s2 ON t.sozlesme_id = s2.sozlesme_id WHERE s2.ogrenci_id = :oid) as toplam_odenen, 
                                    (SELECT SUM(o1.tutar) FROM odeme1 o1 JOIN sozlesme1 s3 ON o1.sozlesme_id = s3.sozlesme_id WHERE s3.ogrenci_id = :oid) as toplam_pesinat,
                                    (SELECT MIN(t.vade_tarihi) FROM taksit1 t JOIN sozlesme1 s4 ON t.sozlesme_id = s4.sozlesme_id WHERE s4.ogrenci_id = :oid AND t.odendi_tutar < t.tutar AND t.vade_tarihi >= CURDATE()) as sonraki_taksit
                                FROM sozlesme1 s
                                WHERE s.ogrenci_id = :oid AND s.durum = 1
                            ";
	$finans = $db->get($finansSql, [':oid' => $studentId])[0] ?? [];

	$toplamBorc = (float) ($finans['toplam_borc'] ?? 0);
	$toplamOdenen = (float) ($finans['toplam_odenen'] ?? 0) + (float) ($finans['toplam_pesinat'] ?? 0);
	$kalanBorc = max(0, $toplamBorc - $toplamOdenen);
	$sonrakiTaksit = $finans['sonraki_taksit'] ? date('d.m.Y', strtotime($finans['sonraki_taksit'])) : '-';
	?>
	<div class="row">
		<div class="col-xl-3 col-sm-6 d-flex">
			<div class="card flex-fill">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center overflow-hidden">
							<span class="avatar avatar-md bg-danger-transparent rounded flex-shrink-0 text-danger"><i
									class="ti ti-currency-lira fs-20"></i></span>
							<div class="ms-2 overflow-hidden">
								<p class="fs-12 fw-medium mb-1 text-truncate">Toplam Sözleşme</p>
								<h4><?= number_format($toplamBorc, 2, ',', '.') ?> ₺</h4>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-sm-6 d-flex">
			<div class="card flex-fill">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center overflow-hidden">
							<span class="avatar avatar-md bg-success-transparent rounded flex-shrink-0 text-success"><i
									class="ti ti-wallet fs-20"></i></span>
							<div class="ms-2 overflow-hidden">
								<p class="fs-12 fw-medium mb-1 text-truncate">Tahsil Edilen</p>
								<h4><?= number_format($toplamOdenen, 2, ',', '.') ?> ₺</h4>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-sm-6 d-flex">
			<div class="card flex-fill">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center overflow-hidden">
							<span class="avatar avatar-md bg-warning-transparent rounded flex-shrink-0 text-warning"><i
									class="ti ti-chart-pie fs-20"></i></span>
							<div class="ms-2 overflow-hidden">
								<p class="fs-12 fw-medium mb-1 text-truncate">Kalan Borç</p>
								<h4><?= number_format($kalanBorc, 2, ',', '.') ?> ₺</h4>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-sm-6 d-flex">
			<div class="card flex-fill">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center overflow-hidden">
							<span class="avatar avatar-md bg-info-transparent rounded flex-shrink-0 text-info"><i
									class="ti ti-calendar-event fs-20"></i></span>
							<div class="ms-2 overflow-hidden">
								<p class="fs-12 fw-medium mb-1 text-truncate">Sonraki Taksit</p>
								<h4><?= $sonrakiTaksit ?></h4>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Stats Row -->

	<div class="row">
		<div class="col-md-12">

			<!-- Tabs -->
			<ul class="nav nav-tabs nav-tabs-bottom mb-4">
				<li class="nav-item">
					<a href="#genel-bakis" data-bs-toggle="tab" class="nav-link active"><i
							class="ti ti-layout-dashboard me-2"></i>Genel Bakış</a>
				</li>
				<li class="nav-item">
					<a href="#aile-iletisim" data-bs-toggle="tab" class="nav-link"><i class="ti ti-users me-2"></i>Aile
						& İletişim</a>
				</li>
				<li class="nav-item">
					<a href="#egitim-belge" data-bs-toggle="tab" class="nav-link"><i
							class="ti ti-file-certificate me-2"></i>Eğitim & Belgeler</a>
				</li>
				<li class="nav-item">
					<a href="#sozlesme-tab" data-bs-toggle="tab" class="nav-link" id="loadSozlesmeTab"><i
							class="ti ti-bookmark-edit me-2"></i>Sözleşme ve Taksitler</a>
				</li>
			</ul>
			<!-- /Tabs -->

			<div class="tab-content">

				<!-- TAB 1: Genel Bakış -->
				<div class="tab-pane fade show active" id="genel-bakis">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-light border">
								<i class="ti ti-info-circle me-2 text-primary"></i>
								<strong>Bilgi:</strong> Öğrenci kimlik ve iletişim bilgileri sol panelde yer almaktadır.
							</div>
						</div>
					</div>

					<!-- Hızlı İşlemler Kartı -->
					<div class="card">
						<div class="card-header">
							<h5>Hızlı İşlemler</h5>
						</div>
						<div class="card-body">
							<div class="d-flex gap-2 flex-wrap">
								<a href="ogrenci-duzenle.php?id=<?= $ogr_no ?>" class="btn btn-outline-primary">
									<i class="ti ti-edit me-2"></i>Bilgileri Düzenle
								</a>
								<a href="sozlesme-olustur.php?id=<?= $ogr_no ?>" class="btn btn-outline-success">
									<i class="ti ti-file-plus me-2"></i>Yeni Sözleşme Ekle
								</a>
								<!-- Eğer aktif sözleşme varsa belge linkleri -->
								<?php
								$aktifSozlesme = $db->get("SELECT sozlesme_id FROM sozlesme1 WHERE ogrenci_id = :oid AND durum = 1 LIMIT 1", [':oid' => $studentId]);
								$sozlesmeId = $aktifSozlesme[0]['sozlesme_id'] ?? 0;
								if ($sozlesmeId > 0): ?>
									<a href="sozlesme-belge.php?id=<?= $sozlesmeId ?>" target="_blank"
										class="btn btn-outline-dark">
										<i class="ti ti-printer me-2"></i>Sözleşme Yazdır
									</a>
									<a href="sozlesme-senet.php?id=<?= $sozlesmeId ?>" target="_blank"
										class="btn btn-outline-warning">
										<i class="ti ti-receipt me-2"></i>Senet Yazdır
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

				<!-- TAB 2: Aile & İletişim -->
				<div class="tab-pane fade" id="aile-iletisim">
					<?php
					// Veli Bilgilerini Çek
					$veliler = $db->get("SELECT * FROM veli1 WHERE ogrenci_id = :oid", [':oid' => $studentId]);
					?>
					<?php if ($veliler): ?>
						<div class="row">
							<?php foreach ($veliler as $veli): ?>
								<div class="col-md-6">
									<div class="card">
										<div class="card-header">
											<h5><i class="ti ti-user-shield me-2"></i>Veli Bilgisi</h5>
										</div>
										<div class="card-body">
											<div class="mb-3">
												<label class="text-muted fs-12">Adı Soyadı</label>
												<h6 class="mb-0">
													<?= htmlspecialchars($veli['veli_adi'] . ' ' . $veli['veli_soyadi']) ?>
												</h6>
											</div>
											<div class="mb-3">
												<label class="text-muted fs-12">Yakınlık</label>
												<h6 class="mb-0">Veli / Vasi</h6>
											</div>
											<div class="mb-3">
												<label class="text-muted fs-12">Telefon</label>
												<h6 class="mb-0"><?= htmlspecialchars($veli['veli_tel']) ?></h6>
											</div>
											<div class="mb-3">
												<label class="text-muted fs-12">Adres</label>
												<p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($veli['veli_adres'])) ?>
												</p>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="alert alert-info">
							<i class="ti ti-info-circle me-2"></i> Bu öğrenci için kayıtlı veli bilgisi bulunamadı. Öğrenci
							kendi velisi olabilir.
						</div>
					<?php endif; ?>
				</div>

				<!-- TAB 3: Eğitim & Belgeler -->
				<div class="tab-pane fade" id="egitim-belge">
					<div class="row">
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5>Resmi Belgeler</h5>
								</div>
								<div class="card-body">
									<?php if ($sozlesmeId > 0): ?>
										<div class="list-group list-group-flush">
											<a href="sozlesme-belge.php?id=<?= $sozlesmeId ?>" target="_blank"
												class="list-group-item list-group-item-action d-flex align-items-center">
												<i class="ti ti-file-text fs-20 me-3 text-primary"></i>
												<div>
													<h6 class="mb-0">Kayıt Sözleşmesi</h6>
												</div>
											</a>
											<a href="sozlesme-senet.php?id=<?= $sozlesmeId ?>" target="_blank"
												class="list-group-item list-group-item-action d-flex align-items-center">
												<i class="ti ti-receipt fs-20 me-3 text-warning"></i>
												<div>
													<h6 class="mb-0">Senet / Taksit Belgeleri</h6>
												</div>
											</a>
											<a href="sozlesme-protokol-yazdir.php?id=<?= $sozlesmeId ?>&type=restructure"
												target="_blank"
												class="list-group-item list-group-item-action d-flex align-items-center">
												<i class="ti ti-refresh fs-20 me-3 text-info"></i>
												<div>
													<h6 class="mb-0">Ek Protokol (Varsa)</h6>
												</div>
											</a>
										</div>
									<?php else: ?>
										<div class="text-center py-4 text-muted">
											<p>Aktif sözleşme yok.</p>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-header">
									<h5>Diğer Belgeler</h5>
								</div>
								<div class="card-body">
									<div class="text-center py-4 text-muted">
										<i class="ti ti-files fs-32 mb-2"></i>
										<p>Yüklenmiş belge bulunmuyor.</p>
										<button class="btn btn-sm btn-outline-primary mt-2">Belge Yükle</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- TAB 4: Sözleşme ve Taksitler (AJAX) -->
				<div class="tab-pane fade" id="sozlesme-tab">
					<div class="text-center py-5" id="sozlesme-loader">
						<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Yükleniyor...</span>
						</div>
						<p class="mt-2 text-muted">Sözleşme verileri yükleniyor...</p>
					</div>
					<div id="sozlesme-content"></div>
				</div>

			</div> <!-- /tab-content -->

		</div>
	</div>
</div>

</div>
</div>
</div>
<!-- /Page Wrapper -->

<!-- Scripts -->
<script data-cfasync="false" src="assets/js/jquery-3.7.1.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap.bundle.min.js"></script>
<script data-cfasync="false" src="assets/js/feather.min.js"></script>
<script data-cfasync="false" src="assets/js/jquery.slimscroll.min.js"></script>
<script data-cfasync="false" src="assets/js/script.js"></script>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		// Sözleşme tabına tıklandığında yükle
		var triggerTabList = [].slice.call(document.querySelectorAll('#loadSozlesmeTab'))
		triggerTabList.forEach(function (triggerEl) {
			var tabTrigger = new bootstrap.Tab(triggerEl)

			triggerEl.addEventListener('shown.bs.tab', function (event) {
				if ($('#sozlesme-content').is(':empty')) {
					loadSozlesmeContent();
				}
			})
		})

		function loadSozlesmeContent() {
			$('#sozlesme-loader').show();
			$('#sozlesme-content').hide();

			$.ajax({
				url: 'ogrenci-detay-sozlesme-icerik.php',
				type: 'GET',
				data: { id: '<?= $ogr_no ?>' },
				success: function (response) {
					$('#sozlesme-content').html(response).fadeIn();
					$('#sozlesme-loader').hide();
				},
				error: function () {
					$('#sozlesme-content').html('<div class="alert alert-danger">Veriler yüklenirken hata oluştu.</div>').show();
					$('#sozlesme-loader').hide();
				}
			});
		}
	});
</script>

</body>

</html>