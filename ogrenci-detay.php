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

<!-- Ödeme Modalı (sozlesme-guncelleme.php'den uyarlandı) -->
<div class="modal fade" id="add_fees_collect" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div class="d-flex align-items-center">
					<h4 class="modal-title">Sözleşme Taksit Ödeme </h4>
					<span class="badge badge-sm bg-primary ms-2" id="badgeSozNo"> </span>
				</div>
				<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
					<i class="ti ti-x"></i>
				</button>
			</div>
			<form id="formTaksitTahsil" action="#" method="post" autocomplete="off">
				<input type="hidden" id="hidTaksitId" name="taksit_id">
				<input type="hidden" id="hidOgrenciId" name="ogrenci_id">
				<input type="hidden" id="hidSozlesmeId" name="sozlesme_id">
				<input type="hidden" id="hidTaksitVade" name="taksit_vade">
				<input type="hidden" id="hidTaksitTutarRaw" name="taksit_tutar_raw">
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-4">
							<div class="mb-3">
								<label class="form-label text-warning text-muted mb-1">Toplam Tutar</label>
								<input type="text" class="form-control text-warning text-end" id="ozetToplam"
									value="0,00 TL" readonly>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="mb-3">
								<label class="form-label text-success text-muted mb-1">Alınan Peşinat</label>
								<input type="text" class="form-control text-success text-end" id="ozetPesinat"
									value="0,00 TL" readonly>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="mb-3">
								<label class="form-label text-danger mb-1">Kalan Borç Tutarı</label>
								<input type="text" class="form-control text-end text-danger fw-semibold" id="ozetKalan"
									value="0,00 TL" readonly>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="mb-3">
								<label class="form-label text-info mb-1">Taksit Tutarı</label>
								<input type="text" class="form-control text-info text-end" id="taksitTutarGoster"
									value="0,00 TL" readonly>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="mb-3">
								<label class="form-label">Ödeme Türü</label>
								<select class="form-select select2" name="yontem_id" required>
									<option value="">Ödeme Türü Seçiniz</option>
									<?php foreach ($odemeYontemleri as $y): ?>
										<option value="<?= (int) $y['yontem_id'] ?>">
											<?= htmlspecialchars($y['yontem_adi']) ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="mb-3">
								<label class="form-label">Kasa</label>
								<select class="form-select select2" name="kasa_id" required>
									<option value="">Kasa Türü Seçiniz</option>
									<?php foreach ($kasalar as $k): ?>
										<option value="<?= (int) $k['kasa_id'] ?>">
											<?= htmlspecialchars($k['kasa_adi']) ?>
											<?php
											if (!empty($k['kasa_tipi']))
												echo ' - ' . htmlspecialchars($k['kasa_tipi']);
											?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="mb-3">
							<label class="form-label">Ödeme Tarihi</label>
							<div class="input-icon">
								<input type="text" class="form-control datetimepicker" name="odeme_tarihi"
									value="<?= date('d.m.Y') ?>">
								<span class="input-icon-addon"><i class="ti ti-calendar"></i></span>
							</div>
						</div>
					</div>

					<div id="alertFazla" class="alert alert-outline-danger alert-dismissible fade show d-none">
						Girmiş Olduğunuz Tutar Kalan Ödemenizden Fazladır.
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>
					<div class="bg-light-300 p-3 pb-0 rounded pb-4">
						<div class="row align-items-center">
							<div class="col-lg-12">
								<div class="d-flex align-items-center justify-content-between">
									<div class="status-title">
										<h5>Durum</h5>
										<p>Farklı Tutar Ödemek İstiyorum</p>
									</div>
									<div class="form-check form-switch">
										<input class="form-check-input" type="checkbox" role="switch" id="chkFarkli">
									</div>
								</div>
							</div>
							<div class="col-lg-12">
								<div class="mb-0">
									<label class="form-label">Tutar Giriniz</label>
									<input type="text" class="form-control text-end" id="inpOdenecekTutar"
										placeholder="0,00 TL" disabled>
								</div>
							</div>
						</div>
					</div>

				</div>

				<div class="modal-footer">
					<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">Vazgeç</a>
					<button type="submit" class="btn btn-primary">Tahsil Et</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /Ödeme Modalı -->


<!-- External Libs -->
<script src="assets/js/moment.js"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
<script src="assets/plugins/select2/js/select2.min.js"></script>
<!-- SweetAlert2 (Emin olmak için) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
	// --------------------------------------------------------------------------
	// 2) Ödeme Modalı Mantığı (sozlesme-guncelleme.php'den taşındı)
	// --------------------------------------------------------------------------
	document.addEventListener('DOMContentLoaded', () => {
		// Init Plugins
		if ($('.select2').length > 0) { $('.select2').select2({ dropdownParent: $('#add_fees_collect') }); }
		if ($('.datetimepicker').length > 0) {
			$('.datetimepicker').datetimepicker({
				format: 'DD.MM.YYYY',
				icons: {
					up: "fas fa-angle-up",
					down: "fas fa-angle-down",
					next: 'fas fa-angle-right',
					previous: 'fas fa-angle-left'
				}
			});
		}

		const modalEl = document.getElementById('add_fees_collect');
		const bsModal = () => new bootstrap.Modal(modalEl);
		const badgeSozNo = document.getElementById('badgeSozNo');
		const inpToplam = document.getElementById('ozetToplam');
		const inpPesinat = document.getElementById('ozetPesinat');
		const inpKalan = document.getElementById('ozetKalan');
		const inpTaksit = document.getElementById('taksitTutarGoster');
		const chkFarkli = document.getElementById('chkFarkli');
		const inpCustom = document.getElementById('inpOdenecekTutar');
		const alertBox = document.getElementById('alertFazla');
		const formTaksit = document.getElementById('formTaksitTahsil');

		const tlFmt = n => (Number(n || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
		const tlParse = s => {
			if (!s) return 0;
			s = String(s).replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
			const n = parseFloat(s);
			return isNaN(n) ? 0 : n;
		};

		const hideAlert = () => alertBox && alertBox.classList.add('d-none');
		const showAlert = () => alertBox && alertBox.classList.remove('d-none');

		// "Farklı Tutar" Toggle
		chkFarkli?.addEventListener('change', () => {
			hideAlert();
			if (chkFarkli.checked) {
				inpTaksit.value = tlFmt(0);
				inpCustom.removeAttribute('disabled');
				inpCustom.value = '';
				inpCustom.focus();
			} else {
				const raw = parseFloat(modalEl.dataset.taksitKalanRaw || '0');
				inpCustom.value = '';
				inpCustom.setAttribute('disabled', 'disabled');
				inpTaksit.value = tlFmt(raw);
			}
		});

		// Custom alan validasyon
		inpCustom?.addEventListener('input', (e) => {
			let v = e.target.value;
			// Basit maske
			v = v.replace(/[^\d,\.]/g, '');

			const kalanBorc = parseFloat(modalEl.dataset.kalanBorcRaw || '0');
			const valNum = tlParse(v);

			if (valNum > kalanBorc) showAlert(); else hideAlert();
			e.target.value = v;
		});

		// "ÖDE" butonuna tıklanınca (Delegate Event çünkü butonlar AJAX ile geliyor)
		document.addEventListener('click', async (e) => {
			const btn = e.target.closest('.btn-ode');
			if (!btn) return;
			e.preventDefault();

			const taksitId = btn.dataset.taksitId;
			const ogrenciId = btn.dataset.ogrenciId;
			const sozId = btn.dataset.sozlesmeId;

			if (!taksitId || !sozId) return;

			// Reset UI
			hideAlert();
			if (chkFarkli) chkFarkli.checked = false;
			if (inpCustom) { inpCustom.value = ''; inpCustom.disabled = true; }
			if (inpToplam) inpToplam.value = '';

			// Backend'den detay çek (Taksit ve Sözleşme özeti)
			try {
				const url = `sozlesme-ajax/taksit-ozet.php?taksit_id=${taksitId}&sozlesme_id=${sozId}`;
				const r = await fetch(url);
				const j = await r.json();

				if (!j.ok) {
					Swal.fire('Hata', j.msg || 'Veri alınamadı', 'error');
					return;
				}

				// Modal alanlarını doldur
				if (badgeSozNo) badgeSozNo.textContent = j.sozlesme?.no || '';
				if (inpToplam) inpToplam.value = tlFmt(j.ozet?.toplam_taksit);
				if (inpPesinat) inpPesinat.value = tlFmt(j.ozet?.pesinat);
				if (inpKalan) inpKalan.value = tlFmt(j.ozet?.kalan_borc);
				if (inpTaksit) inpTaksit.value = tlFmt(j.taksit?.kalan);

				// Dataset'e sakla
				modalEl.dataset.taksitId = taksitId;
				modalEl.dataset.sozlesmeId = sozId;
				modalEl.dataset.ogrenciId = ogrenciId;
				modalEl.dataset.taksitKalanRaw = j.taksit?.kalan || 0;
				modalEl.dataset.kalanBorcRaw = j.ozet?.kalan_borc || 0;

				// Modalı aç
				const myModal = new bootstrap.Modal(modalEl);
				myModal.show();

			} catch (err) {
				console.error(err);
				Swal.fire('Hata', 'Sunucu ile iletişim hatası', 'error');
			}
		});

		// Form Submit
		formTaksit?.addEventListener('submit', async (ev) => {
			ev.preventDefault();
			const btnSubmit = formTaksit.querySelector('button[type="submit"]');

			// Verileri topla
			const taksit_id = modalEl.dataset.taksitId;
			const sozlesme_id = modalEl.dataset.sozlesmeId;
			const ogrenci_id = modalEl.dataset.ogrenciId;
			const yontem_id = formTaksit.querySelector('[name="yontem_id"]').value;
			const kasa_id = formTaksit.querySelector('[name="kasa_id"]').value;
			const tarih = formTaksit.querySelector('[name="odeme_tarihi"]').value;

			const taksit_raw = parseFloat(modalEl.dataset.taksitKalanRaw || 0);
			const farkli = chkFarkli.checked ? 1 : 0;
			let odenecek = farkli ? tlParse(inpCustom.value) : taksit_raw;

			if (!yontem_id || !kasa_id) {
				Swal.fire('Uyarı', 'Lütfen Ödeme Türü ve Kasa seçiniz.', 'warning');
				return;
			}
			if (odenecek <= 0) {
				Swal.fire('Uyarı', 'Geçersiz tutar.', 'warning');
				return;
			}

			// Butonu kilitle
			const oldHtml = btnSubmit.innerHTML;
			btnSubmit.disabled = true;
			btnSubmit.innerHTML = 'İşleniyor...';

			try {
				const fd = new FormData();
				fd.append('taksit_id', taksit_id);
				fd.append('sozlesme_id', sozlesme_id);
				fd.append('ogrenci_id', ogrenci_id);
				fd.append('yontem_id', yontem_id);
				fd.append('kasa_id', kasa_id);
				fd.append('odeme_tarihi', tarih);
				fd.append('farkli_tutar', farkli);
				fd.append('odenecek_tutar', odenecek);
				fd.append('taksit_tutar_raw', taksit_raw);
				fd.append('aciklama', 'Taksit Tahsilatı (Yeni Panel)');

				const res = await fetch('sozlesme-ajax/taksit-tahsil-et.php', { method: 'POST', body: fd });
				const json = await res.json();

				if (json.ok) {
					Swal.fire({
						icon: 'success', title: 'Başarılı', text: 'Tahsilat yapıldı.', timer: 1500, showConfirmButton: false
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire('Hata', json.msg || 'İşlem başarısız', 'error');
				}

			} catch (err) {
				console.error(err);
				Swal.fire('Hata', 'Bir sorun oluştu.', 'error');
			} finally {
				btnSubmit.disabled = false;
				btnSubmit.innerHTML = oldHtml;
			}
		});
	});
</script>

</body>

</html>