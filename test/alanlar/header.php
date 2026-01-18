<?php
if (!isset($pageTitle)) {
    $pageTitle = "";
}
const SITE_NAME = "Sqooler Yönetim Sistemi";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Sqooler yönetim sistemi">
    <meta name="author" content="Yabancı Dil Dünyası">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $pageTitle . " - " . SITE_NAME; ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <!-- <script src="assets/js/theme-script.js" type="text/javascript"></script> -->

    <?php if (!empty($page_styles) && is_array($page_styles)): ?>
        <?php foreach ($page_styles as $s):
            $href = htmlspecialchars($s['href'] ?? '', ENT_QUOTES, 'UTF-8');
            if (!$href)
                continue;
            $media = isset($s['media']) ? ' media="' . htmlspecialchars($s['media'], ENT_QUOTES, 'UTF-8') . '"' : '';
            ?>
            <link rel="stylesheet" href="<?= $href ?>" <?= $media ?>>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($page_inline_styles)): ?>
        <style>
            <?= $page_inline_styles ?>
        </style>
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/icons/feather/feather.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.45.0/tabler-icons.min.css">
    <link rel="stylesheet" href="assets/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- SweetAlert2 (tek referans) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (!empty($_SESSION['flash_swal'])):
        $f = $_SESSION['flash_swal'];
        unset($_SESSION['flash_swal']); ?>
        <script>
            (function () {
                function show() {
                    if (!window.Swal) { console.error('SweetAlert2 yüklenemedi'); return; }
                    Swal.fire({
                        icon: '<?= htmlspecialchars($f['icon'] ?? 'info', ENT_QUOTES) ?>',
                        title: '<?= htmlspecialchars($f['title'] ?? '', ENT_QUOTES) ?>',
                        text: '<?= htmlspecialchars($f['text'] ?? '', ENT_QUOTES) ?>'
                    }).then(function () {
                        <?php if (!empty($f['redirect'])): ?>
                            location.href = '<?= htmlspecialchars($f['redirect'], ENT_QUOTES) ?>';
                        <?php endif; ?>
                    });
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', show);
                } else {
                    show();
                }
            })();
        </script>
    <?php endif; ?>

    <style>
        .icon-addon {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            padding-right: 15px;
        }

        .input-icon-start .form-control {
            padding-left: 2.25rem
        }

        .thead-light th {
            background: #f8f9fa
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left active">
                <a href="index.php" class="logo logo-normal">
                    <img src="assets/img/logo.svg" alt="Logo">
                </a>
                <a href="index.php" class="logo-small">
                    <img src="assets/img/logo-small.svg" alt="Logo">
                </a>
                <a href="index.php" class="dark-logo">
                    <img src="assets/img/logo-dark.svg" alt="Logo">
                </a>
                <a id="toggle_btn" href="javascript:void(0);">
                    <i class="ti ti-menu-deep"></i>
                </a>
            </div>
            <!-- /Logo -->

            <a id="mobile_btn" class="mobile_btn" href="index.php#sidebar">
                <span class="bar-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </a>

            <div class="header-user">
                <div class="nav user-menu">
                    <div class="nav-item nav-search-inputs me-auto">

                    </div>
                    <div class="d-flex align-items-center">
                        <!-- Session Timer -->
                        <div class="me-2">
                            <span class="btn btn-outline-light fw-normal bg-white d-flex align-items-center p-2"
                                title="Oturum Süresi">
                                <i class="ti ti-clock me-1"></i><span id="sessionTimer">30:00</span>
                            </span>
                        </div>

                        <div class="dropdown me-2">
                            <a href="#" class="btn btn-outline-light fw-normal bg-white d-flex align-items-center p-2"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-calendar-due me-1"></i>Dönem : 2024 / 2025
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
                                    Dönem : 2023 / 2024
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
                                    Dönem : 2022 / 2023
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">
                                    Dönem : 2021 / 2022
                                </a>
                            </div>
                        </div>
                        <div class="pe-1 ms-1">
                            <div class="dropdown">
                                <a href="#"
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
                                <a href="#" class="btn btn-outline-light bg-white btn-icon me-1"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-square-rounded-plus"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right border shadow-sm dropdown-md">
                                    <div class="p-3 border-bottom">
                                        <h5>Ekleme İşlemi</h5>
                                    </div>
                                    <div class="p-3 pb-0">
                                        <div class="row gx-2">
                                            <div class="col-6">
                                                <a href="ogrenci-ekle.php"
                                                    class="d-block bg-primary-transparent ronded p-2 text-center mb-3 class-hover">
                                                    <div class="avatar avatar-lg mb-2">
                                                        <span
                                                            class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-primary rounded-circle"><i
                                                                class="ti ti-school"></i></span>
                                                    </div>
                                                    <p class="text-dark">Öğrenci</p>
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
                                                    <p class="text-dark">Sözleşme</p>
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
                                                    <p class="text-dark">Taksit</p>
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="kasa1.php"
                                                    class="d-block bg-info-transparent ronded p-2 text-center mb-3 class-hover">
                                                    <div class="avatar avatar-lg mb-2">
                                                        <span
                                                            class="d-inline-flex align-items-center justify-content-center w-100 h-100 bg-info rounded-circle"><i
                                                                class="ti ti-license"></i></span>
                                                    </div>
                                                    <p class="text-dark">Gelir - Gider</p>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pe-1" id="notification_item">
                            <a href="#" class="btn btn-outline-light bg-white btn-icon position-relative me-1"
                                id="notification_popup">
                                <i class="ti ti-bell"></i>
                                <span class="notification-status-dot"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown p-4">
                                <div
                                    class="d-flex align-items-center justify-content-between border-bottom p-0 pb-3 mb-3">
                                    <h4 class="notification-title">Bildirimler (2)</h4>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="text-primary fs-15 me-3 lh-1">Tümünü okundu işaretle</a>
                                        <div class="dropdown">
                                            <a href="javascript:void(0);" class="bg-white dropdown-toggle"
                                                data-bs-toggle="dropdown"><i class="ti ti-calendar-due me-1"></i>Bugün
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
                                                        <p class="mb-1"><span
                                                                class="text-dark fw-semibold">Gösterilen</span>
                                                            performans
                                                            eşiğin altında.</p>
                                                        <span>Şuanda</span>
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
                                                                class="text-dark fw-semibold">Sylvia</span> eklenen
                                                            randevu
                                                            14:00'te</p>
                                                        <span>10 dakika önce</span>
                                                        <div
                                                            class="d-flex justify-content-start align-items-center mt-1">
                                                            <span class="btn btn-light btn-sm me-2">Deny</span>
                                                            <span class="btn btn-primary btn-sm">Onayla</span>
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
                                                        <p class="mb-1">Yeni öğrenci kaydı <span
                                                                class="text-dark fw-semibold"> George</span> oluşturuldu
                                                            <span class="text-dark fw-semibold"> Teressa</span>
                                                        </p>
                                                        <span>2 saat önce</span>
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
                                                        <p class="mb-1">Yeni öğretmen kaydı için <span
                                                                class="text-dark fw-semibold">Elisa</span>
                                                        </p>
                                                        <span>09:45'te</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex p-0">
                                    <a href="ogrenci-ekle.php#" class="btn btn-light w-100 me-2">İptal Et</a>
                                    <a href="activities.html" class="btn btn-primary w-100">Hepsini Görüntüle</a>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown ms-1">
                            <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <span class="avatar avatar-md rounded">
                                    <img src="https://ui-avatars.com/api/?name=<?= $user['ad'] ?>" alt="img"
                                        class="img-fluid">
                                </span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="d-block">
                                    <div class="d-flex align-items-center p-2">
                                        <span class="avatar avatar-md me-2 online avatar-rounded">
                                            <img src="https://ui-avatars.com/api/?name=<?= $user['ad'] ?>" alt="img">
                                        </span>
                                        <div>
                                            <h6 class=""><?= htmlspecialchars($user['ad'] ?? 'Kullanıcı') ?></h6>
                                            <p class="text-primary mb-0">
                                                <?= $rol = ($user['kisi_yetki'] == 1) ? 'Admin' : 'Personel'; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <hr class="m-0">

                                    <hr class="m-0">
                                    <a class="dropdown-item d-inline-flex align-items-center p-2" href="cikis.php"><i
                                            class="ti ti-login me-2"></i>Çıkış</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="dropdown mobile-user-menu">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="cikis.php">Çıkış</a>
                </div>
            </div>

        </div>

        <!-- Session Timeout Modal -->
        <div class="modal fade" id="session-timeout-modal" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-5">
                        <div class="avatar avatar-xl bg-danger-transparent mb-4">
                            <span class="avatar-title rounded-circle">
                                <i class="ti ti-clock-exclamation fs-36 text-danger"></i>
                            </span>
                        </div>
                        <h3 class="mb-2">Oturum Süresi Doluyor!</h3>
                        <p class="text-muted mb-4">
                            Hiçbir işlem yapmadığınız için oturumunuz
                            <span id="timeout-countdown" class="fw-bold text-dark fs-18 mx-1">10</span>
                            saniye içinde kapatılacak.
                        </p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" id="btn-extend-session">
                                <i class="ti ti-reload me-2"></i>Oturum Süresini Uzat
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Persistent Session Timer Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const SESSION_DURATION_MS = 60 * 1000;      // TEST İÇİN 1 Dakika (Normalde 30 * 60 * 1000)
                const WARNING_DURATION_SEC = 10;            // Son 10 saniye uyarı

                const timerDisplay = document.getElementById('sessionTimer');
                const modalEl = document.getElementById('session-timeout-modal');
                const countdownEl = document.getElementById('timeout-countdown');
                const extendBtn = document.getElementById('btn-extend-session');

                let modalInstance = null;
                let warningInterval = null;

                // localStorage anahtarını belirle (oturum bazlı olması için user_id vb eklenebilir ama şu an basit tutuyoruz)
                const STORE_KEY = 'session_expiry_time';

                // Helper: Zamanı formatla MM:SS
                function formatTime(ms) {
                    if (ms < 0) ms = 0;
                    const totalSec = Math.floor(ms / 1000);
                    const m = Math.floor(totalSec / 60);
                    const s = totalSec % 60;
                    return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                }

                // Hedef zamanı al veya oluştur
                function getExpiryTime() {
                    let expiry = localStorage.getItem(STORE_KEY);
                    if (!expiry || parseInt(expiry) < Date.now()) {
                        // Eğer kayıt yoksa veya süre çoktan dolmuşsa (ve kullanıcı hala buradaysa) yeni süre başlat
                        // (Login sonrası ilk yükleme veya süresi dolmuş ama oturum kapanmamışsa)
                        expiry = setExpiryTime();
                    }
                    return parseInt(expiry);
                }

                function setExpiryTime() {
                    const expiry = Date.now() + SESSION_DURATION_MS;
                    localStorage.setItem(STORE_KEY, expiry);
                    return expiry;
                }

                // Süreyi uzatma fonksiyonu
                function extendSession() {
                    setExpiryTime();
                    // Modalı kapat
                    if (modalInstance) modalInstance.hide();
                    // Uyarı interval'i temizle
                    if (warningInterval) {
                        clearInterval(warningInterval);
                        warningInterval = null;
                    }
                    // Sayaç hemen güncellensin
                    updateTimer();
                }

                // Buton click eventi
                if (extendBtn) {
                    extendBtn.addEventListener('click', extendSession);
                }

                // Sayaç döngüsü
                function updateTimer() {
                    const expiry = getExpiryTime();
                    const now = Date.now();
                    const remaining = expiry - now;

                    // Display güncelle
                    if (timerDisplay) {
                        timerDisplay.textContent = formatTime(remaining);
                    }

                    // Süre doldu mu (tamamen)
                    if (remaining <= 0) {
                        // Çıkış yap
                        localStorage.removeItem(STORE_KEY);
                        window.location.href = 'cikis.php';
                        return;
                    }

                    // Son 10 saniye mi? Modalı göster
                    // (remaining <= 11000 çünkü 10000ms altında modalda geri sayım daha doğru görünsün)
                    if (remaining <= (WARNING_DURATION_SEC * 1000) + 1000) {
                        if (!modalInstance && window.bootstrap) {
                            modalInstance = new bootstrap.Modal(modalEl);
                            modalInstance.show();
                        }

                        // Modal içindeki geri sayımı güncelle
                        if (countdownEl) {
                            const secLeft = Math.ceil(remaining / 1000);
                            countdownEl.textContent = secLeft;
                        }
                    }
                }

                // Eğer sayfa ilk açıldığında hedef süreye 30 dakikadan fazla varsa (saat değişimi vs) resetle
                // Veya çoktan dolmuşsa (örn tarayıcı kapalı kaldı)
                // updateTimer içinde zaten kontrol ediliyor ama...

                // Başlat
                // İlk okumada değer yoksa (login sonrası ilk hit) set eder.
                // localStorage'da kayıtlı değer varsa onu kullanır (sayfa yenilemede resetlenmez).

                // Eğer kullanıcı manuel olarak çıkış yaptıysa bu değeri temizlememiz lazım.
                // `cikis.php`ye gidince JS çalışmaz. O yüzden en iyisi login sayfasında temizlemektir.
                // Ama şimdilik basit mantıkla ilerliyoruz.

                setInterval(updateTimer, 1000);
                updateTimer(); // İlk çağrı
            });
        </script>