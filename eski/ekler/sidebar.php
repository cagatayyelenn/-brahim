<?php
$sube_id = intval($_SESSION['subedurum']);
$subeler = "SELECT * FROM `sube` WHERE `sube`.`sube_id` = $sube_id";
$subess  = $Ydil->getone($subeler);

// Oturumu açan kullanıcı (güvenli)
$id = (int)($_SESSION['user_id'] ?? 0);
$kullanici = ['ad'=>'','soyad'=>''];
if ($id > 0) {
    $kullanici = $Ydil->getone("SELECT ad, soyad,mail_adres FROM kullanici_giris WHERE id={$id}") ?: $kullanici;
}
?> 
<nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
    <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle"><i data-feather="menu"></i></button>
    <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="anasayfa.php">Sqooler / <?= $subess['sube_adi']; ?> Şubesi</a>
    <ul class="navbar-nav align-items-center ms-auto">
        <li class="nav-item dropdown no-caret d-none d-md-block me-3">
            <span class="nav-link dropdown-toggle"  >
                <div class="fw-500"><?=  $kullanici['ad']; ?> <?=  $kullanici['soyad']; ?>  </div>
            </span>
        </li>

        <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
            <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownAlerts" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i data-feather="bell"></i><span class="badge">2</span></a>
            <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownAlerts">
                <h6 class="dropdown-header dropdown-notifications-header">
                    <i class="me-2" data-feather="bell"></i>
                    Uyarılar
                </h6>
                <a class="dropdown-item dropdown-notifications-item" href="#!">
                    <div class="dropdown-notifications-item-icon bg-warning"><i data-feather="activity"></i></div>
                    <div class="dropdown-notifications-item-content">
                        <div class="dropdown-notifications-item-content-text">Randevulu listesinde öğrenci var.</div>
                    </div>
                </a>
                <a class="dropdown-item dropdown-notifications-item" href="#!"> 
                    <div class="dropdown-notifications-item-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="dropdown-notifications-item-content">
                        <div class="dropdown-notifications-item-content-text">Ödemesi geçikmiş öğrenciler var!</div>
                    </div>
                </a>
                <a class="dropdown-item dropdown-notifications-item" href="#!">
                    <div class="dropdown-notifications-item-icon bg-info"><i data-feather="bar-chart"></i></div>
                    <div class="dropdown-notifications-item-content">
                        <div class="dropdown-notifications-item-content-text">İbrahim Öğretmenin Dersi Yaklaşıyor</div>
                    </div>
                </a>
            </div>
        </li>



        <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
            <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img class="img-fluid" src="assets/img/illustrations/profiles/profile-1.png" /></a>
            <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                <h6 class="dropdown-header d-flex align-items-center">
                    <img class="dropdown-user-img" src="assets/img/illustrations/profiles/profile-1.png" />
                    <div class="dropdown-user-details">
                        <div class="dropdown-user-details-name">Giriş yapan</div>
                        <div class="dropdown-user-details-email"><?=  $kullanici['mail_adres']; ?> </div>
                    </div>
                </h6>
                <div class="dropdown-divider"></div>

                <a class="dropdown-item" href="cikis.php">
                    <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                    Çıkış
                </a>
            </div>
        </li>
    </ul>
</nav>
