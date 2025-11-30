<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";
 
$kasas="SELECT * FROM `kasa` ORDER BY `kasa`.`id` ASC";
$kasa=$Ydil->get($kasas);

$hturs="SELECT * FROM `kasa_hareket_turleri` ORDER BY `kasa_hareket_turleri`.`tur_id` ASC";
$htur=$Ydil->get($hturs);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Personel Kayıt Alanı - Sqooler</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="js/sweetalert2.all.min.js"></script>
</head>
<body class="nav-fixed">
<?php include 'ekler/sidebar.php'; ?>
<div id="layoutSidenav">
    <?php include 'ekler/menu.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
                <div class="container-xl px-4">
                    <div class="page-header-content">
                        <div class="row align-items-center justify-content-between pt-3">
                            <div class="col-auto mb-3">
                                <h1 class="page-header-title">
                                    <div class="page-header-icon"><i data-feather="user-plus"></i></div>
                                    Kasa Kayıt
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Main page content-->
            <div class="container-xl px-4 mt-4">
                <div class="row">
                    <div class="col-xl-4 mb-4">
                        <a class="card lift h-100" href="javascript:void(0)" id="btnKasaGiris">
                            <div class="card-body d-flex justify-content-center flex-column">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="me-3">
                                        <i class="feather-xl text-primary mb-3" data-feather="package"></i>
                                        <h5>Kasa Girişi Yapın</h5>
                                        <div class="text-muted small">Kasa girişi işlemlerini başlatın</div>
                                    </div>
                                    <img src="assets/img/illustrations/browser-stats.svg" alt="..." style="width: 8rem" />
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-4 mb-4">
                        <a class="card lift h-100" href="javascript:void(0)" id="btnKasaCikis">
                            <div class="card-body d-flex justify-content-center flex-column">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="me-3">
                                        <i class="feather-xl text-secondary mb-3" data-feather="book"></i>
                                        <h5>Kasa Çıkışı Yapın</h5>
                                        <div class="text-muted small">Kasa çıkışı işlemlerini başlatın</div>
                                    </div>
                                    <img src="assets/img/illustrations/processing.svg" alt="..." style="width: 8rem" />
                                </div>
                            </div>
                        </a>
                    </div>
 
                </div>
                <div class="container-fluid px-0" id="kasaGirisForm" style="display:none;">
                    <form id="kasaForm">
                        <div class="row gx-4">
                            <div class="col-lg-8">
                                <div class="card shadow-sm border-0 rounded-3 mb-4">
                                    <div class="card-header bg-primary text-white fw-bold">Kasa Giriş İşlemleri</div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Hareket Türü</label>
                                                <select class="form-select" name="kasa_turu" id="kasaTuru" required>
                                                    <option selected disabled value="">Kasa Türü Seçiniz</option>
                                                    <?php
                                                    if(!empty($htur)){
                                                        foreach($htur as $row){
                                                            echo '<option value="'.$row['tur_id'].'">'.htmlspecialchars($row['tur_adi']).'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Tarih</label>
                                                <input type="date" class="form-control" name="tarih" id="tarih" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Tutar</label>
                                                <input type="number" class="form-control text-end" name="tutar" id="tutar" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="fw-bold">Ödeme Türü</label>
                                                <select class="form-select" name="odeme_turu" id="odemeTuru" required>
                                                    <option selected disabled value="">Ödeme Türü Seçiniz</option>
                                                    <option value="nakit">NAKİT</option>
                                                    <option value="kredikarti">KREDİ KARTI</option>
                                                    <option value="bankahavalesi">BANKA HAVALESİ</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="fw-bold">Açıklama</label>
                                                <textarea class="form-control" name="aciklama" id="aciklama" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SAĞ: Ödeme Yapın -->
                            <div class="col-lg-4" id="odemeKart">
                                <div class="card card-header-actions">
                                    <div class="card-header bg-primary text-white fw-bold">Kasa Girişi Yap</div>
                                    <div class="card-body">
                                        <div class="d-grid mb-3">
                                            <button type="button" class="fw-500 btn btn-primary-soft text-primary" id="btnIptal">İptal</button>
                                        </div>
                                        <div class="d-grid">
                                            <button type="button" class="fw-500 btn btn-primary" id="btnOdemeYap">Kaydet</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- echo çıktısı buraya basılacak -->
                    <div id="sonuc" class="mt-4"></div>
                </div>

                <div class="container-fluid px-0" id="kasaCikisForm" style="display:none;">
                    <form id="cikisForm">
                        <div class="row gx-4">
                            <div class="col-lg-8">
                                <div class="card shadow-sm border-0 rounded-3 mb-4">
                                    <div class="card-header bg-secondary text-white fw-bold">Kasa Çıkış İşlemleri</div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Hareket Türü</label>
                                                <select class="form-select" name="kasa_turu" id="kasaTuru" required>
                                                    <option selected disabled value="">Kasa Türü Seçiniz</option>
                                                    <?php
                                                    if(!empty($htur)){
                                                        foreach($htur as $row){
                                                            echo '<option value="'.$row['tur_id'].'">'.htmlspecialchars($row['tur_adi']).'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Tarih</label>
                                                <input type="date" class="form-control" name="tarih" id="cikisTarih" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Tutar</label>
                                                <input type="number" class="form-control text-end" name="tutar" id="cikisTutar" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="fw-bold">Ödeme Türü</label>
                                                <select class="form-select" name="odeme_turu" id="cikisOdemeTuru" required>
                                                    <option selected disabled value="">Ödeme Türü Seçiniz</option>
                                                    <option value="nakit">NAKİT</option>
                                                    <option value="kredikarti">KREDİ KARTI</option>
                                                    <option value="bankahavalesi">BANKA HAVALESİ</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="fw-bold">Açıklama</label>
                                                <textarea class="form-control" name="aciklama" id="cikisAciklama" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SAĞ: Çıkış Yap -->
                            <div class="col-lg-4">
                                <div class="card card-header-actions">
                                    <div class="card-header bg-secondary text-white fw-bold">Kasa Çıkış Yap</div>
                                    <div class="card-body">
                                        <div class="d-grid mb-3">
                                            <button type="button" class="fw-500 btn btn-secondary-soft text-secondary" id="btnCikisIptal">İptal</button>
                                        </div>
                                        <div class="d-grid">
                                            <button type="button" class="fw-500 btn btn-secondary" id="btnCikisYap">Kaydet</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Çıktı -->
                    <div id="cikisSonuc" class="mt-4"></div>
                </div>
            </div>
        </main>
        <footer class="footer-admin mt-auto footer-light">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website 2021</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a>
                        &middot;
                        <a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
<script>
    document.getElementById("btnKasaGiris").addEventListener("click", function(){
        document.getElementById("kasaGirisForm").style.display = "block";
        document.getElementById("kasaCikisForm").style.display = "none";
    });

    document.getElementById("btnKasaCikis").addEventListener("click", function(){
        document.getElementById("kasaCikisForm").style.display = "block";
        document.getElementById("kasaGirisForm").style.display = "none";
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        async function kasaGonder(formId, islemTipi){
            try {
                const form = document.getElementById(formId);
                if (!form) {
                    throw new Error(`Form bulunamadı: #${formId}`);
                }

                const fd = new FormData(form);
                fd.append('islem_tipi', islemTipi);

                const res = await fetch('kasa_islem.php', {
                    method: 'POST',
                    body: fd,
                    // same-origin olduğu için cookie’ler varsayılan olarak gider.
                });

                if (!res.ok) {
                    const txt = await res.text().catch(()=>'');
                    throw new Error(`Sunucu hatası (${res.status}): ${txt || 'Bilinmiyor'}`);
                }

// JSON bekle
                let data;
                try {
                    data = await res.json();
                } catch (e) {
                    // Eski 1/0 formatı gelirse geri uyumluluk
                    const raw = (await res.text()).trim();
                    data = { ok: raw === '1', message: raw };
                }

                const ok = !!data.ok;

                const title = ok ? "Başarılı" : "Başarısız";
                const text  = ok ? (data.message || "İşlem başarılı") : (data.message || "İşlem başarısız");
                const icon  = ok ? "success" : "error";

                if (window.Swal && Swal.fire) {
                    await Swal.fire({ title, text, icon, confirmButtonText: "Tamam" });
                } else {
                    alert(`${title}: ${text}`);
                }

                window.location.reload();

            } catch (err) {
                console.error(err);
                const msg = (err && err.message) ? err.message : "Beklenmeyen hata";
                if (window.Swal && Swal.fire) {
                    Swal.fire({
                        title: "Hata",
                        text: msg,
                        icon: "error",
                        confirmButtonText: "Tamam"
                    }).then(() => {
                        // Hata sonrası sayfayı yenilemek zorunda değilsiniz; isterseniz bırakın:
                        // window.location.reload();
                    });
                } else {
                    alert(`Hata: ${msg}`);
                }
            }
        }

        // Kasa GİRİŞ
        const btnOdeme = document.getElementById('btnOdemeYap');
        if (btnOdeme) {
            btnOdeme.addEventListener('click', (e) => {
                e.preventDefault();
                kasaGonder('kasaForm', 'giris');
            });
        }

        // Kasa ÇIKIŞ
        const btnCikis = document.getElementById('btnCikisYap');
        if (btnCikis) {
            btnCikis.addEventListener('click', (e) => {
                e.preventDefault();
                kasaGonder('cikisForm', 'cikis');
            });
        }

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="js/scripts.js"></script>
</body>
</html>
