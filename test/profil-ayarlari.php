<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<meta name="description" content="Preskool - Bootstrap Admin Template">
	<meta name="keywords" content="admin, estimates, bootstrap, business, html5, responsive, Projects">
	<meta name="author" content="Dreams technologies - Bootstrap Admin Template">
	<meta name="robots" content="noindex, nofollow">
	<title>Preskool Admin Template</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">

	<!-- Theme Script js -->
	<script src="assets/js/theme-script.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">

	<!-- Feather CSS -->
	<link rel="stylesheet" href="assets/plugins/icons/feather/feather.css">

	<!-- Tabler Icon CSS -->
	<link rel="stylesheet" href="assets/plugins/tabler-icons/tabler-icons.css">

	<!-- Daterangepikcer CSS -->
	<link rel="stylesheet" href="assets/plugins/daterangepicker/daterangepicker.css">

	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
	<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

	<!-- Datatable CSS -->
	<link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">

	<!-- Main CSS -->
	<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

	<!-- Main Wrapper -->
	<div class="main-wrapper">

		<!-- Header -->
		<div class="header">

			<!-- Logo -->
			<div class="header-left active">
				<a href="index.html" class="logo logo-normal">
					<img src="assets/img/logo.svg" alt="Logo">
				</a>
				<a href="index.html" class="logo-small">
					<img src="assets/img/logo-small.svg" alt="Logo">
				</a>
				<a href="index.html" class="dark-logo">
					<img src="assets/img/logo-dark.svg" alt="Logo">
				</a>
				<a id="toggle_btn" href="javascript:void(0);">
					<i class="ti ti-menu-deep"></i>
				</a>
			</div>
			<!-- /Logo -->

			<a id="mobile_btn" class="mobile_btn" href="profile-settings.html#sidebar">
				<span class="bar-icon">
					<span></span>
					<span></span>
					<span></span>
				</span>
			</a>

			<div class="header-user">
				<div class="nav user-menu">

					<!-- Search -->
					<div class="nav-item nav-search-inputs me-auto">
						<div class="top-nav-search">
							<a href="javascript:void(0);" class="responsive-search">
								<i class="fa fa-search"></i>
							</a>
							<form action="#" class="dropdown">
								<div class="searchinputs" id="dropdownMenuClickable">
									<input type="text" placeholder="Search">
									<div class="search-addon">
										<button type="submit"><i class="ti ti-command"></i></button>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- /Search -->

					<div class="d-flex align-items-center">
						<div class="dropdown me-2">
							<a href="profile-settings.html#" class="btn btn-outline-light fw-normal bg-white d-flex align-items-center p-2"
								data-bs-toggle="dropdown" aria-expanded="false">
								<i class="ti ti-calendar-due me-1"></i>Academic Year : 2024 / 2025
							</a>
							<div class="dropdown-menu dropdown-menu-right">
								<a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
									Academic Year : 2023 / 2024
								</a>
								<a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
									Academic Year : 2022 / 2023
								</a>
								<a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
									Academic Year : 2021 / 2022
								</a>
							</div>
						</div>
						<div class="pe-1 ms-1">
							<div class="dropdown">
								<a href="profile-settings.html#"
									class="btn btn-outline-light bg-white btn-icon d-flex align-items-center me-1 p-2"
									data-bs-toggle="dropdown" aria-expanded="false">
									<img src="assets/img/flags/us.png" alt="Language" class="img-fluid rounded-pill">
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<a href="javascript:void(0);"
										class="dropdown-item active d-flex align-items-center">
										<img class="me-2 rounded-pill" src="assets/img/flags/us.png" alt="Img"
											height="22" width="22"> English
									</a>
									<a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
										<img class="me-2 rounded-pill" src="assets/img/flags/fr.png" alt="Img"
											height="22" width="22"> French
									</a>
									<a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
										<img class="me-2 rounded-pill" src="assets/img/flags/es.png" alt="Img"
											height="22" width="22"> Spanish
									</a>
									<a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
										<img class="me-2 rounded-pill" src="assets/img/flags/de.png" alt="Img"
											height="22" width="22"> German
									</a>
								</div>
							</div>
						</div>
						<div class="pe-1">
							<div class="dropdown">
								<a href="profile-settings.html#" class="btn btn-outline-light bg-white btn-icon me-1"
									data-bs-toggle="dropdown" aria-expanded="false">
									<i class="ti ti-square-rounded-plus"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-right border shadow-sm dropdown-md">
									<div class="p-3 border-bottom">
										<h5>Add New</h5>
									</div>
									<div class="p-3 pb-0">
										<div class="row gx-2">
											<div class="col-6">
												<a href="add-student.html"
													class="d-block bg-primary-transparent ronded p-2 text-center mb-3 class-hover">
													<div class="avatar avatar-lg mb-2">
														<span
															class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-primary rounded-circle"><i
																class="ti ti-school"></i></span>
													</div>
													<p class="text-dark">Students</p>
												</a>
											</div>
											<div class="col-6">
												<a href="add-teacher.html"
													class="d-block bg-success-transparent ronded p-2 text-center mb-3 class-hover">
													<div class="avatar avatar-lg mb-2">
														<span
															class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-success rounded-circle"><i
																class="ti ti-users"></i></span>
													</div>
													<p class="text-dark">Teachers</p>
												</a>
											</div>
											<div class="col-6">
												<a href="add-staff.html"
													class="d-block bg-warning-transparent ronded p-2 text-center mb-3 class-hover">
													<div class="avatar avatar-lg rounded-circle mb-2">
														<span
															class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-warning rounded-circle"><i
																class="ti ti-users-group"></i></span>
													</div>
													<p class="text-dark">Staffs</p>
												</a>
											</div>
											<div class="col-6">
												<a href="add-invoice.html"
													class="d-block bg-info-transparent ronded p-2 text-center mb-3 class-hover">
													<div class="avatar avatar-lg mb-2">
														<span
															class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-info rounded-circle"><i
																class="ti ti-license"></i></span>
													</div>
													<p class="text-dark">Invoice</p>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="pe-1">
							<a href="profile-settings.html#" id="dark-mode-toggle"
								class="dark-mode-toggle activate btn btn-outline-light bg-white btn-icon me-1">
								<i class="ti ti-moon"></i>
							</a>
							<a href="profile-settings.html#" id="light-mode-toggle"
								class="dark-mode-toggle btn btn-outline-light bg-white btn-icon me-1">
								<i class="ti ti-brightness-up"></i>
							</a>
						</div>
						<div class="pe-1" id="notification_item">
							<a href="profile-settings.html#" class="btn btn-outline-light bg-white btn-icon position-relative me-1"
								id="notification_popup">
								<i class="ti ti-bell"></i>
								<span class="notification-status-dot"></span>
							</a>
							<div class="dropdown-menu dropdown-menu-end notification-dropdown p-4">
								<div
									class="d-flex align-items-center justify-content-between border-bottom p-0 pb-3 mb-3">
									<h4 class="notification-title">Notifications (2)</h4>
									<div class="d-flex align-items-center">
										<a href="profile-settings.html#" class="text-primary fs-15 me-3 lh-1">Mark all as read</a>
										<div class="dropdown">
											<a href="javascript:void(0);" class="bg-white dropdown-toggle"
												data-bs-toggle="dropdown"><i class="ti ti-calendar-due me-1"></i>Today
											</a>
											<ul class="dropdown-menu mt-2 p-3">
												<li>
													<a href="javascript:void(0);" class="dropdown-item rounded-1">
														This Week
													</a>
												</li>
												<li>
													<a href="javascript:void(0);" class="dropdown-item rounded-1">
														Last Week
													</a>
												</li>
												<li>
													<a href="javascript:void(0);" class="dropdown-item rounded-1">
														Last Week
													</a>
												</li>
											</ul>
										</div>
									</div>
								</div>

								<div class="noti-content">
									<div class="d-flex flex-column">
										<div class="border-bottom mb-3 pb-3">
											<a href="activities.html">
												<div class="d-flex">
													<span class="avatar avatar-lg me-2 flex-shrink-0">
														<img src="assets/img/profiles/avatar-27.jpg" alt="Profile">
													</span>
													<div class="flex-grow-1">
														<p class="mb-1"><span class="text-dark fw-semibold">Shawn</span>
															performance in Math is
															below the threshold.</p>
														<span>Just Now</span>
													</div>
												</div>
											</a>
										</div>
										<div class="border-bottom mb-3 pb-3">
											<a href="activities.html" class="pb-0">
												<div class="d-flex">
													<span class="avatar avatar-lg me-2 flex-shrink-0">
														<img src="assets/img/profiles/avatar-23.jpg" alt="Profile">
													</span>
													<div class="flex-grow-1">
														<p class="mb-1"><span
																class="text-dark fw-semibold">Sylvia</span> added
															appointment on
															02:00 PM</p>
														<span>10 mins ago</span>
														<div
															class="d-flex justify-content-start align-items-center mt-1">
															<span class="btn btn-light btn-sm me-2">Deny</span>
															<span class="btn btn-primary btn-sm">Approve</span>
														</div>
													</div>
												</div>
											</a>
										</div>
										<div class="border-bottom mb-3 pb-3">
											<a href="activities.html">
												<div class="d-flex">
													<span class="avatar avatar-lg me-2 flex-shrink-0">
														<img src="assets/img/profiles/avatar-25.jpg" alt="Profile">
													</span>
													<div class="flex-grow-1">
														<p class="mb-1">New student record <span
																class="text-dark fw-semibold"> George</span> is
															created by <span class="text-dark fw-semibold">
																Teressa</span></p>
														<span>2 hrs ago</span>
													</div>
												</div>
											</a>
										</div>
										<div class="border-0 mb-3 pb-0">
											<a href="activities.html">
												<div class="d-flex">
													<span class="avatar avatar-lg me-2 flex-shrink-0">
														<img src="assets/img/profiles/avatar-01.jpg" alt="Profile">
													</span>
													<div class="flex-grow-1">
														<p class="mb-1">A new teacher record for <span
																class="text-dark fw-semibold">Elisa</span>
														</p>
														<span>09:45 AM</span>
													</div>
												</div>
											</a>
										</div>
									</div>
								</div>
								<div class="d-flex p-0">
									<a href="profile-settings.html#" class="btn btn-light w-100 me-2">Cancel</a>
									<a href="activities.html" class="btn btn-primary w-100">View All</a>
								</div>
							</div>
						</div>
						<div class="pe-1">
							<a href="chat.html" class="btn btn-outline-light bg-white btn-icon position-relative me-1">
								<i class="ti ti-brand-hipchat"></i>
								<span class="chat-status-dot"></span>
							</a>
						</div>
						<div class="pe-1">
							<a href="profile-settings.html#" class="btn btn-outline-light bg-white btn-icon me-1">
								<i class="ti ti-chart-bar"></i>
							</a>
						</div>
						<div class="pe-1">
							<a href="profile-settings.html#" class="btn btn-outline-light bg-white btn-icon me-1" id="btnFullscreen">
								<i class="ti ti-maximize"></i>
							</a>
						</div>
						<div class="dropdown ms-1">
							<a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
								data-bs-toggle="dropdown">
								<span class="avatar avatar-md rounded">
									<img src="assets/img/profiles/avatar-27.jpg" alt="Img" class="img-fluid">
								</span>
							</a>
							<div class="dropdown-menu">
								<div class="d-block">
									<div class="d-flex align-items-center p-2">
										<span class="avatar avatar-md me-2 online avatar-rounded">
											<img src="assets/img/profiles/avatar-27.jpg" alt="img">
										</span>
										<div>
											<h6 class="">Kevin Larry</h6>
											<p class="text-primary mb-0">Administrator</p>
										</div>
									</div>
									<hr class="m-0">
									<a class="dropdown-item d-inline-flex align-items-center p-2" href="profile.html">
										<i class="ti ti-user-circle me-2"></i>My Profile</a>
									<a class="dropdown-item d-inline-flex align-items-center p-2"
										href="profile-settings.html"><i class="ti ti-settings me-2"></i>Settings</a>
									<hr class="m-0">
									<a class="dropdown-item d-inline-flex align-items-center p-2" href="login.html"><i
											class="ti ti-login me-2"></i>Logout</a>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>

			<!-- Mobile Menu -->
			<div class="dropdown mobile-user-menu">
				<a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
					aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
				<div class="dropdown-menu dropdown-menu-end">
					<a class="dropdown-item" href="profile.html">My Profile</a>
					<a class="dropdown-item" href="profile-settings.html">Settings</a>
					<a class="dropdown-item" href="login.html">Logout</a>
				</div>
			</div>
			<!-- /Mobile Menu -->

		</div>
		<!-- /Header -->

		<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li>
                    <a href="javascript:void(0);" class="d-flex align-items-center border bg-white rounded p-2 mb-4">
                        <img src="assets/img/icons/global-img.svg" class="avatar avatar-md img-fluid rounded"
                            alt="Profile">
                        <span
                            class="text-dark ms-2 fw-normal"><?= !empty($user['sube_adi']) ? htmlspecialchars($user['sube_adi']) . ' Şubesi' : 'Geçersiz Şube' ?></span>
                    </a>
                </li>
            </ul>
            <ul>
                <li>
                    <h6 class="submenu-hdr"><span>Anasayfa</span></h6>
                    <ul>

                        <li><a href="index.php" class="menuactive"><i
                                    class="ti ti-layout-dashboard"></i><span>Anasayfa</span></a></li>
                                    <li><a href="ogrenci-listesi.php"><i class="ti ti-school"></i><span>Duyurular</span></a>
                                    

                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Kişiler</span></h6>
                    <ul>

                        <li><a href="ogrenci-listesi.php"><i class="ti ti-school"></i><span>Öğrenciler</span></a>
                        <li><a href="ogrenci-listesi.php"><i class="ti ti-user-bolt"></i><span>Öğretmenler</span></a>
                            <!--<li><a href="ogrenci-listesi.php" ><i class="ti ti-users"></i><span>Öğretmenler</span></a>
                        <li><a href="ogrenci-listesi.php" ><i class="ti ti-users-group"></i><span>Gruplar-Firmalar</span></a></li>-->
                        <li><a href="personeller.php"><i class="ti ti-users-group"></i><span>Personeller</span></a>

                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Sınıf Yönetimi</span></h6>
                    <ul>

                        <li><a href="ogrenci-listesi.php"><i class="ti ti-school"></i><span>Sınıflar</span></a>
                        <li><a href="ogrenci-listesi.php"><i class="ti ti-user-bolt"></i><span>Konular</span></a>
                            <!--<li><a href="ogrenci-listesi.php" ><i class="ti ti-users"></i><span>Öğretmenler</span></a>
                        <li><a href="ogrenci-listesi.php" ><i class="ti ti-users-group"></i><span>Gruplar-Firmalar</span></a></li>-->
                        <li><a href="personeller.php"><i class="ti ti-users-group"></i><span>Ödevler</span></a>

                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>İşlemler</span></h6>
                    <ul>
                        <li><a href="gorusme.php"><i class="ti ti-message-circle"></i><span>Görüşme</span></a></li>
                        <li><a href="kitap.php"><i class="ti ti-book"></i><span>Kitap Satış</span></a></li>
                        <li><a href="is-basvuru.php"><i class="ti ti-briefcase"></i><span>İş Başvuruları</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Muhasebe</span></h6>
                    <ul>
                        <li><a href="kasa1.php"><i class="ti ti-report-money"></i><span>Kasa</span></a></li>
                        <li><a href="ay-basi.php"><i class="ti ti-calendar-stats"></i><span>Ay Başı
                                    Yapılacaklar</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Kurumsal</span></h6>
                    <ul>
                        <li><a href="sirketler.php"><i class="ti ti-building-handshake"></i><span>Şirket
                                    İşbirlikleri</span></a></li>
                        <li><a href="bilgilendirme.php"><i class="ti ti-info-circle"></i><span>Bilgilendirme
                                    Formu</span></a></li>
                        <li><a href="gruplar.php"><i class="ti ti-users"></i><span>Tüm Gruplar</span></a></li>
                        <li><a href="meb-sinif.php"><i class="ti ti-school"></i><span>MEB Sınıfları</span></a></li>
                        <li><a href="gecmis-islemler.php"><i class="ti ti-history"></i><span>İşlem Geçmişi</span></a>
                        </li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Yönetim</span></h6>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-layout-list"></i><span>Okul
                                    Parametreler</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="alan-bilgisi.php">Alan Bilgisi</a></li>
                                <li><a href="birim-bilgisi.php">Birim Bilgisi</a></li>
                                <li><a href="donem-bilgisi.php">Dönem Bilgisi</a></li>
                                <li><a href="grup-bilgisi.php">Grup Bilgisi</a></li>
                                <li><a href="sinif-bilgisi.php">Sınıf Bilgisi</a></li>
                                <li><a href="sube-bilgisi.php">Şube Bilgisi</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-location-pin"></i><span>Konum
                                    Parametreler</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="il-bilgisi.php">İl Bilgisi</a></li>
                                <li><a href="ilce-bilgisi.php">İlçe Bilgisi</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-report-money"></i><span>Muhasebe
                                    Parametreleri</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="fees-group.html">Kasa Tercihleri</a></li>
                                <li><a href="fees-type.html">Ödeme Yöntemleri</a></li>
                                <li><a href="fees-master.html">Kasa Hareket Yöntemleri</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
             <li>
                    <h6 class="submenu-hdr"><span>Destek & Dokümantasyon</span></h6>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-layout-list"></i><span>Destek</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="alan-bilgisi.php">Kullanım Kılavuzu</a></li>
                                <li><a href="alan-bilgisi.php">Sık Sorulan Sorular (SSS)</a></li>
                                <li><a href="alan-bilgisi.php">Teknik Destek</a></li>
                                <li><a href="alan-bilgisi.php">Talep / Ticket Sistemi</a></li>
                                <li><a href="alan-bilgisi.php">Güncelleme Notları</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>    
            <li>
                    <h6 class="submenu-hdr"><span>Sistem Ayarları</span></h6>
                    <ul>
                        
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-layout-list"></i><span>Kullanıcı & Yetkilendirme</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-layout-list"></i><span>Kullanıcı Rolleri</span><span class="menu-arrow"></span></a>
                                
                                <ul>
                                    <li><a href="alan-bilgisi.php">Kullanıcı Rolleri</a></li>
                                    <li><a href="alan-bilgisi.php">Admin</a></li>
                                    <li><a href="alan-bilgisi.php">Yönetici</a></li>
                                    <li><a href="alan-bilgisi.php">Öğrenci</a></li>
                                    <li><a href="alan-bilgisi.php">Öğretmen</a></li>
                                    <li><a href="alan-bilgisi.php">Personel</a></li>
                                    <li><a href="alan-bilgisi.php">Misafir</a></li>
                                </ul> 
                                </li>
                                
                                <li><a href="alan-bilgisi.php">Yetki Tanımlama</a></li>
                                <li><a href="alan-bilgisi.php">Giriş & Güvenlik Ayarları</a></li>
                                <li><a href="alan-bilgisi.php">Şifre Poltikaları</a></li>
                                <li><a href="alan-bilgisi.php">Kişisel Güvenlik Kodları</a></li>
                                <li><a href="birim-bilgisi.php">Giriş Logları</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-layout-list"></i><span>Genel Ayarlar</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="profil-ayarlari.php">Profil Ayarları</a></li>
                                <li><a href="alan-bilgisi.php">Kurum Bilgileri</a></li>
                                <li><a href="alan-bilgisi.php">Şube Yönetimi</a></li>
                                <li><a href="alan-bilgisi.php">Dil Ayarları</a></li>
                                <li><a href="alan-bilgisi.php">Tema & Görünüm</a></li>
                                <li><a href="birim-bilgisi.php">Güvenlik Ayarları</a></li>
                                <li><a href="donem-bilgisi.php">Bildirim Ayarları</a></li>
                                <li><a href="grup-bilgisi.php">Entegrasyonlar</a></li>
                                <li><a href="grup-bilgisi.php">Sürümler</a></li>
                                <li><a href="sinif-bilgisi.php">Yedekleme</a></li>
                                <li><a href="sube-bilgisi.php">Güncelleme</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- <li>
                    <h6 class="submenu-hdr"><span>HRM</span></h6>
                    <ul>
                        <li><a href="staffs.html"><i class="ti ti-users-group"></i><span>Staffs</span></a></li>
                        <li><a href="departments.html"><i class="ti ti-layout-distribute-horizontal"></i><span>Departments</span></a></li>
                        <li><a href="designation.html"><i class="ti ti-user-exclamation"></i><span>Designation</span></a></li>
                        <li class="submenu">
                            <a href="javascript:void(0);" ><i class="ti ti-calendar-share"></i><span>Attendance</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="student-attendance.html">Student Attendance</a></li>
                                <li><a href="teacher-attendance.html">Teacher Attendance</a></li>
                                <li><a href="staff-attendance.html">Staff Attendance</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);" ><i class="ti ti-calendar-stats"></i><span>Leaves</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="list-leaves.html">List of leaves</a></li>
                                <li><a href="approve-request.html">Approve Request</a></li>
                            </ul>
                        </li>
                        <li><a href="holidays.html"><i class="ti ti-briefcase"></i><span>Holidays</span></a></li>
                        <li><a href="payroll.html"><i class="ti ti-moneybag"></i><span>Payroll</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Finance & Accounts</span></h6>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-swipe"></i><span>Accounts</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="expenses.html">Expenses</a></li>
                                <li><a href="expenses-category.html">Expense Category</a></li>
                                <li><a href="accounts-income.html">Income</a></li>
                                <li><a href="accounts-invoices.html">Invoices</a></li>
                                <li><a href="invoice.html">Invoice View</a></li>
                                <li><a href="accounts-transactions.html">Transactions</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Announcements</span></h6>
                    <ul>
                        <li><a href="notice-board.html"><i class="ti ti-clipboard-data"></i><span>Notice Board</span></a></li>
                        <li><a href="events.html"><i class="ti ti-calendar-question"></i><span>Events</span></a></li>
                    </ul>

                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Reports</span></h6>
                    <ul>
                        <li><a href="attendance-report.html"><i class="ti ti-calendar-due"></i><span>Attendance Report</span></a></li>
                        <li><a href="class-report.html"><i class="ti ti-graph"></i><span>Class Report</span></a></li>
                        <li><a href="student-report.html"><i class="ti ti-chart-infographic"></i><span>Student Report</span></a></li>
                        <li><a href="grade-report.html"><i class="ti ti-calendar-x"></i><span>Grade Report</span></a></li>
                        <li><a href="leave-report.html"><i class="ti ti-line"></i><span>Leave Report</span></a></li>
                        <li><a href="fees-report.html"><i class="ti ti-mask"></i><span>Fees Report</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>User Management</span></h6>
                    <ul>
                        <li><a href="users.html"><i class="ti ti-users-minus"></i><span>Users</span></a></li>
                        <li><a href="roles-permission.html"><i class="ti ti-shield-plus"></i><span>Roles & Permissions</span></a></li>
                        <li><a href="delete-account.html"><i class="ti ti-user-question"></i><span>Delete Account Request</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Membership</span></h6>
                    <ul>
                        <li><a href="membership-plans.html"><i class="ti ti-user-plus"></i><span>Membership Plans</span></a></li>
                        <li><a href="membership-addons.html"><i class="ti ti-cone-plus"></i><span>Membership Addons</span></a></li>
                        <li><a href="membership-transactions.html"><i class="ti ti-file-power"></i><span>Transactions</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Content</span></h6>
                    <ul>
                        <li><a href="pages.html"><i class="ti ti-page-break"></i><span>Pages</span></a></li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-brand-blogger"></i><span>Blog</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="blog.html">All Blogs</a></li>
                                <li><a href="blog-categories.html">Categories</a></li>
                                <li><a href="blog-comments.html">Comments</a></li>
                                <li><a href="blog-tags.html">Tags</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-map-pin-search"></i><span>Location</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="countries.html">Countries</a></li>
                                <li><a href="states.html">States</a></li>
                                <li><a href="cities.html">Cities</a></li>
                            </ul>
                        </li>
                        <li><a href="testimonials.html"><i class="ti ti-quote"></i><span>Testimonials</span></a></li>
                        <li><a href="faq.html"><i class="ti ti-question-mark"></i><span>FAQ</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Support</span></h6>
                    <ul>
                        <li><a href="contact-messages.html"><i class="ti ti-message"></i><span>Contact Messages</span></a></li>
                        <li><a href="tickets.html"><i class="ti ti-ticket"></i><span>Tickets</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Pages</span></h6>
                    <ul>
                        <li><a href="profile.html"><i class="ti ti-user"></i><span>Profile</span></a></li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-lock-open"></i><span>Authentication</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu submenu-two"><a href="javascript:void(0);" class="">Login<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="login.html">Cover</a></li>
                                        <li><a href="login-2.html">Illustration</a></li>
                                        <li><a href="login-3.html">Basic</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-two"><a href="javascript:void(0);" class="">Register<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="register.html">Cover</a></li>
                                        <li><a href="register-2.html">Illustration</a></li>
                                        <li><a href="register-3.html">Basic</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-two"><a href="javascript:void(0);">Forgot Password<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="forgot-password.html">Cover</a></li>
                                        <li><a href="forgot-password-2.html">Illustration</a></li>
                                        <li><a href="forgot-password-3.html">Basic</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-two"><a href="javascript:void(0);">Reset Password<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="reset-password.html">Cover</a></li>
                                        <li><a href="reset-password-2.html">Illustration</a></li>
                                        <li><a href="reset-password-3.html">Basic</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-two"><a href="javascript:void(0);">Email Verification<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="email-verification.html">Cover</a></li>
                                        <li><a href="email-verification-2.html">Illustration</a></li>
                                        <li><a href="email-verification-3.html">Basic</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-two"><a href="javascript:void(0);">2 Step Verification<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="two-step-verification.html">Cover</a></li>
                                        <li><a href="two-step-verification-2.html">Illustration</a></li>
                                        <li><a href="two-step-verification-3.html">Basic</a></li>
                                    </ul>
                                </li>
                                <li><a href="lock-screen.html">Lock Screen</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-error-404"></i><span>Error Pages</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="404-error.html">404 Error</a></li>
                                <li><a href="500-error.html">500 Error</a></li>
                            </ul>
                        </li>
                        <li><a href="blank-page.html"><i class="ti ti-brand-nuxt"></i><span>Blank Page</span></a></li>
                        <li><a href="coming-soon.html"><i class="ti ti-file"></i><span>Coming Soon</span></a></li>
                        <li><a href="under-maintenance.html"><i class="ti ti-moon-2"></i><span>Under Maintenance</span></a></li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Settings</span></h6>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-shield-cog"></i><span>General Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="profile-settings.html">Profile Settings</a></li>
                                <li><a href="security-settings.html">Security Settings</a></li>
                                <li><a href="notifications-settings.html">Notifications Settings</a></li>
                                <li><a href="connected-apps.html">Connected Apps</a></li>
                            </ul>
                        </li>


                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-device-laptop"></i><span>Website Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="company-settings.html">Company Settings</a></li>
                                <li><a href="localization.html">Localization</a></li>
                                <li><a href="prefixes.html">Prefixes</a></li>
                                <li><a href="preferences.html">Preferences</a></li>
                                <li><a href="social-authentication.html">Social Authentication</a></li>
                                <li><a href="language.html">Language</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-apps"></i><span>App Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="invoice-settings.html">Invoice Settings</a></li>
                                <li><a href="custom-fields.html">Custom Fields</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-file-symlink"></i><span>System Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="email-settings.html">Email Settings</a></li>
                                <li><a href="email-templates.html">Email Templates</a></li>
                                <li><a href="sms-settings.html">SMS Settings</a></li>
                                <li><a href="otp-settings.html">OTP</a></li>
                                <li><a href="gdpr-cookies.html">GDPR Cookies</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-zoom-money"></i><span>Financial Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="payment-gateways.html">Payment Gateways </a></li>
                                <li><a href="tax-rates.html">Tax Rates</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-calendar-repeat"></i><span>Academic Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="school-settings.html">School Settings </a></li>
                                <li><a href="religion.html">Religion</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-flag-cog"></i><span>Other Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="storage.html">Storage</a></li>
                                <li><a href="ban-ip-address.html">Ban IP Address</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>UI Interface</span></h6>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-hierarchy-2"></i><span>Base UI</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="ui-alerts.html">Alerts</a></li>
                                <li><a href="ui-accordion.html">Accordion</a></li>
                                <li><a href="ui-avatar.html">Avatar</a></li>
                                <li><a href="ui-badges.html">Badges</a></li>
                                <li><a href="ui-borders.html">Border</a></li>
                                <li><a href="ui-buttons.html">Buttons</a></li>
                                <li><a href="ui-buttons-group.html">Button Group</a></li>
                                <li><a href="ui-breadcrumb.html">Breadcrumb</a></li>
                                <li><a href="ui-cards.html">Card</a></li>
                                <li><a href="ui-carousel.html">Carousel</a></li>
                                <li><a href="ui-colors.html">Colors</a></li>
                                <li><a href="ui-dropdowns.html">Dropdowns</a></li>
                                <li><a href="ui-grid.html">Grid</a></li>
                                <li><a href="ui-images.html">Images</a></li>
                                <li><a href="ui-lightbox.html">Lightbox</a></li>
                                <li><a href="ui-media.html">Media</a></li>
                                <li><a href="ui-modals.html">Modals</a></li>
                                <li><a href="ui-offcanvas.html">Offcanvas</a></li>
                                <li><a href="ui-pagination.html">Pagination</a></li>
                                <li><a href="ui-popovers.html">Popovers</a></li>
                                <li><a href="ui-progress.html">Progress</a></li>
                                <li><a href="ui-placeholders.html">Placeholders</a></li>
                                <li><a href="ui-rangeslider.html">Range Slider</a></li>
                                <li><a href="ui-spinner.html">Spinner</a></li>
                                <li><a href="ui-sweetalerts.html">Sweet Alerts</a></li>
                                <li><a href="ui-nav-tabs.html">Tabs</a></li>
                                <li><a href="ui-toasts.html">Toasts</a></li>
                                <li><a href="ui-tooltips.html">Tooltips</a></li>
                                <li><a href="ui-typography.html">Typography</a></li>
                                <li><a href="ui-video.html">Video</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-hierarchy-3"></i><span>Advanced UI</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="ui-ribbon.html">Ribbon</a></li>
                                <li><a href="ui-clipboard.html">Clipboard</a></li>
                                <li><a href="ui-drag-drop.html">Drag & Drop</a></li>
                                <li><a href="ui-rangeslider.html">Range Slider</a></li>
                                <li><a href="ui-rating.html">Rating</a></li>
                                <li><a href="ui-text-editor.html">Text Editor</a></li>
                                <li><a href="ui-counter.html">Counter</a></li>
                                <li><a href="ui-scrollbar.html">Scrollbar</a></li>
                                <li><a href="ui-stickynote.html">Sticky Note</a></li>
                                <li><a href="ui-timeline.html">Timeline</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-chart-line"></i>
                                <span>Charts</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="chart-apex.html">Apex Charts</a></li>
                                <li><a href="chart-c3.html">Chart C3</a></li>
                                <li><a href="chart-js.html">Chart Js</a></li>
                                <li><a href="chart-morris.html">Morris Charts</a></li>
                                <li><a href="chart-flot.html">Flot Charts</a></li>
                                <li><a href="chart-peity.html">Peity Charts</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-icons"></i>
                                <span>Icons</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="icon-fontawesome.html">Fontawesome Icons</a></li>
                                <li><a href="icon-feather.html">Feather Icons</a></li>
                                <li><a href="icon-ionic.html">Ionic Icons</a></li>
                                <li><a href="icon-material.html">Material Icons</a></li>
                                <li><a href="icon-pe7.html">Pe7 Icons</a></li>
                                <li><a href="icon-simpleline.html">Simpleline Icons</a></li>
                                <li><a href="icon-themify.html">Themify Icons</a></li>
                                <li><a href="icon-weather.html">Weather Icons</a></li>
                                <li><a href="icon-typicon.html">Typicon Icons</a></li>
                                <li><a href="icon-flag.html">Flag Icons</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-input-search"></i><span>Forms</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu submenu-two">
                                    <a href="javascript:void(0);">Form Elements<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="form-basic-inputs.html">Basic Inputs</a></li>
                                        <li><a href="form-checkbox-radios.html">Checkbox & Radios</a></li>
                                        <li><a href="form-input-groups.html">Input Groups</a></li>
                                        <li><a href="form-grid-gutters.html">Grid & Gutters</a></li>
                                        <li><a href="form-select.html">Form Select</a></li>
                                        <li><a href="form-mask.html">Input Masks</a></li>
                                        <li><a href="form-fileupload.html">File Uploads</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-two">
                                    <a href="javascript:void(0);">Layouts<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="form-horizontal.html">Horizontal Form</a></li>
                                        <li><a href="form-vertical.html">Vertical Form</a></li>
                                        <li><a href="form-floating-labels.html">Floating Labels</a></li>
                                    </ul>
                                </li>
                                <li><a href="form-validation.html">Form Validation</a></li>
                                <li><a href="form-select2.html">Select2</a></li>
                                <li><a href="form-wizard.html">Form Wizard</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-table-plus"></i><span>Tables</span><span
                                    class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="tables-basic.html">Basic Tables </a></li>
                                <li><a href="data-tables.html">Data Table </a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <h6 class="submenu-hdr"><span>Help</span></h6>
                    <ul>
                        <li><a href="https://preschool.dreamstechnologies.com/documentation/index.html"><i class="ti ti-file-text"></i><span>Documentation</span></a></li>
                        <li><a href="https://preschool.dreamstechnologies.com/documentation/changelog.html"><i class="ti ti-exchange"></i><span>Changelog</span><span class="badge badge-primary badge-xs text-white fs-10 ms-auto">v1.8.3</span></a></li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-menu-2"></i><span>Multi Level</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="javascript:void(0);">Multilevel  1</a></li>
                                <li class="submenu submenu-two"><a href="javascript:void(0);">Multilevel  2<span class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="javascript:void(0);">Multilevel  2.1</a></li>
                                        <li class="submenu submenu-two submenu-three"><a href="javascript:void(0);">Multilevel  2.2<span class="menu-arrow inside-submenu inside-submenu-two"></span></a>
                                            <ul>
                                                <li><a href="javascript:void(0);">Multilevel  2.2.1</a></li>
                                                <li><a href="javascript:void(0);">Multilevel  2.2.2</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li><a href="javascript:void(0);">Multilevel  3</a></li>
                            </ul>
                        </li>
                    </ul>
                </li> -->
            </ul>
        </div>
    </div>
</div>

		<!-- Page Wrapper -->
		<div class="page-wrapper">
			<div class="content">
				<div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
					<div class="my-auto mb-2">
						<h3 class="page-title mb-1">Genel Ayarlar</h3>
						<nav>
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item">
									<a href="index.html">Kontrol Paneli</a>
								</li>
								<li class="breadcrumb-item">
									<a href="javascript:void(0);">Settings</a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Genel Ayarlar</li>
							</ol>
						</nav>
					</div>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
						<div class="pe-1 mb-2">
							<a href="profile-settings.html#" class="btn btn-outline-light bg-white btn-icon" data-bs-toggle="tooltip"
								data-bs-placement="top" aria-label="Refresh" data-bs-original-title="Refresh">
								<i class="ti ti-refresh"></i>
							</a>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xxl-2 col-xl-3">
						<div class="pt-3 d-flex flex-column list-group mb-4">
							<a href="profile-settings.html" class="d-block rounded p-2 active">Profil Ayarları</a>
							<a href="security-settings.html" class="d-block rounded p-2">Güvenlik Ayarları</a>
							<a href="notifications-settings.html" class="d-block rounded p-2">Bildirimler</a>
							<a href="connected-apps.html" class="d-block rounded p-2">Bağlı Cihazlar</a>
						</div>
					</div>
					<div class="col-xxl-10 col-xl-9">
						<div class="flex-fill border-start ps-3">
							<form action="profile-settings.html">
								<div
									class="d-flex align-items-center justify-content-between flex-wrap border-bottom pt-3 mb-3">
									<div class="mb-3">
										<h5 class="mb-1">Profil Ayarları</h5>
										<p>Fotoğrafınızı buraya yükleyin</p>
									</div>
									<div class="mb-3">
										<button class="btn btn-light me-2" type="button">İptal</button>
										<button class="btn btn-primary" type="submit">Kaydet</button>
									</div>
								</div>
								<div class="d-md-flex d-block">
									<div class="flex-fill">
										<div class="card">
											<div class="card-header p-3">
												<h5>Personel Bilgileri</h5>
											</div>
											<div class="card-body p-3 pb-0">
												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">Adınız</label>
														<input type="text" class="form-control"
															placeholder="Enter First Name">
													</div>
													<div class="mb-3 flex-fill">
														<label class="form-label">Soyadınız</label>
														<input type="text" class="form-control"
															placeholder="Enter Last Name">
													</div>
												</div>
												<div class="mb-3">
													<label class="form-label">Email Adresiniz</label>
													<input type="email" class="form-control" placeholder="Enter Email">
												</div>
												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">Kullanıcı Adınız</label>
														<input type="email" class="form-control"
															placeholder="Enter User Name">
													</div>
													<div class="mb-3 flex-fill">
														<label class="form-label">Telefon Numaranız</label>
														<input type="email" class="form-control"
															placeholder="Enter Phone Number">
													</div>
												</div>
											</div>
										</div>
										<div class="card">
											<div class="card-header p-3">
												<h5>Adres Bilgileriniz</h5>
											</div>
											<div class="card-body p-3 pb-0">
												<div class="mb-3">
													<label class="form-label">Adres</label>
													<input type="text" class="form-control" placeholder="Enter Address">
												</div>
												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">Ülke</label>
														<input type="text" class="form-control"
															placeholder="Enter Country">
													</div>
													<div class="mb-3 flex-fill">
														<label class="form-label">Eyalet / İl</label>
														<input type="email" class="form-control"
															placeholder="Enter State">
													</div>
												</div>
												<div class="d-block d-xl-flex">
													<div class="mb-3 flex-fill me-xl-3 me-0">
														<label class="form-label">Şehir</label>
														<input type="email" class="form-control" placeholder="City">
													</div>
													<div class="mb-3 flex-fill">
														<label class="form-label">Posta Kodu</label>
														<input type="email" class="form-control"
															placeholder="Enter Postal Code">
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="settings-right-sidebar ms-md-3">
										<div class="card">
											<div class="card-header p-3">
												<h5>Kişisel Bilgileriniz</h5>
											</div>
											<div class="card-body p-3 pb-0">
												<div class="settings-profile-upload">
													<span class="profile-pic">
														<img src="assets/img/profiles/avatar-27.jpg" alt="Profile">
													</span>
													<div class="title-upload">
														<h5>Fotoğrafını Düzenle</h5>
														<a href="profile-settings.html#" class="me-2">Sil </a>
														<a href="profile-settings.html#" class="text-primary">Güncelle</a>
													</div>
												</div>
												<div class="profile-uploader profile-uploader-two">
													<span class="upload-icon"><i class="ti ti-upload"></i></span>
													<div class="drag-upload-btn mb-0 border-0 pb-0 bg-transparent">
														<p class="upload-btn"><span>Yüklemek için tıklayın</span> veya sürükle ve bırak
</p>
														<h6>JPG ya da PNG</h6>
														<h6>(Max 450 x 450 px)</h6>
													</div>
													<input type="file" class="form-control" multiple="" id="image_sign">
													<div id="frames"></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Page Wrapper -->

	</div>
	<!-- /Main Wrapper -->

	<!-- jQuery -->
	<script src="assets/js/jquery-3.7.1.min.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Bootstrap Core JS -->
	<script src="assets/js/bootstrap.bundle.min.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Daterangepikcer JS -->
	<script src="assets/js/moment.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>
	<script src="assets/plugins/daterangepicker/daterangepicker.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Datetimepicker JS -->
	<script src="assets/plugins/moment/moment.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>
	<script src="assets/js/bootstrap-datetimepicker.min.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Feather Icon JS -->
	<script src="assets/js/feather.min.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Slimscroll JS -->
	<script src="assets/js/jquery.slimscroll.min.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Datatable JS -->
	<script src="assets/js/jquery.dataTables.min.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>
	<script src="assets/js/dataTables.bootstrap5.min.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

	<!-- Custom JS -->
	<script src="assets/js/script.js" type="245bdda0a8c32f92e085d6f5-text/javascript"></script>

<script src="../../cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="245bdda0a8c32f92e085d6f5-|49" defer></script><script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"3ca157e612a14eccbb30cf6db6691c29","server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>

</html>