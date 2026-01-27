<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="row">

            <!-- Page Header -->
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
                                <li class="breadcrumb-item active" aria-current="page">Detaylı Bilgiler</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center  flex-wrap">
                        <a href="student-details.html#" class="btn btn-light me-2 mb-2" data-bs-toggle="modal" data-bs-target="#login_detail"><i class="ti ti-lock me-2"></i>Panel Giriş Bilgileri</a>
                        <a href="ogrenci-duzenle.php?id=<?= $ogrenci['ogrenci_numara'] ?>" class="btn btn-primary d-flex align-items-center mb-2"><i class="ti ti-edit-circle me-2"></i>Öğrenciyi Düzenle</a>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

        </div>

        <div class="row">

            <div class="col-xxl-3 col-xl-4  ">
                <div class="card border-white">
                    <div class="card-header">
                        <div class="d-flex align-items-center flex-wrap row-gap-3">
                            <div class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                <img src="https://ui-avatars.com/api/?name=<?= $ogrenci['ogrenci_adi'] ?>+<?= $ogrenci['ogrenci_soyadi'] ?>" class="img-fluid" alt="img">
                            </div>
                            <div class="overflow-hidden">
                                <?php
                                if($ogrenci['aktif'] == 1){ echo '<span class="badge badge-soft-success"><i class="ti ti-circle-filled"></i> Aktif</span>'; }
                                else{ echo '<span class="badge badge-soft-secondary"><i class="ti ti-circle-filled"></i> Pasif</span>';}

                                ?>
                                <h5 class="mb-1 text-truncate"><?= $ogrenci['ogrenci_adi']." ".$ogrenci['ogrenci_soyadi'] ?></h5>
                                <p class="text-primary"><?= $ogrenci['ogrenci_numara'] ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <h5 class="mb-3">Öğrenci bilgileri</h5>
                        <?php
                        $adSoyad   = trim(($ogrenci['ogrenci_adi'] ?? '').' '.($ogrenci['ogrenci_soyadi'] ?? ''));
                        $cinsiyetT = ($ogrenci['ogrenci_cinsiyet'] === '1' || $ogrenci['ogrenci_cinsiyet'] === 1) ? 'Erkek'
                            : (($ogrenci['ogrenci_cinsiyet'] === '0' || $ogrenci['ogrenci_cinsiyet'] === 0) ? 'Kız' : '-');
                        ?>
                        <dl class="row mb-0">
                            <dt class="col-6 fw-medium text-dark mb-3">T.C. Kimlik</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($ogrenci['ogrenci_tc'] ?? '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Ad Soyad</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($adSoyad ?: '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Cinsiyet</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($cinsiyetT) ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Doğum Tarihi</dt>
                            <dd class="col-6 mb-3"><?=  formatDateTR($ogrenci['ogrenci_dogumtar']); ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Telefon</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($ogrenci['ogrenci_tel'] ?? '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">E-posta</dt>
                            <dd class="col-6 mb-3"><?= htmlspecialchars($ogrenci['ogrenci_mail'] ?? '-') ?></dd>
                            <dt class="col-6 fw-medium text-dark mb-3">İl / İlçe</dt>
                            <dd class="col-6 mb-3">
                                <?= htmlspecialchars($ogrenci['il_adi'] ?? '-') ?> /
                                <?= htmlspecialchars($ogrenci['ilce_adi'] ?? '-') ?>
                            </dd>
                            <dt class="col-6 fw-medium text-dark mb-3">Adres</dt>
                            <dd class="col-6 mb-3"><?= nl2br(htmlspecialchars($ogrenci['ogrenci_adres'] ?? '-')) ?></dd>
                        </dl>
                    </div>
                </div>


                <div class="card border-white">
                    <div class="card-body">
                        <h5 class="mb-3">Sözleşme Bilgileri</h5>
                        <dl class="row mb-0">
                            <!-- SÖZLEŞME ÖZETİ -->
                            <dt class="col-6 fw-medium text-dark mb-3">Sözleşme</dt>
                            <dd class="col-6 mb-3">
                                <?php if ($sozlesme['sozlesme_sayisi'] > 0): ?>
                                    <span class="badge bg-success">Var (<?= $sozlesme['sozlesme_sayisi'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Yok</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Toplam Taksit</dt>
                            <dd class="col-6 mb-3"><?= $sozlesme['toplam_taksit'] ?></dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Ödenen Taksit</dt>
                            <dd class="col-6 mb-3"><?= $sozlesme['odenen_taksit'] ?></dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Gecikmiş Taksit</dt>
                            <dd class="col-6 mb-3">
                                <?= $sozlesme['gecikmis_taksit'] ?>
                                <?php if ($sozlesme['gecikmis_taksit'] > 0): ?>
                                    <a href="ogrenci-detay-sozlesme.php?id=<?= $ogr_no ?>"
                                       class="btn btn-sm btn-outline-danger ms-2">
                                        Öde
                                    </a>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-6 fw-medium text-dark mb-3">Kalan Taksit</dt>
                            <dd class="col-6 mb-3"><?= $sozlesme['kalan_taksit'] ?></dd>
                        </dl>

                        <?php if ($sozlesme['sozlesme_sayisi'] === 0): ?>
                            <a href="sozlesme-olustur.php?id=<?= $ogr_no ?>" class="btn btn-primary btn-sm w-100 mt-3">
                                <i class="ti ti-file-plus me-2"></i>Sözleşme Oluştur
                            </a>
                        <?php else: ?>
                            <a href="ogrenci-detay-sozlesme.php?id=<?= $ogr_no ?>" class="btn btn-primary btn-sm w-100 mt-3">
                                Taksit Ödeme
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- <div class="card border-white">
                     <div class="card-body pb-1">
                         <ul class="nav nav-tabs nav-tabs-bottom mb-3">
                             <li class="nav-item"><a class="nav-link active" href="student-details.html#hostel" data-bs-toggle="tab">Hostel</a></li>
                             <li class="nav-item"><a class="nav-link" href="student-details.html#transport" data-bs-toggle="tab">Transportation</a></li>
                         </ul>
                         <div class="tab-content">
                             <div class="tab-pane fade show active" id="hostel">
                                 <div class="d-flex align-items-center mb-3">
                                     <span class="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default"><i class="ti ti-building-fortress fs-16"></i></span>
                                     <div>
                                         <h6 class="fs-14 mb-1">HI-Hostel, Floor</h6>
                                         <p class="text-primary">Room No : 25</p>
                                     </div>
                                 </div>
                             </div>
                             <div class="tab-pane fade" id="transport">
                                 <div class="d-flex align-items-center mb-3">
                                     <span class="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default"><i class="ti ti-bus fs-16"></i></span>
                                     <div>
                                         <span class="fs-12 mb-1">Route</span>
                                         <p class="text-dark">Newyork</p>
                                     </div>
                                 </div>
                                 <div class="row">
                                     <div class="col-sm-6">
                                         <div class="mb-3">
                                             <span class="fs-12 mb-1">Bus Number</span>
                                             <p class="text-dark">AM 54548</p>
                                         </div>
                                     </div>
                                     <div class="col-sm-6">
                                         <div class="mb-3">
                                             <span class="fs-12 mb-1">Pickup Point</span>
                                             <p class="text-dark">Cincinatti</p>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
                  /Transport Information -->

            </div>