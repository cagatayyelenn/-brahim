<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

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

if (!empty($ogrenciler)) {
	$aktifogr = 0;
	$pasifogr = 0;
	$toplamogr = 0;

	foreach ($ogrenciler as $key => $value) {
		if ($value['durum'] == 'Aktif') {
			$aktifogr++;
		}
		if ($value['durum'] == 'Pasif') {
			$pasifogr++;
		}
		$toplamogr++;
	}
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

		<div class="row">
			<div class="col-md-12">
				<div class="alert-message">
					<div class="alert alert-success rounded-pill d-flex align-items-center justify-content-between border-success mb-4"
						role="alert">
						<div class="d-flex align-items-center">
							<span class="me-1 avatar avatar-sm flex-shrink-0">
								<img src="assets/img/profiles/avatar-27.jpg" alt="Img" class="img-fluid rounded-circle">
							</span>
							<p>Çağatay ders ödemesi yapıldı <strong class="mx-1">“İngilizce”</strong></p>
						</div>
						<button type="button" class="btn-close p-0" data-bs-dismiss="alert" aria-label="Close">
							<span><i class="ti ti-x"></i></span>
						</button>
					</div>
				</div>
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
									<h2 class="counter"><?= $toplamogr ?></h2>

								</div>
								<p>Toplam Öğrenci</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">Aktif : <span class="text-dark fw-semibold"><?= $aktifogr ?></span></p>
							<span class="text-light">|</span>
							<p>Pasif : <span class="text-dark fw-semibold"><?= $pasifogr ?></span></p>
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



		</div>

		<div class="row">

			<!-- Fees Collection -->
			<div class="col-xxl-6 col-xl-6 d-flex">
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
			<div class="col-xxl-6 col-xl-6 d-flex">
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