<?php
// Menü durum hesaplama – temiz/sade

$gelenveri = basename($_SERVER['SCRIPT_NAME']);

$ogrenci_url   = ['ogrenci-kayit.php','ogrenci-listesi.php'];
$ogretmen_url  = ['ogretmen-kayit.php','ogretmen-listesi.php'];
$personel_url  = ['personel-kayit.php','personel-listesi.php'];
$islemler_url  = ['birim-bilgisi.php','donem-bilgisi.php','sinif-bilgisi.php','sube-bilgisi.php','grup-bilgisi.php','alan-bilgisi.php','il-bilgisi.php','ilce-bilgisi.php'];
$muhasebe_url  = ['sozlesme-tahsilat.php','yeni-gelir-gider.php','kasa-listesi.php','kasa.php','odemesi-gecikmis.php'];

// Yardımcılar
$inGroup = function(string $current, array $list): bool {
    return in_array($current, $list, true);
};
$showIf   = fn(bool $b) => $b ? 'show'   : '';
$activeIf = fn(bool $b) => $b ? 'active' : '';

// Gruplar
$ogrenciGrupAc   = $inGroup($gelenveri, $ogrenci_url);
$ogretmenGrupAc  = $inGroup($gelenveri, $ogretmen_url);
$personelGrupAc  = $inGroup($gelenveri, $personel_url);
$islemlerGrupAc  = $inGroup($gelenveri, $islemler_url);
$muhasebeGrupAc  = $inGroup($gelenveri, $muhasebe_url);

// Alt menüler
$ogrencimenuac       = $showIf($ogrenciGrupAc);
$ogrencimenuactive   = $activeIf($ogrenciGrupAc);

$ogretmenmenuac      = $showIf($ogretmenGrupAc);
$ogretmenmenuactive  = $activeIf($ogretmenGrupAc);

$personelmenuac      = $showIf($personelGrupAc);
$personelmenuactive  = $activeIf($personelGrupAc);

$islemlerac          = $showIf($islemlerGrupAc);
$muhac               = $showIf($muhasebeGrupAc);

// Ana “Kayıt İşlemleri” grubu (herhangi bir alt grup açıksa)
$kayitGrupAc         = ($ogrenciGrupAc || $ogretmenGrupAc || $personelGrupAc);
$kayitiac            = $showIf($kayitGrupAc);
$kayitiactive        = $activeIf($kayitGrupAc);

// Oturumu açan kullanıcı (güvenli)
$id = (int)($_SESSION['user_id'] ?? 0);
$kullanici = ['ad'=>'','soyad'=>''];
if ($id > 0) {
    $kullanici = $Ydil->getone("SELECT ad, soyad,kisi FROM kullanici_giris WHERE kisi_id={$id}") ?: $kullanici;
}
?>
<div id="layoutSidenav_nav">
    <nav class="sidenav shadow-right sidenav-light">
        <div class="sidenav-menu">
            <div class="nav accordion" id="accordionSidenav">

                <div class="sidenav-menu-heading">Sqooler</div>

                <a class="nav-link collapsed"  data-bs-toggle="collapse" data-bs-target="#kayit"  >
                    <div class="nav-link-icon"><i data-feather="grid"></i></div> Kayıt İşlemleri
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo $kayitiac; ?>" id="kayit" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPagesMenu">
                        <a class="nav-link <?= @$ogrencimenuactive; ?>" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#ogrenciislemleri" aria-expanded="false" aria-controls="ogrenciislemleri">
                            Öğrenci İşlemleri
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?= @$ogrencimenuac; ?>" id="ogrenciislemleri" data-bs-parent="#accordionSidenavPagesMenu">
                            <nav class="sidenav-menu-nested nav">
                                <a class="nav-link <?php echo $gelenveri == 'ogrenci-kayit.php'   ? 'active' : ''; ?>" href="ogrenci-kayit.php">Yeni Öğrenci Kayıt</a>
                                <a class="nav-link <?php echo $gelenveri == 'ogrenci-listesi.php'   ? 'active' : ''; ?>" href="ogrenci-listesi.php">Öğrenci Listesi</a>
                            </nav>
                        </div>
                        <a class="nav-link <?= @$ogretmenmenuactive; ?>" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#ogretmenislemleri" aria-expanded="false" aria-controls="ogretmenislemleri">
                            Öğretmen İşlemleri
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?= @$ogretmenmenuac; ?>" id="ogretmenislemleri" data-bs-parent="#accordionSidenavPagesMenu">
                          <nav class="sidenav-menu-nested nav">
                              <a class="nav-link <?php echo $gelenveri == 'ogretmen-kayit.php'   ? 'active' : ''; ?>" href="ogretmen-kayit.php">Yeni Öğretmen Kayıt</a>
                              <a class="nav-link <?php echo $gelenveri == 'ogretmen-listesi.php'   ? 'active' : ''; ?>" href="ogretmen-listesi.php">Öğretmen Listesi</a>
                          </nav>
                        </div>
                        <a class="nav-link <?= @$personelmenuactive; ?>" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#personelislemleri" aria-expanded="false" aria-controls="personelislemleri">
                            Personel İşlemleri
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?= @$personelmenuac; ?>" id="personelislemleri" data-bs-parent="#accordionSidenavPagesMenu">
                          <nav class="sidenav-menu-nested nav">
                              <a class="nav-link <?php echo $gelenveri == 'personel-kayit.php'   ? 'active' : ''; ?>" href="personel-kayit.php">Yeni Personel Kayıt</a>
                              <a class="nav-link <?php echo $gelenveri == 'personel-listesi.php'   ? 'active' : ''; ?>" href="personel-listesi.php">Personel Listesi</a>
                          </nav>
                        </div>
                        <a class="nav-link <?php echo $gelenveri == 'ders-atama.php'   ? 'active' : ''; ?>" href="ders-atama.php">Ders Atama</a>
                        <a class="nav-link <?php echo $gelenveri == 'ders-kapama.php'   ? 'active' : ''; ?>" href="ders-kapama.php">Ders Kapama</a>
                    </nav>
                </div>
                <a class="nav-link collapsed"  data-bs-toggle="collapse" data-bs-target="#muhasebe"  >
                    <div class="nav-link-icon"><i data-feather="globe"></i></div> Muhasebe İşlemleri
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= $muhac; ?>" id="muhasebe" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavAppsMenu">
                      <a class="nav-link <?php echo $gelenveri == 'sozlesme-tahsilat.php'   ? 'active' : ''; ?>" href="sozlesme-tahsilat.php">Sözleşme  / Tashilat </a>
                      <a class="nav-link <?php echo $gelenveri == 'yeni-gelir-gider.php'   ? 'active' : ''; ?>" href="yeni-gelir-gider.php">Yeni Gelir-Gider Girişi</a>
                      <a class="nav-link <?php echo $gelenveri == 'kasa-listesi.php'   ? 'active' : ''; ?>" href="kasa-listesi.php">Gelir-Gider Listesi</a>
                      <a class="nav-link <?php echo $gelenveri == 'kasa.php'   ? 'active' : ''; ?>" href="kasa.php">Kasa Hareketleri</a>
                      <a class="nav-link <?php echo $gelenveri == 'odemesi-gecikmis.php'   ? 'active' : ''; ?>" href="odemesi-gecikmis.php">Ödemesi Gecikenlerin Listesi</a>
                    </nav>
                </div>
                <a class="nav-link collapsed"  data-bs-toggle="collapse" data-bs-target="#parametreler"  >
                    <div class="nav-link-icon"><i data-feather="repeat"></i></div> Parametreler
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= $islemlerac; ?>" id="parametreler" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavAppsMenu">
                    <a class="nav-link <?php echo $gelenveri == 'birim-bilgisi.php'   ? 'active' : ''; ?>" href="birim-bilgisi.php">Birim Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'donem-bilgisi.php'   ? 'active' : ''; ?>" href="donem-bilgisi.php">Dönem Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'sinif-bilgisi.php'   ? 'active' : ''; ?>" href="sinif-bilgisi.php">Sınıf Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'sube-bilgisi.php'   ? 'active' : ''; ?>" href="sube-bilgisi.php">Şube İşlemleri</a>
                    <a class="nav-link <?php echo $gelenveri == 'grup-bilgisi.php'   ? 'active' : ''; ?>" href="grup-bilgisi.php">Grup İşlemleri</a>
                    <a class="nav-link <?php echo $gelenveri == 'alan-bilgisi.php'   ? 'active' : ''; ?>" href="alan-bilgisi.php">Alan İşlemleri</a>
                    <a class="nav-link <?php echo $gelenveri == 'il-bilgisi.php'   ? 'active' : ''; ?>" href="il-bilgisi.php">İl Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'ilce-bilgisi.php'   ? 'active' : ''; ?>" href="ilce-bilgisi.php">İlçe Bilgisi</a>
                    <a class="nav-link" href="invoice.html">Kullanıcı İşlemleri</a>
                    <a class="nav-link" href="invoice.html">Yetkilendirme</a>
                    <a class="nav-link" href="invoice.html">Genel İşlemler</a>
                    <a class="nav-link" href="pricing.html">Rapor</a>
                    <a class="nav-link" href="invoice.html">İstatislik</a>
                  </nav>
                </div>
                <!--<a class="nav-link collapsed"  data-bs-toggle="collapse" data-bs-target="#collapsePages"  >
                    <div class="nav-link-icon"><i data-feather="layout"></i></div>
                      İşlemler Listesi
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= @$islemlerac ?>" id="collapseLayouts" data-bs-parent="#accordionSidenav">
                  <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavAppsMenu">
                    <a class="nav-link <?php echo $gelenveri == 'birim-bilgisi.php'   ? 'active' : ''; ?>" href="birim-bilgisi.php">Birim Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'donem-bilgisi.php'   ? 'active' : ''; ?>" href="donem-bilgisi.php">Dönem Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'sinif-bilgisi.php'   ? 'active' : ''; ?>" href="sinif-bilgisi.php">Sınıf Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'sube-bilgisi.php'   ? 'active' : ''; ?>" href="sube-bilgisi.php">Şube Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'grup-bilgisi.php'   ? 'active' : ''; ?>" href="grup-bilgisi.php">Grup Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'alan-bilgisi.php'   ? 'active' : ''; ?>" href="alan-bilgisi.php">Alan Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'il-bilgisi.php'   ? 'active' : ''; ?>" href="il-bilgisi.php">İl Bilgisi</a>
                    <a class="nav-link <?php echo $gelenveri == 'ilce-bilgisi.php'   ? 'active' : ''; ?>" href="ilce-bilgisi.php">İlçe Bilgisi</a>
                  </nav>
                  </a>
                </div>
                <!--<a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseComponents" aria-expanded="false" aria-controls="collapseComponents">
                    <div class="nav-link-icon"><i data-feather="package"></i></div>
                    Components
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseComponents" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav">
                        <a class="nav-link" href="alerts.html">Alerts</a>
                        <a class="nav-link" href="avatars.html">Avatars</a>
                        <a class="nav-link" href="badges.html">Badges</a>
                        <a class="nav-link" href="buttons.html">Buttons</a>
                        <a class="nav-link" href="cards.html">
                            Cards
                            <span class="badge bg-primary-soft text-primary ms-auto">Updated</span>
                        </a>
                        <a class="nav-link" href="dropdowns.html">Dropdowns</a>
                        <a class="nav-link" href="forms.html">
                            Forms
                            <span class="badge bg-primary-soft text-primary ms-auto">Updated</span>
                        </a>
                        <a class="nav-link" href="modals.html">Modals</a>
                        <a class="nav-link" href="navigation.html">Navigation</a>
                        <a class="nav-link" href="progress.html">Progress</a>
                        <a class="nav-link" href="step.html">Step</a>
                        <a class="nav-link" href="timeline.html">Timeline</a>
                        <a class="nav-link" href="toasts.html">Toasts</a>
                        <a class="nav-link" href="tooltips.html">Tooltips</a>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseUtilities" aria-expanded="false" aria-controls="collapseUtilities">
                    <div class="nav-link-icon"><i data-feather="tool"></i></div>
                    Utilities
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUtilities" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav">
                        <a class="nav-link" href="animations.html">Animations</a>
                        <a class="nav-link" href="background.html">Background</a>
                        <a class="nav-link" href="borders.html">Borders</a>
                        <a class="nav-link" href="lift.html">Lift</a>
                        <a class="nav-link" href="shadows.html">Shadows</a>
                        <a class="nav-link" href="typography.html">Typography</a>
                    </nav>
                </div>
                <div class="sidenav-menu-heading">Plugins</div>
                <a class="nav-link" href="muhasebe.php">
                    <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                    Muhasebe
                </a>
                <a class="nav-link" href="tables.html">
                    <div class="nav-link-icon"><i data-feather="filter"></i></div>
                    Tables
                </a> -->
            </div>
        </div>
        <div class="sidenav-footer">
            <div class="sidenav-footer-content">
                <div class="sidenav-footer-subtitle">Oturumu Açan:</div>
                <div class="sidenav-footer-title"><?= $kullanici['kisi']; ?>  /  <?= $kullanici['ad']; ?> </div>
            </div>
        </div>
    </nav>
</div>
