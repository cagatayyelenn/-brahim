<?php

include "c/fonk.php";
include "c/config.php";
include "c/user.php";
session_start();
$subeler="SELECT * FROM `sube` ORDER BY `sube`.`sube_id` ASC";
$subess=$Ydil->get($subeler);


/* Cache’i kapat (geri tuşunda RAM’den gösterilmesin) */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

/* Şube seçimi geldiyse session’a yaz ve history’yi replace et */
if (isset($_GET['sec'])) {
    $_SESSION['subedurum'] = (int)$_GET['sec'];

    // header() yerine JS ile REPLACE (geçmişe yazma)
    echo '<!doctype html><html><head><meta charset="utf-8">';
    echo '<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />';
    echo '</head><body>';
    echo '<script>location.replace("anasayfa.php");</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=anasayfa.php"></noscript>';
    echo '</body></html>';
    exit;
}

/* Şube zaten seçiliyse (geri ile gelirlerse) anasayfa’ya at */
if (!empty($_SESSION['subedurum'])) {
    header("Location: anasayfa.php", true, 302);
    exit;
}






?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Sqooler Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script data-search-pseudo-elements="" defer="" src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous"></script>
</head>
<body class="bg-primary">
<div id="layoutAuthentication">
    <div id="layoutAuthentication_content">
        <main>
            <div class="container-xl px-4">
                <div class="row justify-content-center">
                    <!-- Create Organization-->

                    <?php
                    foreach($subess as $s){
                    ?>
                    <div class="col-xl-5 col-lg-6 col-md-8 col-sm-11 mt-4">
                        <div class="card text-center h-100">
                            <div class="card-body px-5 pt-5 d-flex flex-column">
                                <div>
                                    <div class="h3 text-primary"><?php echo htmlspecialchars($s['sube_adi']); ?></div>
                                    <p class="text-muted mb-4">Bu şube için işlemleri başlatabilirsiniz.</p>
                                </div>
                                <div class="icons-org-create align-items-center mx-auto mt-auto">
                                    <i class="icon-users" data-feather="users"></i>
                                    <i class="icon-plus fas fa-plus"></i>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent px-5 py-4">
                                <div class="small text-center">
                                    <a class="btn btn-block btn-primary" href="sube.php?sec=<?php echo $s['sube_id']; ?>">
                                        <?php echo $s['sube_adi']; ?> için işlem yap
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    
                </div>
            </div>
        </main>
    </div>
    <div id="layoutAuthentication_footer">
        <footer class="footer-admin mt-auto footer-dark">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright © Your Website 2021</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="multi-tenant-select.html#!">Privacy Policy</a>
                        ·
                        <a href="multi-tenant-select.html#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
<script>
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            // BFCache’ten döndüyse zorla yenile → sunucuya gider, tekrar anasayfa yönlendirmesi çalışır
            location.reload();
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>

<script src="https://assets.startbootstrap.com/js/sb-customizer.js"></script>
</body>
</html>
