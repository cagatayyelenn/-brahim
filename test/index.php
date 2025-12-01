<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
?>

<?php
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);

$sql = "SELECT 
    o.ogrenci_id,
    o.ogrenci_numara AS ogrenci_no,
    o.ogrenci_tc,
    CONCAT(o.ogrenci_adi, ' ', o.ogrenci_soyadi) AS ad_soyad,
    o.ogrenci_tel AS telefon,
    o.ogrenci_mail AS email,
    o.ogrenci_cinsiyet AS cinsiyet,
    o.ogrenci_dogumtar AS dogum_tarihi,
    IF(o.aktif = 1, 'Aktif', 'Pasif') AS durum
FROM ogrenci1 o
WHERE o.sube_id = :sube_id
ORDER BY o.ogrenci_numara DESC";

$ogrenciler = $db->get($sql, [':sube_id' => $sube_id]);

/* ------------------ Yardımcı fonksiyonlar ------------------ */
if (!function_exists('h')) {
	function h($s)
	{
		return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
	}
}
if (!function_exists('formatDateTRSafe')) {
	function formatDateTRSafe($d)
	{
		if (function_exists('formatDateTR'))
			return formatDateTR($d);
		if (!$d || $d === '0000-00-00')
			return '-';
		$ts = strtotime($d);
		return $ts ? date('d.m.Y', $ts) : '-';
	}
}

/* ------------------ Sayaçlar (TOPLAM / AKTİF / PASİF) ------------------ */
$toplamOgrenci = is_array($ogrenciler) ? count($ogrenciler) : 0;
$aktifSay = 0;
$pasifSay = 0;

if (!empty($ogrenciler)) {
	foreach ($ogrenciler as $o) {
		$durum = mb_strtolower(trim((string) ($o['durum'] ?? '')), 'UTF-8');
		if ($durum === 'aktif') {
			$aktifSay++;
		} else {
			$pasifSay++;
		}
	}
}

/* ===================== PDO Bağlantısı ===================== */
function ngls_get_pdo($db)
{
	if (isset($db->pdo) && $db->pdo instanceof PDO)
		return $db->pdo;
	if (method_exists($db, 'getPdo')) {
		$pdo = $db->getPdo();
		if ($pdo instanceof PDO)
			return $pdo;
	}
	if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
		$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
		$pdo = new PDO($dsn, DB_USER, DB_PASS, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
		return $pdo;
	}
	throw new Exception('PDO bağlantısı bulunamadı.');
}
$pdo = ngls_get_pdo($db);

/* ===================== POST İşlemleri ===================== */
// Ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gorusme_kaydet'])) {
	$gorusen = isset($_SESSION['ad']) ? trim($_SESSION['ad']) : 'Bilinmiyor';
	$ad_soyad = trim($_POST['ad_soyad'] ?? '');
	$dil = trim($_POST['dil'] ?? '');
	$aciklama = trim($_POST['aciklama'] ?? '');
	$sonuc = trim($_POST['sonuc'] ?? '');

	$hatalar = [];
	if ($ad_soyad === '')
		$hatalar[] = 'Ad Soyad zorunlu.';
	if ($dil === '')
		$hatalar[] = 'Dil zorunlu.';
	if ($sonuc === '')
		$hatalar[] = 'Sonuç zorunlu.';

	if (empty($hatalar)) {
		$stmt = $pdo->prepare("INSERT INTO gorusmeler (ad_soyad, dil, aciklama, gorusen, sonuc) VALUES (?, ?, ?, ?, ?)");
		$stmt->execute([$ad_soyad, $dil, $aciklama, $gorusen, $sonuc]);
		header("Location: " . $_SERVER['REQUEST_URI']);
		exit;
	} else {
		$_SESSION['gorusme_hatalar'] = $hatalar;
		$_SESSION['gorusme_eski'] = [
			'ad_soyad' => $ad_soyad,
			'dil' => $dil,
			'aciklama' => $aciklama,
			'sonuc' => $sonuc
		];
		header("Location: " . $_SERVER['REQUEST_URI']);
		exit;
	}
}

// Güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gorusme_guncelle'])) {
	$id = (int) ($_POST['id'] ?? 0);
	$ad_soyad = trim($_POST['ad_soyad'] ?? '');
	$dil = trim($_POST['dil'] ?? '');
	$aciklama = trim($_POST['aciklama'] ?? '');
	$sonuc = trim($_POST['sonuc'] ?? '');

	if ($id > 0 && $ad_soyad !== '' && $dil !== '' && $sonuc !== '') {
		$stmt = $pdo->prepare("UPDATE gorusmeler SET ad_soyad=?, dil=?, aciklama=?, sonuc=? WHERE id=?");
		$stmt->execute([$ad_soyad, $dil, $aciklama, $sonuc, $id]);
	}
	header("Location: " . $_SERVER['REQUEST_URI']);
	exit;
}

// Sil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gorusme_sil'])) {
	$id = (int) ($_POST['id'] ?? 0);
	if ($id > 0) {
		$stmt = $pdo->prepare("DELETE FROM gorusmeler WHERE id=?");
		$stmt->execute([$id]);
	}
	header("Location: " . $_SERVER['REQUEST_URI']);
	exit;
}

/* ===================== Liste ===================== */
$gorusmeler = [];
try {
	$q = $pdo->query("SELECT id, tarih, ad_soyad, dil, aciklama, gorusen, sonuc
                      FROM gorusmeler
                      ORDER BY tarih DESC
                      LIMIT 500");
	$gorusmeler = $q->fetchAll();
} catch (Exception $e) { /* tablo yoksa sessiz */
}

/* ===================== Sayfa ===================== */
$pageTitle = "Anasayfa";
$page_styles[] = ['href' => 'assets/plugins/owlcarousel/owl.carousel.min.css'];
$page_styles[] = ['href' => 'assets/plugins/owlcarousel/owl.theme.default.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>
<!-- Page Wrapper -->
<div class="page-wrapper">
	<div class="content">

		<!-- Görüşme Listesi -->
		<div class="card mb-4">
			<div class="card-header d-flex align-items-center justify-content-between">
				<h4 class="card-title mb-0">Görüşme Listesi</h4>
				<small class="text-muted">Görüşen: <?= htmlspecialchars($_SESSION['ad'] ?? 'Bilinmiyor'); ?></small>
			</div>
			<div class="card-body">

				<?php if (!empty($_SESSION['gorusme_hatalar'])): ?>
					<div class="alert alert-danger">
						<ul class="mb-0">
							<?php foreach ($_SESSION['gorusme_hatalar'] as $h): ?>
								<li><?= htmlspecialchars($h); ?></li>
							<?php endforeach;
							unset($_SESSION['gorusme_hatalar']); ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="table-responsive">
					<table class="table table-striped table-hover align-middle" id="gorusme-tablo">
						<thead class="table-light">
							<tr>
								<th>#</th>
								<th>Tarih</th>
								<th>Ad Soyad</th>
								<th>Dil</th>
								<th>Açıklama</th>
								<th>Görüşen</th>
								<th>Sonuç</th>
								<th style="width:160px;">İşlemler</th>
							</tr>
						</thead>
						<tbody>

							<!-- YENİ KAYIT SATIRI -->
							<tr class="table-info">
								<td colspan="8" class="p-0">
									<form method="post" class="p-2">
										<div class="row g-2 align-items-end">
											<div class="col-12 col-md-3">
												<label class="form-label mb-1">Ad Soyad <span
														class="text-danger">*</span></label>
												<input type="text" name="ad_soyad" class="form-control" required
													value="<?= htmlspecialchars(($_SESSION['gorusme_eski']['ad_soyad'] ?? '')) ?>"
													placeholder="Ad Soyad">
											</div>
											<div class="col-12 col-md-2">
												<label class="form-label mb-1">Dil <span
														class="text-danger">*</span></label>
												<select name="dil" class="form-select" required>
													<option value="">Seçiniz</option>
													<?php
													$diller = ['Türkçe', 'İngilizce', 'Almanca', 'Fransızca', 'Arapça', 'Farsça', 'İspanyolca', 'İtalyanca', 'Korece', 'Japonca', 'Flemenkçe'];
													$selDil = $_SESSION['gorusme_eski']['dil'] ?? '';
													foreach ($diller as $d) {
														$sel = ($selDil === $d) ? 'selected' : '';
														echo "<option $sel>" . htmlspecialchars($d) . "</option>";
													}
													?>
												</select>
											</div>
											<div class="col-12 col-md-3">
												<label class="form-label mb-1">Açıklama</label>
												<input type="text" name="aciklama" class="form-control"
													value="<?= htmlspecialchars(($_SESSION['gorusme_eski']['aciklama'] ?? '')) ?>"
													placeholder="Kısa not...">
											</div>
											<div class="col-12 col-md-2">
												<label class="form-label mb-1">Sonuç <span
														class="text-danger">*</span></label>
												<select name="sonuc" class="form-select" required>
													<?php
													$sonuclar = ['İletişime Geçildi', 'Kaydedildi', 'Randevu Verildi', 'Kayıt Oldu', 'Olumsuz', 'Beklemede'];
													$selSonuc = $_SESSION['gorusme_eski']['sonuc'] ?? '';
													foreach ($sonuclar as $s) {
														$sel = ($selSonuc === $s) ? 'selected' : '';
														echo "<option $sel>" . htmlspecialchars($s) . "</option>";
													}
													?>
												</select>
											</div>
											<div class="col-12 col-md-2 d-grid">
												<button type="submit" name="gorusme_kaydet"
													class="btn btn-primary">Ekle</button>
											</div>
										</div>
									</form>
									<?php unset($_SESSION['gorusme_eski']); ?>
								</td>
							</tr>
							<!-- /YENİ KAYIT SATIRI -->

							<?php if (!empty($gorusmeler)): ?>
								<?php foreach ($gorusmeler as $row): ?>
									<?php
									$s = mb_strtolower($row['sonuc'] ?? '', 'UTF-8');
									// Haritalama: class ve metin rengi
									$rowClass = '';
									$textClass = '';
									if ($s === 'iletişime geçildi' || $s === 'iletisime gecildi') {      // YEŞİL + BEYAZ
										$rowClass = 'row-green';
										$textClass = 'text-white';
									} elseif ($s === 'kaydedildi') {                                     // MAVİ + BEYAZ
										$rowClass = 'row-blue';
										$textClass = 'text-white';
									} elseif ($s === 'randevu verildi') {                                // SARI + KOYU
										$rowClass = 'row-yellow';
										$textClass = 'text-dark';
									} elseif ($s === 'olumsuz') {                                        // KIRMIZI + BEYAZ
										$rowClass = 'row-red';
										$textClass = 'text-white';
									} elseif ($s === 'beklemede') {                                      // YEŞİL + BEYAZ
										$rowClass = 'row-green';
										$textClass = 'text-white';
									} elseif ($s === 'kayıt oldu' || $s === 'kayit oldu') {              // İsteğe bağlı: Kaydoldu
										$rowClass = 'row-blue';
										$textClass = 'text-white';
									}
									?>
									<tr class="<?= $rowClass ?> <?= $textClass ?>">
										<td><?= (int) $row['id']; ?></td>
										<td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['tarih']))); ?></td>
										<td><?= htmlspecialchars($row['ad_soyad']); ?></td>
										<td><?= htmlspecialchars($row['dil']); ?></td>
										<td><?= htmlspecialchars($row['aciklama']); ?></td>
										<td><?= htmlspecialchars($row['gorusen']); ?></td>
										<td>
											<span
												class="badge bg-dark bg-opacity-50 <?= $textClass === 'text-dark' ? 'text-dark' : 'text-white' ?>">
												<?= htmlspecialchars($row['sonuc']); ?>
											</span>
										</td>
										<td>
											<button type="button" class="btn btn-sm btn-warning me-1 btn-edit"
												data-id="<?= (int) $row['id']; ?>"
												data-ad="<?= htmlspecialchars($row['ad_soyad']); ?>"
												data-dil="<?= htmlspecialchars($row['dil']); ?>"
												data-aciklama="<?= htmlspecialchars($row['aciklama']); ?>"
												data-sonuc="<?= htmlspecialchars($row['sonuc']); ?>" data-bs-toggle="modal"
												data-bs-target="#editModal">
												Düzenle
											</button>
											<form method="post" class="d-inline"
												onsubmit="return confirm('Bu kaydı silmek istiyor musunuz?');">
												<input type="hidden" name="id" value="<?= (int) $row['id']; ?>">
												<button class="btn btn-sm btn-danger" name="gorusme_sil">Sil</button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="8" class="text-muted">Henüz kayıt yok.</td>
								</tr>
							<?php endif; ?>

						</tbody>
					</table>
				</div>

				<!-- DataTables Bootstrap 5 CSS -->
				<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.8/fh-3.4.0/datatables.min.css" />

				<style>
					#gorusme-tablo td,
					#gorusme-tablo th {
						vertical-align: middle;
					}

					#gorusme-tablo th {
						font-weight: 600;
					}

					/* Satır renklerini hücre seviyesinde uygula (DataTables/striped/hover override) */
					.row-green>td,
					.row-green>th {
						background-color: #198754 !important;
						color: #fff !important;
					}

					.row-blue>td,
					.row-blue>th {
						background-color: #0d6efd !important;
						color: #fff !important;
					}

					.row-yellow>td,
					.row-yellow>th {
						background-color: #ffc107 !important;
						color: #212529 !important;
					}

					.row-red>td,
					.row-red>th {
						background-color: #dc3545 !important;
						color: #fff !important;
					}

					.table-hover>tbody>tr.row-green:hover>* {
						background-color: #198754 !important;
						color: #fff !important;
					}

					.table-hover>tbody>tr.row-blue:hover>* {
						background-color: #0d6efd !important;
						color: #fff !important;
					}

					.table-hover>tbody>tr.row-yellow:hover>* {
						background-color: #ffc107 !important;
						color: #212529 !important;
					}

					.table-hover>tbody>tr.row-red:hover>* {
						background-color: #dc3545 !important;
						color: #fff !important;
					}

					/* Sarı satırdaki link/badge koyu kalsın */
					.row-yellow a,
					.row-yellow .badge {
						color: #212529 !important;
					}

					.row-green a,
					.row-green .badge,
					.row-blue a,
					.row-blue .badge,
					.row-red a,
					.row-red .badge {
						color: #fff !important;
					}

					/* Sütun başlıklarındaki filtre input’ları için ufak düzen */
					#gorusme-tablo thead tr.filters th {
						padding: .5rem;
					}

					#gorusme-tablo thead tr.filters input {
						height: 32px;
						font-size: .85rem;
					}
				</style>


			</div>
		</div>
		<!-- /Görüşme Listesi -->
		<!-- ÜST BAŞLIK -->
		<div class="d-md-flex d-block align-items-center justify-content-between mb-3">
			<div class="my-auto mb-2">
				<h3 class="page-title mb-1">Öğrenci Listesi</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="anasayfa.php">Anasayfa</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">Öğrenci Listesi</li>
					</ol>
				</nav>
			</div>
			<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
				<div class="mb-2">
					<a href="ogrenci-ekle.php" class="btn btn-primary d-flex align-items-center">
						<i class="ti ti-square-rounded-plus me-2"></i>Öğrenci Ekle
					</a>
				</div>
			</div>
		</div>

		<!-- ÖĞRENCİ ÖZET KARTI (TOPLAM / AKTİF / PASİF) -->
		<div class="row mb-4">
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card border-0">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl bg-danger-transparent me-2 p-1">
								<img src="assets/img/icons/student.svg" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<!-- TOPLAM ÖĞRENCİ DİNAMİK -->
									<h2 class="counter">
										<?= (int) $toplamOgrenci ?>
									</h2>
									<!-- İstersen dinamik bir yüzde de koyabilirsin; şimdilik boş bırakıldı -->
									<span class="badge bg-danger">
										<?= $toplamOgrenci > 0 ? '100%' : '0%' ?>
									</span>
								</div>
								<p>Toplam Öğrenci</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">
								Aktif :
								<span class="text-dark fw-semibold">
									<?= (int) $aktifSay ?>
								</span>
							</p>
							<span class="text-light">|</span>
							<p>
								Aktif Değil :
								<span class="text-dark fw-semibold">
									<?= (int) $pasifSay ?>
								</span>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ÖĞRENCİ LİSTESİ TABLOSU (DataTable) -->
		<div class="card">
			<div class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
				<h4 class="mb-3">Öğrenci Listesi</h4>
			</div>
			<div class="card-body p-0 py-3">
				<div class="custom-datatable-filter table-responsive">
					<table class="table datatable align-middle">
						<thead class="thead-light">
							<tr>
								<th class="no-sort" style="width:44px">
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox" id="select-all">
									</div>
								</th>
								<th style="min-width:120px;">Öğrenci No</th>
								<th style="min-width:120px;">TC</th>
								<th>Ad - Soyad</th>
								<th>Durum</th>
								<th>Doğum Tarihi</th>
								<th>Cinsiyet</th>
								<th class="no-sort" style="min-width:180px;">İşlemler</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($ogrenciler)): ?>
								<?php foreach ($ogrenciler as $o):
									$numara = h($o['ogrenci_no'] ?? '');
									$tc = h($o['ogrenci_tc'] ?? '');
									$adSoyad = h($o['ad_soyad'] ?? '');
									$telefon = h($o['telefon'] ?? '');
									$eposta = h($o['email'] ?? ($o['eposta'] ?? ''));
									$dogumRaw = $o['dogum_tarihi'] ?? null;
									$dogum = h(formatDateTRSafe($dogumRaw));
									$cinsiyetV = trim((string) ($o['cinsiyet'] ?? ''));
									$cinsiyet = $cinsiyetV === '' ? '-' : (
										($cinsiyetV === '1' || $cinsiyetV === 'Erkek') ? 'Erkek' :
										(($cinsiyetV === '0' || $cinsiyetV === 'Kız') ? 'Kız' : h($cinsiyetV))
									);
									$durumV = trim((string) ($o['durum'] ?? 'Belirsiz'));
									$isAktif = (mb_strtolower($durumV, 'UTF-8') === 'aktif');
									$badgeCls = $isAktif
										? 'badge badge-soft-success d-inline-flex align-items-center'
										: 'badge badge-soft-secondary d-inline-flex align-items-center';

									$avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($adSoyad ?: $numara) . '&size=64&background=DDD&color=333&bold=true';
									?>
									<tr>
										<td>
											<div class="form-check form-check-md">
												<input class="form-check-input row-check" type="checkbox"
													value="<?= $numara ?>">
											</div>
										</td>

										<td>
											<a href="ogrenci-detay.php?id=<?= $numara ?>" class="link-primary fw-semibold">
												<?= $numara ?: '-' ?>
											</a>
										</td>

										<td><?= $tc ?: '-' ?></td>

										<td>
											<div class="d-flex align-items-center">
												<span class="avatar avatar-md me-2">
													<img src="<?= h($avatarUrl) ?>" class="img-fluid rounded-circle"
														alt="avatar">
												</span>
												<div class="ms-1">
													<a href="ogrenci-detay.php?id=<?= $numara ?>" class="text-dark fw-semibold">
														<?= $adSoyad ?: '-' ?>
													</a>
													<?php if ($telefon): ?>
														<div class="small text-muted"><?= h($telefon) ?></div>
													<?php endif; ?>
												</div>
											</div>
										</td>

										<td>
											<span class="<?= $badgeCls ?>">
												<i class="ti ti-circle-filled fs-5 me-1"></i><?= h($durumV) ?>
											</span>
										</td>

										<td><?= $dogum ?></td>

										<td><?= h($cinsiyet) ?></td>

										<td>
											<div class="d-flex align-items-center">
												<a href="<?= $telefon ? 'tel:' . preg_replace('/\s+/', '', $telefon) : '#' ?>"
													class="btn btn-outline-light bg-white btn-icon d-flex align-items-center justify-content-center rounded-circle p-0 me-2"
													data-bs-toggle="tooltip" data-bs-placement="top"
													title="<?= $telefon ? h($telefon) : 'Telefon numarası yok' ?>">
													<i class="ti ti-phone<?= $telefon ? '' : ' text-muted' ?>"></i>
												</a>

												<a href="<?= $eposta ? 'mailto:' . h($eposta) : '#' ?>"
													class="btn btn-outline-light bg-white btn-icon d-flex align-items-center justify-content-center rounded-circle p-0 me-2"
													data-bs-toggle="tooltip" data-bs-placement="top"
													title="<?= $eposta ? h($eposta) : 'E-posta adresi yok' ?>">
													<i class="ti ti-mail<?= $eposta ? '' : ' text-muted' ?>"></i>
												</a>

												<div class="dropdown">
													<a href="#"
														class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
														data-bs-toggle="dropdown" aria-expanded="false">
														<i class="ti ti-dots-vertical fs-14"></i>
													</a>
													<ul class="dropdown-menu dropdown-menu-end p-2">
														<li>
															<a class="dropdown-item rounded-1"
																href="ogrenci-detay.php?id=<?= $numara ?>">
																<i class="ti ti-user-circle me-2"></i>Öğrenci Sayfası
															</a>
														</li>
														<li>
															<a class="dropdown-item rounded-1"
																href="ogrenci-duzenle.php?id=<?= $numara ?>">
																<i class="ti ti-edit-circle me-2"></i>Öğrenci Düzenle
															</a>
														</li>
														<li>
															<a class="dropdown-item rounded-1"
																href="sozlesme-olustur.php?id=<?= $numara ?>">
																<i class="ti ti-file-text me-2"></i>Sözleşme Oluştur
															</a>
														</li>
													</ul>
												</div>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="8" class="text-center text-muted">Kayıt bulunamadı.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Page Header -->
		<div class="d-md-flex d-block align-items-center justify-content-between mb-3">
			<div class="my-auto mb-2">
				<h3 class="page-title mb-1">Yönetici Paneli</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="index.html">Genel</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">Yönetici Paneli</li>
					</ol>
				</nav>
			</div>

		</div>
		<!-- /Page Header -->

		<div class="row">
			<div class="col-md-12">
				<div class="alert-message">
					<div class="alert alert-success rounded-pill d-flex align-items-center justify-content-between border-success mb-4"
						role="alert">
						<div class="d-flex align-items-center">
							<span class="me-1 avatar avatar-sm flex-shrink-0"><img
									src="assets/img/profiles/avatar-27.jpg" alt="Img"
									class="img-fluid rounded-circle"></span>
							<p>Çağatay ders ödemesi yapıldı <strong class="mx-1">“İngilizce”</strong></p>
						</div>
						<button type="button" class="btn-close p-0" data-bs-dismiss="alert" aria-label="Close"><span><i
									class="ti ti-x"></i></span></button>
					</div>
				</div>

				<!-- Dashboard Content -->

				<!-- /Dashboard Content -->

			</div>
		</div>

		<div class="row">

			<!-- Total Students -->
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card border-0">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl bg-danger-transparent me-2 p-1">
								<img src="assets/img/icons/student.svg" alt="img">
							</div>







							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<h2 class="counter">550</h2>
									<span class="badge bg-danger">1.2%</span>
								</div>
								<p>Toplam Öğrenci</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">Aktif : <span class="text-dark fw-semibold">450</span></p>
							<span class="text-light">|</span>
							<p>Aktif Değil : <span class="text-dark fw-semibold">11</span></p>
						</div>
					</div>
				</div>
			</div>
			<!-- /Total Students -->

			<!-- Total Teachers -->
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card border-0">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl me-2 bg-secondary-transparent p-1">
								<img src="assets/img/icons/teacher.svg" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<h2 class="counter">284</h2>
									<span class="badge bg-skyblue">1.2%</span>
								</div>
								<p>Toplam Öğretmen</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">Aktif : <span class="text-dark fw-semibold">254</span></p>
							<span class="text-light">|</span>
							<p>Aktif Değil : <span class="text-dark fw-semibold">30</span></p>
						</div>
					</div>
				</div>
			</div>
			<!-- /Total Teachers -->

			<!-- Total Staff -->
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card border-0">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl me-2 bg-warning-transparent p-1">
								<img src="assets/img/icons/staff.svg" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<h2 class="counter">162</h2>
									<span class="badge bg-warning">1.2%</span>
								</div>
								<p>Toplam Çalışan</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">Aktif : <span class="text-dark fw-semibold">15</span></p>
							<span class="text-light">|</span>
							<p>Aktif Değil : <span class="text-dark fw-semibold">02</span></p>
						</div>
					</div>
				</div>
			</div>
			<!-- /Total Staff -->

			<!-- Total Subjects -->
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card border-0">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl me-2 bg-success-transparent p-1">
								<img src="assets/img/icons/subject.svg" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<h2 class="counter">82</h2>
									<span class="badge bg-success">1.2%</span>
								</div>
								<p>Toplam Konu</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">Aktif : <span class="text-dark fw-semibold">81</span></p>
							<span class="text-light">|</span>
							<p>Aktif Değil : <span class="text-dark fw-semibold">01</span></p>
						</div>
					</div>
				</div>
			</div>
			<!-- /Total Subjects -->

		</div>

		<div class="row">

			<!-- Schedules -->
			<div class="col-xxl-4 col-xl-6 col-md-12 d-flex">
				<div class="card flex-fill">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div>
							<h4 class="card-title">Programlar</h4>
						</div>
						<a href="index.html#" class="link-primary fw-medium me-2" data-bs-toggle="modal"
							data-bs-target="#add_event"><i class="ti ti-square-plus me-1"></i>Yeni Ekle</a>
					</div>
					<div class="card-body">
						<div class="datepic mb-4"></div>
						<h5 class="mb-3">Yaklaşan Etkinlikler</h5>
						<div class="event-wrapper event-scroll">
							<!-- Event Item -->
							<div class="border-start border-skyblue border-3 shadow-sm p-3 mb-3">
								<div class="d-flex align-items-center mb-3 pb-3 border-bottom">
									<span class="avatar p-1 me-2 bg-teal-transparent flex-shrink-0">
										<i class="ti ti-user-edit text-info fs-20"></i>
									</span>
									<div class="flex-fill">
										<h6 class="mb-1">Veli-Öğretmen Toplantıları</h6>
										<p class="d-flex align-items-center"><i class="ti ti-calendar me-1"></i>15 July
											2024</p>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<p class="mb-0"><i class="ti ti-clock me-1"></i>09:10AM - 10:50PM</p>
									<div class="avatar-list-stacked avatar-group-sm">
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-01.jpg" class="rounded-circle"
												alt="img">
										</span>
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-07.jpg" class="rounded-circle"
												alt="img">
										</span>
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-02.jpg" class="rounded-circle"
												alt="img">
										</span>
									</div>
								</div>
							</div>
							<!-- /Event Item -->

							<!-- Event Item -->
							<div class="border-start border-info border-3 shadow-sm p-3 mb-3">
								<div class="d-flex align-items-center mb-3 pb-3 border-bottom">
									<span class="avatar p-1 me-2 bg-info-transparent flex-shrink-0">
										<i class="ti ti-user-edit fs-20"></i>
									</span>
									<div class="flex-fill">
										<h6 class="mb-1">Veli-Öğretmen Toplantısı</h6>
										<p class="d-flex align-items-center"><i class="ti ti-calendar me-1"></i>15 July
											2024</p>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<p class="mb-0"><i class="ti ti-clock me-1"></i>09:10AM - 10:50PM</p>
									<div class="avatar-list-stacked avatar-group-sm">
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-05.jpg" class="rounded-circle"
												alt="img">
										</span>
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-06.jpg" class="rounded-circle"
												alt="img">
										</span>
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-07.jpg" class="rounded-circle"
												alt="img">
										</span>
									</div>
								</div>
							</div>
							<!-- /Event Item -->

							<!-- Event Item -->
							<div class="border-start border-danger border-3 shadow-sm p-3 mb-3">
								<div class="d-flex align-items-center mb-3 pb-3 border-bottom">
									<span class="avatar p-1 me-2 bg-danger-transparent flex-shrink-0">
										<i class="ti ti-vacuum-cleaner fs-24"></i>
									</span>
									<div class="flex-fill">
										<h6 class="mb-1">Tatil Toplantısı</h6>
										<p class="d-flex align-items-center"><i class="ti ti-calendar me-1"></i>07 July
											2024 - 07 July 2024</p>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<p class="mb-0"><i class="ti ti-clock me-1"></i>09:10 AM - 10:50 PM</p>
									<div class="avatar-list-stacked avatar-group-sm">
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-11.jpg" class="rounded-circle"
												alt="img">
										</span>
										<span class="avatar border-0">
											<img src="assets/img/parents/parent-13.jpg" class="rounded-circle"
												alt="img">
										</span>
									</div>
								</div>
							</div>
							<!-- /Event Item -->

						</div>
					</div>
				</div>
			</div>
			<!-- /Schedules -->

			<!-- Attendance -->
			<div class="col-xxl-4 col-xl-6 col-md-12 d-flex flex-column">

				<div class="card">
					<div class="card-header d-flex align-items-center justify-content-between">
						<h4 class="card-title">Katılım</h4>
						<div class="dropdown">
							<a href="javascript:void(0);" class="bg-white dropdown-toggle" data-bs-toggle="dropdown"><i
									class="ti ti-calendar-due me-1"></i>Bugün
							</a>
							<ul class="dropdown-menu mt-2 p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Hafta
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Geçen Hafta
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Geçen Hafta
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="card-body">
						<div class="list-tab mb-4">
							<ul class="nav">
								<li>
									<a href="index.html#" class="active" data-bs-toggle="tab"
										data-bs-target="#students">Öğrenciler</a>
								</li>
								<li>
									<a href="index.html#" data-bs-toggle="tab"
										data-bs-target="#teachers">Öğretmenler</a>
								</li>
								<li>
									<a href="index.html#" data-bs-toggle="tab" data-bs-target="#staff">Çalışanlar</a>
								</li>
							</ul>
						</div>
						<div class="tab-content">
							<div class="tab-pane fade active show" id="students">
								<div class="row gx-3">
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>28</h5>
												<p class="fs-12">Acil Olanlar</p>
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>01</h5>
												<p class="fs-12">Yok</p>
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>01</h5>
												<p class="fs-12">Geç</p>
											</div>
										</div>
									</div>
								</div>
								<div class="text-center">
									<div id="student-chart" class="mb-4"></div>
									<a href="student-attendance.html" class="btn btn-light"><i
											class="ti ti-calendar-share me-1"></i>Hepsini Görüntüle</a>
								</div>
							</div>
							<div class="tab-pane fade" id="teachers">
								<div class="row gx-3">
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>30</h5>
												<p class="fs-12">Acil Olanlar</p>
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>03</h5>
												<p class="fs-12">Yok</p>
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>03</h5>
												<p class="fs-12">Geç</p>
											</div>
										</div>
									</div>
								</div>
								<div class="text-center">
									<div id="teacher-chart" class="mb-4"></div>
									<a href="teacher-attendance.html" class="btn btn-light"><i
											class="ti ti-calendar-share me-1"></i>Hepsini Görüntüle</a>
								</div>
							</div>
							<div class="tab-pane fade" id="staff">
								<div class="row gx-3">
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>45</h5>
												<p class="fs-12">Emergency</p>
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>01</h5>
												<p class="fs-12">Absent</p>
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="card bg-light-300 shadow-none border-0">
											<div class="card-body p-3 text-center">
												<h5>10</h5>
												<p class="fs-12">Late</p>
											</div>
										</div>
									</div>
								</div>
								<div class="text-center">
									<div id="staff-chart" class="mb-4"></div>
									<a href="staff-attendance.html" class="btn btn-light"><i
											class="ti ti-calendar-share me-1"></i>View All</a>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="row flex-fill">

					<!-- Best Performer -->
					<div class="col-sm-6 d-flex flex-column">
						<div class="bg-success-800 p-3 br-5 text-center flex-fill mb-4 pb-0  owl-height bg-01">
							<div class="owl-carousel student-slider h-100">
								<div class="item h-100">
									<div class="d-flex justify-content-between flex-column h-100">
										<div>
											<h5 class="mb-3 text-white">En İyi Performans</h5>
											<h4 class="mb-1 text-white">Naci</h4>
											<p class="text-light">Almanca Öğretmeni</p>
										</div>
										<img src="assets/img/performer/performer-01.png" alt="img">
									</div>
								</div>
								<div class="item h-100">
									<div class="d-flex justify-content-between flex-column h-100">
										<div>
											<h5 class="mb-3 text-white">En İyi Performans</h5>
											<h4 class="mb-1 text-white">Parisa</h4>
											<p class="text-light">İngilizce Öğretmeni</p>
										</div>
										<img src="assets/img/performer/performer-02.png" alt="img">
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- /Best Performer -->

					<!-- Star Students -->
					<div class="col-sm-6 d-flex flex-column">
						<div class="bg-info p-3 br-5 text-center flex-fill mb-4 pb-0 owl-height bg-02">
							<div class="owl-carousel teacher-slider h-100">
								<div class="item h-100">
									<div class="d-flex justify-content-between flex-column h-100">
										<div>
											<h5 class="mb-3 text-white">Yıldız Öğrenci</h5>
											<h4 class="mb-1 text-white">Tenesa</h4>
											<p class="text-light">İngilizce</p>
										</div>
										<img src="assets/img/performer/student-performer-01.png" alt="img">
									</div>
								</div>
								<div class="item h-100">
									<div class="d-flex justify-content-between flex-column h-100">
										<div>
											<h5 class="mb-3 text-white">Yıldız Öğrenci</h5>
											<h4 class="mb-1 text-white">Michael </h4>
											<p>Almanca</p>
										</div>
										<img src="assets/img/performer/student-performer-02.png" alt="img">
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- /Star Students -->

				</div>

			</div>
			<!-- /Attendance -->

			<div class="col-xxl-4 col-md-12 d-flex flex-column">

				<!-- Quick Links -->
				<div class="card flex-fill">
					<div class="card-header d-flex align-items-center justify-content-between">
						<h4 class="card-title">Hızlı Linkler</h4>
					</div>
					<div class="card-body pb-1">
						<div class="owl-carousel link-slider">
							<div class="item">
								<a href="class-time-table.html"
									class="d-block bg-success-transparent ronded p-2 text-center mb-3 class-hover">
									<div class="avatar avatar-lg border p-1 border-success rounded-circle mb-2">
										<span
											class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-success rounded-circle"><i
												class="ti ti-calendar"></i></span>
									</div>
									<p class="text-dark">Takvim</p>
								</a>
								<a href="fees-group.html"
									class="d-block bg-secondary-transparent ronded p-2 text-center mb-3 class-hover">
									<div class="avatar avatar-lg border p-1 border-secondary rounded-circle mb-2">
										<span
											class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-secondary rounded-circle"><i
												class="ti ti-license"></i></span>
									</div>
									<p class="text-dark">Ev Ödevleri</p>
								</a>
							</div>
							<div class="item">
								<a href="exam-results.html"
									class="d-block bg-primary-transparent ronded p-2 text-center mb-3 class-hover">
									<div class="avatar avatar-lg border p-1 border-primary rounded-circle mb-2">
										<span
											class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-primary rounded-circle"><i
												class="ti ti-hexagonal-prism"></i></span>
									</div>
									<p class="text-dark">Sınav Sonuçları</p>
								</a>
								<a href="class-home-work.html"
									class="d-block bg-danger-transparent ronded p-2 text-center mb-3 class-hover">
									<div class="avatar avatar-lg border p-1 border-danger rounded-circle mb-2">
										<span
											class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-danger rounded-circle"><i
												class="ti ti-report-money"></i></span>
									</div>
									<p class="text-dark">Ödemeler</p>
								</a>
							</div>
							<div class="item">
								<a href="student-attendance.html"
									class="d-block bg-warning-transparent ronded p-2 text-center mb-3 class-hover">
									<div class="avatar avatar-lg border p-1 border-warning rounded-circle mb-2">
										<span
											class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-warning rounded-circle"><i
												class="ti ti-calendar-share"></i></span>
									</div>
									<p class="text-dark">Katılım</p>
								</a>
								<a href="attendance-report.html"
									class="d-block bg-skyblue-transparent ronded p-2 text-center mb-3 class-hover">
									<div class="avatar avatar-lg border p-1 border-skyblue rounded-circle mb-2">
										<span
											class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-skyblue rounded-circle"><i
												class="ti ti-file-pencil"></i></span>
									</div>
									<p class="text-dark">Raporlar</p>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!-- /Quick Links -->

				<!-- Class Routine -->
				<div class="card flex-fill">
					<div class="card-header d-flex align-items-center justify-content-between">
						<h4 class="card-title">Sınıf Rutini</h4>
						<a href="index.html#" class="link-primary fw-medium" data-bs-toggle="modal"
							data-bs-target="#add_class_routine"><i class="ti ti-square-plus me-1"></i>Yeni
							Ekle</a>
					</div>
					<div class="card-body">
						<div class="d-flex align-items-center rounded border p-3 mb-3">
							<span class="avatar avatar-md flex-shrink-0 border rounded me-2">
								<img src="assets/img/teachers/teacher-01.jpg" class="rounded" alt="Profile">
							</span>
							<div class="w-100">
								<p class="mb-1">Ekim 2025</p>
								<div class="progress progress-xs  flex-grow-1 mb-1">
									<div class="progress-bar progress-bar-striped progress-bar-animated bg-primary rounded"
										role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0"
										aria-valuemax="100"></div>
								</div>
							</div>
						</div>
						<div class="d-flex align-items-center rounded border p-3 mb-3">
							<span class="avatar avatar-md flex-shrink-0 border rounded me-2">
								<img src="assets/img/teachers/teacher-02.jpg" class="rounded" alt="Profile">
							</span>
							<div class="w-100">
								<p class="mb-1">Kasım 2025</p>
								<div class="progress progress-xs  flex-grow-1 mb-1">
									<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning rounded"
										role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0"
										aria-valuemax="100"></div>
								</div>
							</div>
						</div>
						<div class="d-flex align-items-center rounded border p-3 mb-0">
							<span class="avatar avatar-md flex-shrink-0 border rounded me-2">
								<img src="assets/img/teachers/teacher-03.jpg" class="rounded" alt="Profile">
							</span>
							<div class="w-100">
								<p class="mb-1">Aralık 2025</p>
								<div class="progress progress-xs  flex-grow-1 mb-1">
									<div class="progress-bar progress-bar-striped progress-bar-animated bg-success rounded"
										role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0"
										aria-valuemax="100"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /Class Routine -->

				<!-- Class Wise Performance -->
				<div class="card flex-fill">
					<div class="card-header d-flex align-items-center justify-content-between">
						<h4 class="card-title">Performans</h4>
						<div class="dropdown">
							<a href="javascript:void(0);" class="bg-white dropdown-toggle" data-bs-toggle="dropdown"><i
									class="ti ti-school-bell  me-2"></i>Class II
							</a>
							<ul class="dropdown-menu mt-2 p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Class I
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Class II
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Class III
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Class IV
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="card-body">
						<div class="d-md-flex align-items-center justify-content-between">
							<div class="me-md-3 mb-3 mb-md-0 w-100">
								<div
									class="border border-dashed p-3 rounded d-flex align-items-center justify-content-between mb-1">
									<p class="mb-0 me-2"><i
											class="ti ti-arrow-badge-down-filled me-2 text-primary"></i>En iyiler</p>
									<h5>45</h5>
								</div>
								<div
									class="border border-dashed p-3 rounde d-flex align-items-center justify-content-between mb-1">
									<p class="mb-0 me-2"><i
											class="ti ti-arrow-badge-down-filled me-2 text-warning"></i>Ortalama
									</p>
									<h5>11</h5>
								</div>
								<div
									class="border border-dashed p-3 rounded d-flex align-items-center justify-content-between mb-0">
									<p class="mb-0 me-2"><i
											class="ti ti-arrow-badge-down-filled me-2 text-danger"></i>Ortalamanın
										altında
									</p>
									<h5>02</h5>
								</div>
							</div>
							<div id="class-chart" class="text-center text-md-left"></div>
						</div>
					</div>
				</div>
				<!-- /Class Wise Performance -->

			</div>

		</div>

		<div class="row">

			<!-- Fees Collection -->
			<div class="col-xxl-8 col-xl-6 d-flex">
				<div class="card flex-fill">
					<div class="card-header  d-flex align-items-center justify-content-between">
						<h4 class="card-title">Ödeme Tahsilleri</h4>
						<div class="dropdown">
							<a href="javascript:void(0);" class="bg-white dropdown-toggle" data-bs-toggle="dropdown"><i
									class="ti ti-calendar  me-2"></i>Son 1 Hafta
							</a>
							<ul class="dropdown-menu mt-2 p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Ay
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Yıl
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Son 12 Ay
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Son 24 Ay
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="card-body pb-0">
						<div id="fees-chart"></div>
					</div>
				</div>
			</div>
			<!-- /Fees Collection -->

			<!-- Leave Requests -->
			<div class="col-xxl-4 col-xl-6 d-flex">
				<div class="card flex-fill">
					<div class="card-header  d-flex align-items-center justify-content-between">
						<h4 class="card-title">İzin Talepleri</h4>
						<div class="dropdown">
							<a href="javascript:void(0);" class="bg-white dropdown-toggle" data-bs-toggle="dropdown"><i
									class="ti ti-calendar-due me-1"></i>Bugün
							</a>
							<ul class="dropdown-menu mt-2 p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Hafta
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Geçen Hafta
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Geçen Hafta
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="card-body">
						<div class="card mb-2">
							<div class="card-body p-3">
								<div class="d-flex align-items-center justify-content-between mb-3">
									<div class="d-flex align-items-center overflow-hidden me-2">
										<a href="javascript:void(0);" class="avatar avatar-lg flex-shrink-0 me-2">
											<img src="assets/img/profiles/avatar-14.jpg" alt="student">
										</a>
										<div class="overflow-hidden">
											<h6 class="mb-1 text-truncate"><a href="javascript:void(0);">Timoor</a><span
													class="badge badge-soft-danger ms-1">Acil</span></h6>
											<p class="text-truncate">İngilizce</p>
										</div>
									</div>
									<div class="d-flex align-items-center">
										<a href="index.html#" class="avatar avatar-xs p-0 btn btn-success me-1"><i
												class="ti ti-checks"></i></a>
										<a href="index.html#" class="avatar avatar-xs p-0 btn btn-danger"><i
												class="ti ti-x"></i></a>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between border-top pt-3">
									<p class="mb-0">Ayrılış : <span class="fw-semibold">12 kasım</span></p>
									<p>Başvur : <span class="fw-semibold">20 Kasım</span></p>
								</div>
							</div>
						</div>
						<div class="card mb-0">
							<div class="card-body p-3">
								<div class="d-flex align-items-center justify-content-between mb-3">
									<div class="d-flex align-items-center overflow-hidden me-2">
										<a href="javascript:void(0);" class="avatar avatar-lg flex-shrink-0 me-2">
											<img src="assets/img/profiles/avatar-19.jpg" alt="student">
										</a>
										<div class="overflow-hidden">
											<h6 class="mb-1 text-truncate "><a href="javascript:void(0);">Ali</a><span
													class="badge badge-soft-warning ms-1">Gündelik</span></h6>
											<p class="text-truncate">Muhasebe</p>
										</div>
									</div>
									<div class="d-flex align-items-center">
										<a href="index.html#" class="avatar avatar-xs p-0 btn btn-success me-1"><i
												class="ti ti-checks"></i></a>
										<a href="index.html#" class="avatar avatar-xs p-0 btn btn-danger"><i
												class="ti ti-x"></i></a>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between border-top pt-3">
									<p class="mb-0">Başlangıç : <span class="fw-semibold">13 kasım</span></p>
									<p>Bitiş : <span class="fw-semibold">15 Kasım</span></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Leave Requests -->

		</div>

		<div class="row">

			<!-- Links -->
			<div class="col-xl-3 col-md-6 d-flex">
				<a href="student-attendance.html"
					class="card bg-warning-transparent border border-5 border-white animate-card flex-fill">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<span class="avatar avatar-lg bg-warning rounded flex-shrink-0 me-2"><i
										class="ti ti-calendar-share fs-24"></i></span>
								<div class="overflow-hidden">
									<h6 class="fw-semibold text-default">Katılımı Görüntüle</h6>
								</div>
							</div>
							<span
								class="btn btn-white warning-btn-hover avatar avatar-sm p-0 flex-shrink-0 rounded-circle"><i
									class="ti ti-chevron-right fs-14"></i></span>
						</div>
					</div>
				</a>
			</div>
			<!-- /Links -->

			<!-- Links -->
			<div class="col-xl-3 col-md-6 d-flex">
				<a href="events.html"
					class="card bg-success-transparent border border-5 border-white animate-card flex-fill ">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<span class="avatar avatar-lg bg-success rounded flex-shrink-0 me-2"><i
										class="ti ti-speakerphone fs-24"></i></span>
								<div class="overflow-hidden">
									<h6 class="fw-semibold text-default">Yeni Etkinlikler</h6>
								</div>
							</div>
							<span
								class="btn btn-white success-btn-hover avatar avatar-sm p-0 flex-shrink-0 rounded-circle"><i
									class="ti ti-chevron-right fs-14"></i></span>
						</div>
					</div>
				</a>
			</div>
			<!-- /Links -->

			<!-- Links -->
			<div class="col-xl-3 col-md-6 d-flex">
				<a href="membership-plans.html"
					class="card bg-danger-transparent border border-5 border-white animate-card flex-fill">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<span class="avatar avatar-lg bg-danger rounded flex-shrink-0 me-2"><i
										class="ti ti-sphere fs-24"></i></span>
								<div class="overflow-hidden">
									<h6 class="fw-semibold text-default">Üyelik Planları</h6>
								</div>
							</div>
							<span
								class="btn btn-white avatar avatar-sm p-0 flex-shrink-0 rounded-circle danger-btn-hover"><i
									class="ti ti-chevron-right fs-14"></i></span>
						</div>
					</div>
				</a>
			</div>
			<!-- /Links -->

			<!-- Links -->
			<div class="col-xl-3 col-md-6 d-flex">
				<a href="student-attendance.html"
					class="card bg-secondary-transparent border border-5 border-white animate-card flex-fill">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div class="d-flex align-items-center">
								<span class="avatar avatar-lg bg-secondary rounded flex-shrink-0 me-2"><i
										class="ti ti-moneybag fs-24"></i></span>
								<div class="overflow-hidden">
									<h6 class="fw-semibold text-default">Finans ve Muhasebe</h6>
								</div>
							</div>
							<span
								class="btn btn-white secondary-btn-hover avatar avatar-sm p-0 flex-shrink-0 rounded-circle"><i
									class="ti ti-chevron-right fs-14"></i></span>
						</div>
					</div>
				</a>
			</div>
			<!-- /Links -->

		</div>
		<div class="row">

			<!-- Total Earnings -->
			<div class="col-xxl-4 col-xl-6 d-flex flex-column">
				<div class="card flex-fill">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<h6 class="mb-1">Toplam Kazanç</h6>
								<h2>₺164,522,24</h2>
							</div>
							<span class="avatar avatar-lg bg-primary">
								<i class="ti ti-user-dollar"></i>
							</span>
						</div>
					</div>
					<div id="total-earning"></div>
				</div>
				<div class="card flex-fill">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<h6 class="mb-1">Toplam Giderler</h6>
								<h2>₺60,522,24</h2>
							</div>
							<span class="avatar avatar-lg bg-danger">
								<i class="ti ti-user-dollar"></i>
							</span>
						</div>
					</div>
					<div id="total-expenses"></div>
				</div>
			</div>
			<!-- /Total Earnings -->

			<!-- Notice Board -->
			<div class="col-xxl-5 col-xl-12 order-3 order-xxl-2 d-flex">
				<div class="card flex-fill">
					<div class="card-header  d-flex align-items-center justify-content-between">
						<h4 class="card-title">Duyuru Panosu</h4>
						<a href="notice-board.html" class="fw-medium">Tümünü Görüntüle</a>
					</div>
					<div class="card-body">
						<div class="notice-widget">
							<div class="d-sm-flex align-items-center justify-content-between mb-4">
								<div class="d-flex align-items-center overflow-hidden me-2 mb-2 mb-sm-0">
									<span
										class="bg-primary-transparent avatar avatar-md me-2 rounded-circle flex-shrink-0">
										<i class="ti ti-books fs-16"></i>
									</span>
									<div class="overflow-hidden">
										<h6 class="text-truncate mb-1">Yeni Ders Planları</h6>
										<p><i class="ti ti-calendar me-2"></i>Eklendi : 11 kasım 2025</p>
									</div>
								</div>
								<span class="badge bg-light text-dark"><i class="ti ti-clck me-1"></i>20
									Days</span>
							</div>
							<div class="d-sm-flex align-items-center justify-content-between mb-4">
								<div class="d-flex align-items-center overflow-hidden me-2 mb-2 mb-sm-0">
									<span
										class="bg-success-transparent avatar avatar-md me-2 rounded-circle flex-shrink-0">
										<i class="ti ti-note fs-16"></i>
									</span>
									<div class="overflow-hidden">
										<h6 class="text-truncate mb-1">İspanyolca..!!
										</h6>
										<p><i class="ti ti-calendar me-2"></i>Eklendi : 01 kasım 2025</p>
									</div>
								</div>
								<span class="badge bg-light text-dark"><i class="ti ti-clck me-1"></i>15
									Gün</span>
							</div>
							<div class="d-sm-flex align-items-center justify-content-between mb-4">
								<div class="d-flex align-items-center overflow-hidden me-2 mb-2 mb-sm-0">
									<span
										class="bg-danger-transparent avatar avatar-md me-2 rounded-circle flex-shrink-0">
										<i class="ti ti-bell-check fs-16"></i>
									</span>
									<div class="overflow-hidden">
										<h6 class="text-truncate mb-1">Sınava Hazırlık Bildirimi!</h6>
										<p><i class="ti ti-calendar me-2"></i>Eklendi : 05 kasım 2025</p>
									</div>
								</div>
								<span class="badge bg-light text-dark"><i class="ti ti-clck me-1"></i>12
									Gün</span>
							</div>
							<div class="d-sm-flex align-items-center justify-content-between mb-4">
								<div class="d-flex align-items-center overflow-hidden me-2 mb-2 mb-sm-0">
									<span
										class="bg-skyblue-transparent avatar avatar-md me-2 rounded-circle flex-shrink-0">
										<i class="ti ti-notes fs-16"></i>
									</span>
									<div class="overflow-hidden">
										<h6 class="text-truncate mb-1">Online Ders Hazırlıkları</h6>
										<p><i class="ti ti-calendar me-2"></i>Eklendi : 04 kasım 2025</p>
									</div>
								</div>
								<span class="badge bg-light text-dark"><i class="ti ti-clck me-1"></i>02
									Gün</span>
							</div>
							<div class="d-sm-flex align-items-center justify-content-between mb-0">
								<div class="d-flex align-items-center overflow-hidden me-2 mb-2 mb-sm-0">
									<span
										class="bg-warning-transparent avatar avatar-md me-2 rounded-circle flex-shrink-0">
										<i class="ti ti-package fs-16"></i>
									</span>
									<div class="overflow-hidden">
										<h6 class="text-truncate mb-1">Sınav Takvimi Açıklaması</h6>
										<p><i class="ti ti-calendar me-2"></i>Eklendi : 07 Kasım 2025</p>
									</div>
								</div>
								<span class="badge bg-light text-dark"><i class="ti ti-clck me-1"></i>6
									Gün</span>
							</div>
						</div>
					</div>
				</div>

			</div>
			<!-- /Notice Board -->

			<!-- Fees Collection -->
			<div class="col-xxl-3 col-xl-6 order-2 order-xxl-3 d-flex flex-column">
				<div class="card flex-fill mb-2">
					<div class="card-body">
						<p class="mb-2">Toplam Tahsil Edilen Ücretler</p>
						<div class="d-flex align-items-end justify-content-between">
							<h4>₺25,000,02</h4>
							<span class="badge badge-soft-success"><i class="ti ti-chart-line me-1"></i>1.2%</span>
						</div>
					</div>
				</div>
				<div class="card flex-fill mb-2">
					<div class="card-body">
						<p class="mb-2">Bugüne kadar toplanan extra harcamalar</p>
						<div class="d-flex align-items-end justify-content-between">
							<h4>₺4,56,64</h4>
							<span class="badge badge-soft-danger"><i class="ti ti-chart-line me-1"></i>1.2%</span>
						</div>
					</div>
				</div>
				<div class="card flex-fill mb-2">
					<div class="card-body">
						<p class="mb-2">Öğrenci Ödeme Yapmadı</p>
						<div class="d-flex align-items-end justify-content-between">
							<h4>₺545</h4>
							<span class="badge badge-soft-info"><i class="ti ti-chart-line me-1"></i>1.2%</span>
						</div>
					</div>
				</div>
				<div class="card flex-fill mb-4">
					<div class="card-body">
						<p class="mb-2">Toplam Ödenmemiş</p>
						<div class="d-flex align-items-end justify-content-between">
							<h4>₺4,56,64</h4>
							<span class="badge badge-soft-danger"><i class="ti ti-chart-line me-1"></i>1.2%</span>
						</div>
					</div>
				</div>
			</div>
			<!-- /Fees Collection -->

		</div>

		<div class="row">

			<!-- Top Subjects -->
			<div class="col-xxl-4 col-xl-6 d-flex">
				<div class="card flex-fill">
					<div class="card-header  d-flex align-items-center justify-content-between">
						<h4 class="card-title">En Popüler Konular</h4>
						<div class="dropdown">
							<a href="javascript:void(0);" class="bg-white dropdown-toggle" data-bs-toggle="dropdown"><i
									class="ti ti-school-bell  me-2"></i>Sınıf
							</a>
							<ul class="dropdown-menu mt-2 p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Sınıf 2
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Sınıf 3
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Sınıf 4
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Sınıf 5
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="card-body">
						<div class="alert alert-success d-flex align-items-center mb-24" role="alert">
							<i class="ti ti-info-square-rounded me-2 fs-14"></i>
							<div class="fs-14">
								Bu sonuçlar, ilgili sınıfın müfredatının tamamlanmasından elde edilmiştir.
							</div>
						</div>
						<ul class="list-group">
							<li class="list-group-item">
								<div class="row align-items-center">
									<div class="col-sm-4">
										<p class="text-dark">İngilizce</p>
									</div>
									<div class="col-sm-8">
										<div class="progress progress-xs flex-grow-1">
											<div class="progress-bar bg-primary rounded" role="progressbar"
												style="width: 20%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row align-items-center">
									<div class="col-sm-4">
										<p class="text-dark">Almanca</p>
									</div>
									<div class="col-sm-8">
										<div class="progress progress-xs flex-grow-1">
											<div class="progress-bar bg-secondary rounded" role="progressbar"
												style="width: 30%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row align-items-center">
									<div class="col-sm-4">
										<p class="text-dark">Türkçe</p>
									</div>
									<div class="col-sm-8">
										<div class="progress progress-xs flex-grow-1">
											<div class="progress-bar bg-info rounded" role="progressbar"
												style="width: 40%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row align-items-center">
									<div class="col-sm-4">
										<p class="text-dark">Fransızca</p>
									</div>
									<div class="col-sm-8">
										<div class="progress progress-xs flex-grow-1">
											<div class="progress-bar bg-success rounded" role="progressbar"
												style="width: 50%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row align-items-center">
									<div class="col-sm-4">
										<p class="text-dark">Flemençe</p>
									</div>
									<div class="col-sm-8">
										<div class="progress progress-xs flex-grow-1">
											<div class="progress-bar bg-warning rounded" role="progressbar"
												style="width: 70%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row align-items-center">
									<div class="col-sm-4">
										<p class="text-dark">Korece</p>
									</div>
									<div class="col-sm-8">
										<div class="progress progress-xs flex-grow-1">
											<div class="progress-bar bg-danger rounded" role="progressbar"
												style="width: 80%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</div>
								</div>
							</li>
							<li class="list-group-item">
								<div class="row align-items-center">
									<div class="col-sm-4">
										<p class="text-dark">Japonca</p>
									</div>
									<div class="col-sm-8">
										<div class="progress progress-xs flex-grow-1">
											<div class="progress-bar bg-primary rounded" role="progressbar"
												style="width: 85%;" aria-valuenow="30" aria-valuemin="0"
												aria-valuemax="100"></div>
										</div>
									</div>
								</div>
							</li>
						</ul>
					</div>
				</div>

			</div>
			<!-- /Top Subjects -->

			<!-- Student Activity -->
			<div class="col-xxl-4 col-xl-6 d-flex">
				<div class="card flex-fill">
					<div class="card-header  d-flex align-items-center justify-content-between">
						<h4 class="card-title">Öğrenci Etkinliği</h4>
						<div class="dropdown">
							<a href="javascript:void(0);" class="bg-white dropdown-toggle" data-bs-toggle="dropdown"><i
									class="ti ti-calendar me-2"></i>Bu Hafta
							</a>
							<ul class="dropdown-menu mt-2 p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Ay
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Yıl
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Geçen Hafta
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="card-body">
						<div class="d-flex align-items-center overflow-hidden p-3 mb-3 border rounded">
							<span class="avatar avatar-lg flex-shrink-0 rounded me-2">
								<img src="assets/img/students/student-09.jpg" alt="student">
							</span>
							<div class="overflow-hidden">
								<h6 class="mb-1 text-truncate">“İngilizce” dalında 1.lik</h6>
								<p>Bu etkinlik Okulumuzda gerçekleşti.</p>
							</div>
						</div>
						<div class="d-flex align-items-center overflow-hidden p-3 mb-3 border rounded">
							<span class="avatar avatar-lg flex-shrink-0 rounded me-2">
								<img src="assets/img/students/student-12.jpg" alt="student">
							</span>
							<div class="overflow-hidden">
								<h6 class="mb-1 text-truncate">Yurtdışı danışmanlı seminerine katıldı</h6>
								<p>Abdulkadir yönetilik seminerine katıldı.</p>
							</div>
						</div>
						<div class="d-flex align-items-center overflow-hidden p-3 mb-3 border rounded">
							<span class="avatar avatar-lg flex-shrink-0 rounded me-2">
								<img src="assets/img/students/student-11.jpg" alt="student">
							</span>
							<div class="overflow-hidden">
								<h6 class="mb-1 text-truncate">Okullar arası birincilik</h6>
								<p>Başka okulda gerçekleşti</p>
							</div>
						</div>
						<div class="d-flex align-items-center overflow-hidden p-3 mb-0 border rounded">
							<span class="avatar avatar-lg flex-shrink-0 rounded me-2">
								<img src="assets/img/students/student-10.jpg" alt="student">
							</span>
							<div class="overflow-hidden">
								<h6 class="mb-1 text-truncate">Uluslararası konferans</h6>
								<p class="text-truncate">Tüm personeller katıldı</p>
							</div>
						</div>
					</div>
				</div>

			</div>
			<!-- /Student Activity -->

			<!-- Todo -->
			<div class="col-xxl-4 col-xl-12 d-flex">
				<div class="card flex-fill">
					<div class="card-header  d-flex align-items-center justify-content-between">
						<h4 class="card-title">Yapılacaklar</h4>
						<div class="dropdown">
							<a href="javascript:void(0);" class="bg-white dropdown-toggle" data-bs-toggle="dropdown"><i
									class="ti ti-calendar me-2"></i>Bugün
							</a>
							<ul class="dropdown-menu mt-2 p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Ay
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Bu Yıl
									</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">
										Geçen Hafta
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="card-body">
						<ul class="list-group list-group-flush todo-list">
							<li class="list-group-item py-3 px-0 pt-0">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center overflow-hidden me-2 todo-strike-content">
										<div class="form-check form-check-md me-2">
											<input class="form-check-input" type="checkbox" checked>
										</div>
										<div class="overflow-hidden">
											<h6 class="mb-1 text-truncate">Öğrencilere Hatırlatma Gönder</h6>
											<p>13:00'te</p>
										</div>
									</div>
									<span class="badge badge-soft-success mt-2 mt-sm-0">Tamamlandı</span>
								</div>
							</li>
							<li class="list-group-item py-3 px-0">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center overflow-hidden me-2">
										<div class="form-check form-check-md me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="overflow-hidden">
											<h6 class="mb-1 text-truncate">Yeni personele rutin oluşturun</h6>
											<p>10:00</p>
										</div>
									</div>
									<span class="badge badge-soft-skyblue mt-2 mt-sm-0">Devam ediyor</span>
								</div>
							</li>
							<li class="list-group-item py-3 px-0">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center overflow-hidden me-2">
										<div class="form-check form-check-md me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="overflow-hidden">
											<h6 class="mb-1 text-truncate">Öğrencilere Ekstra Ders Bilgileri</h6>
											<p>14:40 </p>
										</div>
									</div>
									<span class="badge badge-soft-warning mt-2 mt-sm-0">Henüz Başlamadı</span>
								</div>
							</li>
							<li class="list-group-item py-3 px-0">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center overflow-hidden me-2">
										<div class="form-check form-check-md me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="overflow-hidden">
											<h6 class="mb-1 text-truncate">Yaklaşan Aylık Ödeme Bilgileri</h6>
											<p>10:00 </p>
										</div>
									</div>
									<span class="badge badge-soft-warning mt-2 mt-sm-0">Henüz Başlamadı</span>
								</div>
							</li>
							<li class="list-group-item py-3 px-0 pb-0">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center overflow-hidden me-2">
										<div class="form-check form-check-md me-2">
											<input class="form-check-input" type="checkbox">
										</div>
										<div class="overflow-hidden">
											<h6 class="mb-1 text-truncate">İngilizce Üzerine Deneme</h6>
											<p>11:00 </p>
										</div>
									</div>
									<span class="badge badge-soft-warning mt-2 mt-sm-0">Henüz Başlamadı</span>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<!-- /Todo -->

		</div>

	</div>
</div>
<!-- /Page Wrapper -->

<!-- Düzenle Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<form method="post" class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Görüşme Düzenle</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" name="id" id="edit-id">
				<div class="mb-3">
					<label class="form-label">Ad Soyad</label>
					<input type="text" class="form-control" name="ad_soyad" id="edit-ad" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Dil</label>
					<select class="form-select" name="dil" id="edit-dil" required>
						<?php
						foreach (['Türkçe', 'İngilizce', 'Almanca', 'Fransızca', 'Arapça', 'Farsça', 'İspanyolca', 'İtalyanca', 'Korece', 'Japonca', 'Flemenkçe'] as $d) {
							echo '<option>' . htmlspecialchars($d) . '</option>';
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label">Açıklama</label>
					<input type="text" class="form-control" name="aciklama" id="edit-aciklama">
				</div>
				<div class="mb-3">
					<label class="form-label">Sonuç</label>
					<select class="form-select" name="sonuc" id="edit-sonuc" required>
						<?php
						foreach (['İletişime Geçildi', 'Kaydedildi', 'Randevu Verildi', 'Kayıt Oldu', 'Olumsuz', 'Beklemede'] as $s) {
							echo '<option>' . htmlspecialchars($s) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-light" type="button" data-bs-dismiss="modal">Kapat</button>
				<button class="btn btn-primary" type="submit" name="gorusme_guncelle">Kaydet</button>
			</div>
		</form>
	</div>
</div>

<div class="modal fade" id="add_class_routine">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-wrapper">
				<div class="modal-header">
					<h4 class="modal-title">Sınıf Rutini Oluştur</h4>
					<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
						<i class="ti ti-x"></i>
					</button>
				</div>
				<form action="index.html">
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<div class="mb-3">
									<label class="form-label">Teacher</label>
									<select class="select">
										<option>Select</option>
										<option>Erickson</option>
										<option>Mori</option>
										<option>Joseph</option>
										<option>James</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="form-label">Class</label>
									<select class="select">
										<option>Select</option>
										<option>I</option>
										<option>II</option>
										<option>III</option>
										<option>IV</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="form-label">Section</label>
									<select class="select">
										<option>Select</option>
										<option>A</option>
										<option>B</option>
										<option>C</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="form-label">Day</label>
									<select class="select">
										<option>Select</option>
										<option>Monday</option>
										<option>Tuesday</option>
										<option>Wedneshday</option>
										<option>Thursday</option>
										<option>Friday</option>
									</select>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div class="mb-3">
											<label class="form-label">Start Time</label>
											<div class="date-pic">
												<input type="text" class="form-control timepicker" placeholder="Choose">
												<span class="cal-icon"><i class="ti ti-clock"></i></span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label class="form-label">End Time</label>
											<div class="date-pic">
												<input type="text" class="form-control timepicker" placeholder="Choose">
												<span class="cal-icon"><i class="ti ti-clock"></i></span>
											</div>
										</div>
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">Class Room</label>
									<select class="select">
										<option>Select</option>
										<option>101</option>
										<option>102</option>
										<option>103</option>
										<option>104</option>
										<option>105</option>
									</select>
								</div>
								<div class="modal-satus-toggle d-flex align-items-center justify-content-between">
									<div class="status-title">
										<h5>Status</h5>
										<p>Change the Status by toggle </p>
									</div>
									<div class="status-toggle modal-status">
										<input type="checkbox" id="user1" class="check">
										<label for="user1" class="checktoggle"> </label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<a href="index.html#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
						<button type="submit" class="btn btn-primary">Add Class Routine</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Add Class Routine -->

<!-- Add Event -->
<div class="modal fade" id="add_event">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">New Event</h4>
				<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
					<i class="ti ti-x"></i>
				</button>
			</div>
			<form action="index.html">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div>
								<label class="form-label">Event For</label>
								<div class="d-flex align-items-center flex-wrap">
									<div class="form-check me-3 mb-3">
										<input class="form-check-input" type="radio" name="event" id="all" checked="">
										<label class="form-check-label" for="all">
											All
										</label>
									</div>
									<div class="form-check me-3 mb-3">
										<input class="form-check-input" type="radio" name="event" id="students">
										<label class="form-check-label" for="students">
											Students
										</label>
									</div>
									<div class="form-check me-3 mb-3">
										<input class="form-check-input" type="radio" name="event" id="staffs">
										<label class="form-check-label" for="staffs">
											Staffs
										</label>
									</div>
								</div>
							</div>
							<div class="all-content" id="all-student">
								<div class="mb-3">
									<label class="form-label">Classes</label>
									<select class="select">
										<option>All Classes</option>
										<option>I</option>
										<option>II</option>
										<option>III</option>
										<option>IV</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="form-label">Sections</label>
									<select class="select">
										<option>All Sections</option>
										<option>A</option>
										<option>B</option>
									</select>
								</div>
							</div>
							<div class="all-content" id="all-staffs">
								<div class="mb-3">
									<div class="bg-light-500 p-3 pb-2 rounded">
										<label class="form-label">Role</label>
										<div class="row">
											<div class="col-md-6">
												<div class="form-check form-check-sm mb-2">
													<input class="form-check-input" type="checkbox">Admin
												</div>
												<div class="form-check form-check-sm mb-2">
													<input class="form-check-input" type="checkbox" checked>Teacher
												</div>
												<div class="form-check form-check-sm mb-2">
													<input class="form-check-input" type="checkbox">Driver
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-check form-check-sm mb-2">
													<input class="form-check-input" type="checkbox">Accountant
												</div>
												<div class="form-check form-check-sm mb-2">
													<input class="form-check-input" type="checkbox">Librarian
												</div>
												<div class="form-check form-check-sm mb-2">
													<input class="form-check-input" type="checkbox">Receptionist
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="mb-3">
									<label class="form-label">All Teachers</label>
									<select class="select">
										<option>Select</option>
										<option>I</option>
										<option>II</option>
										<option>III</option>
										<option>IV</option>
									</select>
								</div>
							</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Event Title</label>
							<input type="text" class="form-control" placeholder="Enter Title">
						</div>
						<div class="mb-3">
							<label class="form-label">Event Category</label>
							<select class="select">
								<option>Select</option>
								<option>Celebration</option>
								<option>Training</option>
								<option>Meeting</option>
								<option>Holidays</option>
							</select>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Start Date</label>
								<div class="date-pic">
									<input type="text" class="form-control datetimepicker" placeholder="15 May 2024">
									<span class="cal-icon"><i class="ti ti-calendar"></i></span>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">End Date</label>
								<div class="date-pic">
									<input type="text" class="form-control datetimepicker" placeholder="21 May 2024">
									<span class="cal-icon"><i class="ti ti-calendar"></i></span>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">Start Time</label>
								<div class="date-pic">
									<input type="text" class="form-control timepicker" placeholder="09:10 AM">
									<span class="cal-icon"><i class="ti ti-clock"></i></span>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label class="form-label">End Time</label>
								<div class="date-pic">
									<input type="text" class="form-control timepicker" placeholder="12:50 PM">
									<span class="cal-icon"><i class="ti ti-clock"></i></span>
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div class="mb-3">
								<div class="bg-light p-3 pb-2 rounded">
									<div class="mb-3">
										<label class="form-label">Attachment</label>
										<p>Upload size of 4MB, Accepted Format PDF</p>
									</div>
									<div class="d-flex align-items-center flex-wrap">
										<div class="btn btn-primary drag-upload-btn mb-2 me-2">
											<i class="ti ti-file-upload me-1"></i>Upload
											<input type="file" class="form-control image_sign" multiple="">
										</div>
										<p class="mb-2">Fees_Structure.pdf</p>
									</div>
								</div>
							</div>
							<div class="mb-0">
								<label class="form-label">Message</label>
								<textarea class="form-control"
									rows="4">Meeting with Staffs on the Quality Improvement s and completion of syllabus before the August,  enhance the students health issue</textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="index.html#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
					<button type="submit" class="btn btn-primary">Save Changes</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- JS -->


<script>
	// Düzenle modalını doldur
	document.addEventListener('click', function (e) {
		const btn = e.target.closest('.btn-edit');
		if (!btn) return;
		document.getElementById('edit-id').value = btn.dataset.id || '';
		document.getElementById('edit-ad').value = btn.dataset.ad || '';
		document.getElementById('edit-dil').value = btn.dataset.dil || '';
		document.getElementById('edit-aciklama').value = btn.dataset.aciklama || '';
		document.getElementById('edit-sonuc').value = btn.dataset.sonuc || '';
	}, false);
</script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Bootstrap tooltips
		if (window.bootstrap) {
			document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
				new bootstrap.Tooltip(el);
			});
		}

		// Select-all
		const selectAll = document.getElementById('select-all');
		const checks = document.querySelectorAll('.row-check');
		if (selectAll) {
			selectAll.addEventListener('change', function () {
				checks.forEach(ch => ch.checked = selectAll.checked);
			});
		}

		// DataTables
		if (window.jQuery && jQuery.fn.DataTable) {
			jQuery('.datatable').DataTable({
				pageLength: 25,
				order: [[1, 'desc']], // Öğrenci No
				columnDefs: [
					{ targets: 'no-sort', orderable: false }
				],
				language: {
					url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
				}
			});
		}
	});
</script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Bootstrap tooltips
		if (window.bootstrap) {
			document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
				new bootstrap.Tooltip(el);
			});
		}

		// Select-all
		const selectAll = document.getElementById('select-all');
		const checks = document.querySelectorAll('.row-check');
		if (selectAll) {
			selectAll.addEventListener('change', function () {
				checks.forEach(ch => ch.checked = selectAll.checked);
			});
		}

		// DataTables
		if (window.jQuery && jQuery.fn.DataTable) {
			jQuery('.datatable').DataTable({
				pageLength: 25,
				order: [[1, 'desc']], // Öğrenci No
				columnDefs: [
					{ targets: 'no-sort', orderable: false }
				],
				language: {
					url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
				}
			});
		}
	});
</script>

<script src="assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<script src="assets/js/moment.js" type="text/javascript"></script>
<script src="assets/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<script src="assets/js/feather.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="assets/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
<script src="assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="assets/js/script.js" type="text/javascript"></script>
<script src="assets/plugins/apexchart/apexcharts.min.js" type="text/javascript"></script>
<script src="assets/plugins/apexchart/chart-data.js" type="text/javascript"></script>
<script src="assets/plugins/owlcarousel/owl.carousel.min.js" type="text/javascript"></script>
<script src="assets/plugins/countup/jquery.counterup.min.js" type="text/javascript"></script>
<script src="assets/plugins/countup/jquery.waypoints.min.js" type="text/javascript"></script>


</body>

</html>