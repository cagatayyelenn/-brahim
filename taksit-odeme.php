<?php
// ogrenci-detay.php (tam sayfa)
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$ogrenci_id = (int)($_GET['ogrenci_id'] ?? 0);
if ($ogrenci_id <= 0) {
    header('Location: ogrenci-listesi.php');
    exit;
}

// Öğrenci + veli
$ogr  = $Ydil->getone("SELECT * FROM ogrenci WHERE ogrenci_id={$ogrenci_id} LIMIT 1");

$ogradi = $ogr['ogrenci_adi'] ?? '';
$ogrtel = $ogr['ogrenci_tel'] ?? '';
$dogumTarihi = $ogr['ogrenci_dogumtar'] ?? null;
$yas = '-';
if ($dogumTarihi) {
    $bugun = new DateTime();
    $dogum = new DateTime($dogumTarihi);
    $yas = $bugun->diff($dogum)->y;
}

// Kasalar
$kasa = $Ydil->get("SELECT id, ad FROM kasa ORDER BY ad ASC");

// Taksitler + sözleşme toplamları
$taksitler = $Ydil->get("
  SELECT 
    t.id, t.satis_id, t.ogrenci_id, t.taksit_tarihi, t.taksit_tutari, t.odendi,
    k.toplam_tutar, k.pesinat_tutari, k.kalan_tutar
  FROM taksitler t
  LEFT JOIN kurs_satislari k ON t.satis_id = k.id
  WHERE t.ogrenci_id = {$ogrenci_id}
  ORDER BY t.taksit_tarihi DESC, t.id DESC
");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Öğrenci Detay</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                    <div class="page-header-icon"><i data-feather="user"></i></div>
                                    <?= htmlspecialchars($ogradi) ?>
                                </h1>
                                <div class="text-muted small">
                                    Yaş: <?= htmlspecialchars($yas) ?> | Tel: <?= htmlspecialchars($ogrtel) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="container-xl px-4 mt-4">
                <nav class="nav nav-borders">
                    <a class="nav-link ms-0">Profil</a>
                    <a class="nav-link active" href="ogrenci-detay.php?ogrenci_id=<?= (int)$ogrenci_id; ?>">Ödemeler</a>
                    <a class="nav-link" href="ogrenci-sozlesme.php?ogrenci_id=<?= (int)$ogrenci_id; ?>">Sözleşmeler</a>
                </nav>
                <hr class="mt-0 mb-4" />

                <!-- ÖDEME FORMU -->
                <form id="paymentForm" method="post">
                    <input type="hidden" name="ogrenci_id" id="hidOgrenciId" value="<?= (int)$ogrenci_id ?>">
                    <input type="hidden" name="taksit_id"  id="hidTaksitId"  value="">
                    <input type="hidden" name="satis_id"   id="hidSatisId"   value="">
                    <input type="hidden" name="tutar"      id="hidTutar"     value="">

                    <div class="row gx-4">
                        <div class="col-lg-8">
                            <!-- Sözleşme Kartı -->
                            <div class="card shadow-sm border-0 rounded-3 mb-4" id="sozlesmeKart" style="display:none;">
                                <div class="card-header bg-primary text-white fw-bold">Sözleşme Tutarları</div>
                                <div class="card-body">
                                    <div class="row mb-3 mt-4">
                                        <div class="col-md-4">
                                            <label class="fw-bold text-secondary">Toplam Tutar</label>
                                            <input type="text" class="form-control text-end" name="toplamTutar" id="toplamTutar" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold text-secondary">Peşinat</label>
                                            <input type="text" class="form-control text-end" name="pesinat" id="pesinat" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold text-success">Kalan Tutar</label>
                                            <input type="text" class="form-control text-end text-success" name="kalanTutar" id="kalanTutar1" readonly>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5 class="text-primary fw-bold">
                                        <span id="odemeTarihi">—</span> tarihli ödemeyi yapıyorsunuz
                                    </h5>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="fw-bold">Taksit Tutarı</label>
                                            <input type="text" class="form-control text-end" name="taksit_tutari" id="taksittutari" readonly>
                                            <input type="hidden"   id="taksittutari">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold">Ödeme Türü</label>
                                            <select class="form-select" name="odeme_turu" id="odemeTuruSel" required>
                                                <option selected disabled value="">Ödeme Türü Seçiniz</option>
                                                <option value="1">Nakit</option>
                                                <option value="2">Kredi Kartı</option>
                                                <option value="3">Havale/EFT</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold">Kasa</label>
                                            <select name="kasa_id" id="kasa_id_view" class="form-select" required>
                                                <option selected disabled value="">Kasa Türü Seçiniz</option>
                                                <?php foreach ($kasa as $kasas): ?>
                                                    <option value="<?= (int)$kasas['id']; ?>"><?= htmlspecialchars($kasas['ad']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3 align-items-end">
                                        <div class="col-md-6">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" id="flexCheckDefault" type="checkbox" value="1">
                                                <label class="form-check-label" for="flexCheckDefault">Farklı Tutar Ödemek İstiyorum</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold text-secondary">Tutar Giriniz</label>
                                            <input type="text" class="form-control text-end" name="farkliTutar" id="farkliTutar" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Taksit Tablosu -->
                            <div class="card card-header-actions mb-4">
                                <div class="card-header">Taksit Ödeme Tablosu</div>
                                <div class="card-body p-0">
                                    <div class="table-responsive table-billing-history">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Sözleşme ID</th>
                                                <th>Tarih</th>
                                                <th>Tutar</th>
                                                <th>Durum</th>
                                                <th class="text-end">İşlem</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (empty($taksitler)): ?>
                                                <tr><td colspan="5" class="text-center text-muted">Ödenmemiş taksit bulunmamaktadır.</td></tr>
                                            <?php else: foreach ($taksitler as $t):
                                                $fno   = '#'.(int)$t['satis_id'];
                                                $tarih = date('d.m.Y', strtotime($t['taksit_tarihi']));
                                                $tutar = number_format((float)$t['taksit_tutari'], 2, ',', '.').' TL';
                                                $gecmisMi = (strtotime($t['taksit_tarihi']) < strtotime(date('Y-m-d')));
                                                $durum = $t['odendi'] ? '<span class="badge bg-success">Ödendi</span>'
                                                    : ($gecmisMi ? '<span class="badge bg-danger">Vadesi Geçmiş</span>'
                                                        : '<span class="badge bg-secondary">Beklemede</span>');
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($fno) ?></td>
                                                    <td><?= htmlspecialchars($tarih) ?></td>
                                                    <td><?= htmlspecialchars($tutar) ?></td>
                                                    <td><?= $durum ?></td>
                                                    <td class="text-end">
                                                        <?php if(!(int)$t['odendi']): ?>
                                                            <a href="#"
                                                               class="btn btn-sm btn-outline-primary btn-pay"
                                                               data-satis-id="<?= (int)$t['satis_id'] ?>"
                                                               data-taksit-id="<?= (int)$t['id'] ?>"
                                                               data-tarih="<?= htmlspecialchars($tarih) ?>"
                                                               data-tutar="<?= (float)$t['taksit_tutari'] ?>"
                                                               data-toplam="<?= (float)($t['toplam_tutar'] ?? 0) ?>"
                                                               data-pesinat="<?= (float)($t['pesinat_tutari'] ?? 0) ?>"
                                                               data-kalan="<?= (float)($t['kalan_tutar'] ?? 0) ?>">
                                                                Öde
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ödeme Kartı -->
                        <div class="col-lg-4" id="odemeKart" style="display:none;">
                            <div class="card card-header-actions">
                                <div class="card-header bg-primary text-white fw-bold">Ödeme Yapın</div>
                                <div class="card-body">
                                    <div class="d-grid mb-3">
                                        <button type="button" class="fw-500 btn btn-primary-soft text-primary" id="btnIptal">İptal</button>
                                    </div>
                                    <div class="d-grid">
                                        <button type="button" class="fw-500 btn btn-primary" id="btnOdemeYap">Ödeme Yap</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /row -->
                    
                </form>
            </div>
        </main>

        <footer class="footer-admin mt-auto footer-light">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a>&middot;<a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const fmtTL = n => new Intl.NumberFormat('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n))+' TL';
    const parseMoney = (raw)=>{
        if(raw==null) return 0;
        let s=String(raw).trim();
        if(!s) return 0;
        s=s.replace(/\s/g,'').replace(/\./g,'').replace(/,/g,'.');
        const v=Number(s);
        return Number.isFinite(v)?v:0;
    };

    (function(){
        const btnOdeme = document.getElementById('btnOdemeYap');
        const sozlesmeKart = document.getElementById('sozlesmeKart');
        const odemeKart    = document.getElementById('odemeKart');

        const inpToplam  = document.getElementById('toplamTutar');
        const inpPesinat = document.getElementById('pesinat');
        const inpKalan   = document.getElementById('kalanTutar1');
        const inpTaksit  = document.getElementById('taksittutari');
        const spnTarih   = document.getElementById('odemeTarihi');

        const selOdemeTuru = document.getElementById('odemeTuruSel');
        const selKasa      = document.getElementById('kasa_id_view');

        const hidOgrenciId = document.getElementById('hidOgrenciId');
        const hidTaksitId  = document.getElementById('hidTaksitId');
        const hidSatisId   = document.getElementById('hidSatisId');
        const hidTutar     = document.getElementById('hidTutar');

        const chkDifferent = document.getElementById('flexCheckDefault');
        const inpFarkli    = document.getElementById('farkliTutar');

        // Başta butonu kilitle
        btnOdeme.disabled = true;

        // Taksit seçilince alanları doldur + butonu aç
        document.querySelectorAll('.btn-pay').forEach(btn=>{
            btn.addEventListener('click', e=>{
                e.preventDefault();
                const d = btn.dataset;

                hidTaksitId.value = d.taksitId;
                hidSatisId.value  = d.satisId;
                hidTutar.value    = d.tutar;

                inpToplam.value  = fmtTL(d.toplam||0);
                inpPesinat.value = fmtTL(d.pesinat||0);
                inpKalan.value   = fmtTL(d.kalan||0);
                inpTaksit.value  = fmtTL(d.tutar||0);
                spnTarih.textContent = d.tarih||'—';

                chkDifferent.checked = false;
                inpFarkli.readOnly   = true;
                inpFarkli.value      = '';

                sozlesmeKart.style.display = 'block';
                odemeKart.style.display    = 'block';

                btnOdeme.disabled = false; // burada aç
            });
        });

        document.getElementById('btnIptal')?.addEventListener('click', e=>{
            e.preventDefault();
            sozlesmeKart.style.display = 'none';
            odemeKart.style.display    = 'none';
            btnOdeme.disabled = true; // iptal edilince tekrar kilitle
        });

        chkDifferent?.addEventListener('change', ()=>{
            if (chkDifferent.checked) {
                inpFarkli.readOnly = false;
                inpFarkli.value    = '';
                inpTaksit.value    = fmtTL(0);
            } else {
                inpFarkli.readOnly = true;
                inpFarkli.value    = '';
                inpTaksit.value    = fmtTL(parseMoney(hidTutar.value||'0'));
            }
        });

        // ÖDEME YAP
        btnOdeme.addEventListener('click', async (e)=>{

            
            e.preventDefault();

            // 1) Zorunlu alan kontrolleri (erken çık)
            if (!hidTaksitId.value) {
                return Swal.fire({icon:'info', title:'Taksit seçin', text:'Öde butonlarından bir taksit seçmelisiniz.'});
            }
            if (!selOdemeTuru.value) {
                return Swal.fire({icon:'info', title:'Ödeme türü gerekli', text:'Ödeme türünü seçin.'});
            }
            if (!selKasa.value) {
                return Swal.fire({icon:'info', title:'Kasa gerekli', text:'Kasa seçin.'});
            }

            // 2) Tutarı belirle
            const farkliVal = parseMoney(inpFarkli?.value ?? '');
            const origVal   = parseMoney(hidTutar?.value ?? '0');
            const odeme_tutar = (farkliVal>0 ? farkliVal : origVal);

            if (odeme_tutar <= 0) {
                return Swal.fire({icon:'warning', title:'Geçersiz tutar', text:'Lütfen geçerli bir tutar girin (örn: 6000 veya 6.000,00).'});
            }

            // 3) Gönder
            const fd = new FormData();
            fd.append('ogrenci_id',  hidOgrenciId.value);
            fd.append('taksit_id',   hidTaksitId.value);
            fd.append('satis_id',    hidSatisId.value);
            fd.append('kasa_id',     selKasa.value);
            fd.append('odeme_turu',  selOdemeTuru.value);
            fd.append('odeme_tutar', odeme_tutar);
            const chkFarkliTutar = document.getElementById('flexCheckDefault');
            fd.append('farkli_tutar', chkFarkliTutar.checked ? 1 : 0);

            const res = await fetch('odeme_isle.php', { method:'POST', body: fd });
            const raw = await res.text();
            let j; try { j = JSON.parse(raw); } catch { j = {status:0, message:raw}; }

            if (String(j.status)==='1') {
                Swal.fire({icon:'success', title:'Ödeme Başarılı'}).then(()=>location.reload());
            } else {
                Swal.fire({icon:'error', title:'Başarısız', text: j.message || 'Ödeme alınamadı.'});
            }
        });
    })();
</script>
</body>
</html>