<?php 
ob_start(); 
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

$sql = "SELECT p.personel_id, CONCAT(p.personel_adi,' ',p.personel_soyadi) AS adsoyad, p.personel_tel, COALESCE(s.sube_adi, '—') AS sube_adi, p.yetki, p.durum, k.eposta AS login_eposta, k.telefon AS login_telefon FROM personel1 p LEFT JOIN sube s ON s.sube_id = p.sube_id LEFT JOIN kullanici_giris1 k ON k.kisi_id = p.personel_id AND k.kisi_turu = 'personel' /* WHERE p.durum = 1 */ ORDER BY p.personel_id ASC; ";
$rows = $db->get($sql);  // Ydil::get($querySql, $params=[])
$yetkiMap = [1 => 'Admin', 2 => 'Yönetici'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FORM VERİLERİ
    $adi   = trim($_POST['personel_adi'] ?? '');
    $soyad = trim($_POST['personel_soyadi'] ?? '');
    $tel   = preg_replace('/\D+/', '', $_POST['personel_tel'] ?? '');
    $mail  = trim($_POST['personel_mail'] ?? '');
    $cins  = $_POST['personel_cinsiyet'] ?? '';
    $dogum = $_POST['personel_dogumtar'] ?? null;
    $adres = trim($_POST['personel_adres'] ?? '');
    $yetki = (int)($_POST['yetki'] ?? 0);
    $sube  = $_POST['sube_id'] ?? null;


    if ($yetki === 1) $sube = null;

    // ZORUNLU ALAN KONTROLÜ
    if ($adi=='' || $soyad=='' || $tel=='' || !in_array($cins, ['E','K']) || !in_array($yetki, [1,2])) {
        $db->swalToggle('error','Eksik Bilgi','Zorunlu alanları doldurun.','personeller.php');
        exit;
    }

    // --- 1. PERSONEL1 TABLOSUNA EKLE ---
    $cols = [
        'personel_adi','personel_soyadi','personel_tel','personel_mail',
        'personel_cinsiyet','personel_dogumtar','personel_adres',
        'yetki','sube_id','durum'
    ];
    $vals = [
        $adi, $soyad, $tel, $mail,
        $cins, $dogum ?: null, $adres,
        $yetki, $sube ?: null, 1
    ];

    $insertPersonel = $db->insert('personel1', $cols, $vals);

    if ($insertPersonel['status'] != 1) {
        $db->swalToggle('error','Hata','Personel eklenemedi.','personeller.php');
        exit;
    }

    $personelId = $insertPersonel['id']; // eklenen personelin id'si

    // --- 2. KULLANICI_GIRIS1 TABLOSUNA EKLE ---
    // şifre otomatik oluşturuluyor (TC yoksa varsayılan)
    $sifreHash = '0';

    $colsLogin = [
        'eposta','telefon','sifre','kisi_turu','kisi_id'
    ];
    $valsLogin = [
        $mail ?: null, $tel ?: null, $sifreHash, 'personel', $personelId
    ];

    $insertLogin = $db->insert('kullanici_giris1', $colsLogin, $valsLogin);

    if ($insertLogin['status'] == 1) {
        $db->swalToggle('success','Başarılı','Personel ve kullanıcı girişi oluşturuldu.','personeller.php');
    } else {
        $db->swalToggle('warning','Uyarı','Personel eklendi ancak kullanıcı girişi oluşturulamadı.','personeller.php');
    }

    exit;
}

?>

<?php
$pageTitle = "Personeller";
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>


<!-- Page Wrapper -->
    <div class="page-wrapper">
      <div class="content">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
          <div class="my-auto mb-2">
            <h3 class="page-title mb-1">Personeller</h3>
            <nav>
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                  <a href="index.php">Anasayfa</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Personeller</li>
              </ol>
            </nav>
          </div>
          <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">

            <div class="mb-2">
              <a href="#" data-bs-toggle="modal" data-bs-target="#per_ekle" class="btn btn-primary">
                    <i class="ti ti-square-rounded-plus me-2"></i>Personel Ekle
                </a>
            </div>
          </div>
        </div>


        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
            <h4 class="mb-3"> </h4>
            <div class="d-flex align-items-center flex-wrap">

            </div>
          </div>
          <div class="card-body p-0 py-3">


            <div class="custom-datatable-filter table-responsive">
              <table class="table datatable">
                <thead class="thead-light">
                  <tr>

                      <th>ID</th>
                      <th>Ad Soyad</th>
                      <th>Telefon</th>
                      <th>Şube</th>
                      <th>Yetki</th>
                      <th>Durum</th>
                      <th>İşlem</th>
                  </tr>
                </thead>
                  <tbody>
                <?php if (!empty($rows)): ?>
                  <?php foreach ($rows as $r):
                    $kid   = (int)$r['personel_id'];
                    $ad    = htmlspecialchars($r['adsoyad'] ?? '');
                    $tel   = htmlspecialchars($r['personel_tel'] ?? '');
                    $sube  = htmlspecialchars($r['sube_adi'] ?? '—');
                    $yetki = $yetkiMap[(int)($r['yetki'] ?? 0)] ?? ('Yetki: '.(int)($r['yetki'] ?? 0));
                    $login = htmlspecialchars($r['login_eposta'] ?? ($r['login_telefon'] ?? ''));
                    $son   = !empty($r['son_giris']) ? htmlspecialchars(formatDateTR($r['son_giris'], true)) : '';
                    $durum = $r['durum'];
                  ?>
                  <tr>


                    <td><a href="#" class="link-primary">P<?= str_pad($kid, 6, '0', STR_PAD_LEFT) ?></a></td>

                    <td>
                      <div class="d-flex align-items-center">
                        <div class="ms-0">
                          <p class="text-dark mb-0"><?= $ad ?></p>
                          <?php if ($login): ?><span class="fs-12 text-muted"><?= $login ?></span><?php endif; ?>
                        </div>
                      </div>
                    </td>

                    <td><?= $tel ?></td>
                    <td><?= $sube ?></td>
                    <td>
                      <?= $yetki ?>
                      <?php if ($son): ?><br><span class="badge bg-light text-dark">Son giriş: <?= $son ?></span><?php endif; ?>
                    </td>
                      <td><?= $durum == 1
                              ? '<span class="badge badge-soft-success d-inline-flex align-items-center"><i class="ti ti-circle-filled fs-5 me-1"></i>aktif</span>'
                              : '<span class="badge badge-soft-danger d-inline-flex align-items-center"><i class="ti ti-circle-filled fs-5 me-1"></i>pasif</span>'
                          ?></td>


                      <td>
                          <div class="d-flex align-items-center">
                              <div class="dropdown">
                                  <a href="teachers.html#"
                                     class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
                                     data-bs-toggle="dropdown" aria-expanded="false">
                                      <i class="ti ti-dots-vertical fs-14"></i>
                                  </a>
                                  <ul class="dropdown-menu dropdown-menu-right p-3">
                                      <li> <a class="dropdown-item rounded-1"  href="#" data-bs-toggle="modal" data-bs-target="#per_duz" data-id="<?= $kid ?>"> <i class="ti ti-edit-circle me-2"></i>Düzenle </a>
                                      </li>
                                      <li>
                                          <a class="dropdown-item rounded-1" href="#"
                                             data-bs-toggle="modal"
                                             data-bs-target="#login_detail"
                                             data-id="<?= $kid ?>">
                                              <i class="ti ti-lock me-2"></i>Şifre Sıfırla
                                          </a>
                                      </li>
                                  </ul>
                              </div>
                          </div>
                      </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="7" class="text-center text-muted">Kayıt bulunamadı.</td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Add Parent -->
    <div class="modal fade" id="per_ekle">
        <div class="modal-dialog modal-dialog-centered  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title">Personel Ekle</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="personeller.php" method="post" id="per-ekle-form">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Ad</label>
                                    <input type="text" name="personel_adi" class="form-control" placeholder="Personelin Adı" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Soyad</label>
                                    <input type="text" name="personel_soyadi" class="form-control" placeholder="Personelin Soyadı" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Telefon</label>
                                    <input type="text" name="personel_tel" class="form-control" placeholder="Personelin Telefonu" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">E-posta</label>
                                    <input type="email" name="personel_mail" class="form-control" placeholder="Personelin Mail Adresi">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Doğum Tarihi</label>
                                    <input type="date" name="personel_dogumtar" class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Cinsiyet</label>
                                    <select name="personel_cinsiyet" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                        <option value="E">Erkek</option>
                                        <option value="K">Kadın</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label mb-1 d-block">Yetki</label>
                                <div class="mb-3 d-flex align-items-center gap-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="yetki" id="yetki-admin" value="1" required>
                                        <label class="form-check-label" for="yetki-admin">Admin</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="yetki" id="yetki-yonetici" value="2" required>
                                        <label class="form-check-label" for="yetki-yonetici">Yönetici</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Şube</label>
                                    <select name="sube_id" id="sube_id" class="form-select">
                                        <option value="">Şube Seçiniz</option>
                                        <?php
                                        $subeler = $db->finds("sube", "sube_durum", 1);
                                        foreach ($subeler as $s) {
                                            echo '<option value="'.$s["sube_id"].'">'.$s["sube_adi"].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Adres</label>
                                    <textarea name="personel_adres" rows="3" class="form-control" placeholder="Adres Giriniz"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" form="per-ekle-form" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Edit Parent -->
    <div class="modal fade" id="per_duz">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <h4 class="modal-title">Personel Bilgilerini Düzenleme</h4>
                    </div>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <form  method="post" id="per-duz-form">
                    <input type="hidden" name="personel_id" id="duz_personel_id">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Ad</label>
                                    <input type="text" name="personel_adi" id="duz_personel_adi" class="form-control" placeholder="Personelin Adı" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Soyad</label>
                                    <input type="text" name="personel_soyadi" id="duz_personel_soyadi" class="form-control" placeholder="Personelin Soyadı" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Telefon</label>
                                    <input type="text" name="personel_tel" id="duz_personel_tel" class="form-control" placeholder="Personelin Telefonu" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">E-posta</label>
                                    <input type="email" name="personel_mail" id="duz_personel_mail" class="form-control" placeholder="Personelin Mail Adresi">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Doğum Tarihi</label>
                                    <input type="date" name="personel_dogumtar" id="duz_personel_dogumtar" class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Cinsiyet</label>
                                    <select name="personel_cinsiyet" id="duz_personel_cinsiyet" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                        <option value="E">Erkek</option>
                                        <option value="K">Kadın</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label mb-1 d-block">Yetki</label>
                                <div class="mb-3 d-flex align-items-center gap-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="yetki" id="duz_yetki_admin" value="1" required>
                                        <label class="form-check-label" for="duz_yetki_admin">Admin</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="yetki" id="duz_yetki_yonetici" value="2" required>
                                        <label class="form-check-label" for="duz_yetki_yonetici">Yönetici</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Şube</label>
                                    <select name="sube_id" id="duz_sube_id" class="form-select">
                                        <option value="">Şube Seçiniz</option>
                                        <?php
                                        $subeler = $db->finds("sube", "sube_durum", 1);
                                        foreach ($subeler as $s) {
                                            echo '<option value="'.$s["sube_id"].'">'.$s["sube_adi"].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Adres</label>
                                    <textarea name="personel_adres" id="duz_personel_adres" rows="4" class="form-control" placeholder="Adres Giriniz"></textarea>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>Durum</h5>
                                        <p>Personeli aktif/pasif yap</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="duz_durum">
                                        <input type="hidden" name="durum" id="duz_durum_hidden" value="1">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- /Edit Parent -->

    <!-- Delete Modal -->
    <div class="modal fade" id="login_detail">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="sifre_sifirla_form">
                    <input type="hidden" name="kisi_id" id="sifre_kisi_id">
                    <div class="modal-body text-center">

                        <h4>Şifre Sıfırlansın mı?</h4>
                        <p>Bu işlem kullanıcının mevcut şifresini sıfırlayacaktır. Kullanıcı giriş ekranından şifresini belirleyebilir.</p>
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</a>
                            <button type="submit" class="btn btn-danger">Evet, Sıfırla</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



  </div>


<script src="assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<script src="assets/js/moment.js" type="text/javascript"></script>
 <script src="assets/js/feather.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="assets/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
<script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js" type="text/javascript"></script>
<script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js" type="text/javascript"></script>
<script src="assets/js/script.js" type="text/javascript"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const adminRadio = document.getElementById('yetki-admin');
        const yoneticiRadio = document.getElementById('yetki-yonetici');
        const subeSelect = document.querySelector('select[name="sube_id"]');

        function toggleSube() {
            if (adminRadio.checked) {
                subeSelect.disabled = true;
                subeSelect.value = "";
            } else {
                subeSelect.disabled = false;
            }
        }

        // Sayfa yüklenince ve seçim değişince kontrol et
        toggleSube();
        adminRadio.addEventListener('change', toggleSube);
        yoneticiRadio.addEventListener('change', toggleSube);
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('per_duz');
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const personelId = button.getAttribute('data-id');
            const hiddenInput = modal.querySelector('input[name="personel_id"]');
            if (hiddenInput) hiddenInput.value = personelId;
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('per_duz');
        const subeSelect = document.getElementById('duz_sube_id');
        const adminRadio = document.getElementById('duz_yetki_admin');
        const yoneticiRadio = document.getElementById('duz_yetki_yonetici');
        const durumSwitch = document.getElementById('duz_durum');
        const durumHidden = document.getElementById('duz_durum_hidden');

        function toggleSubeByYetki() {
            if (adminRadio.checked) {
                subeSelect.disabled = true;
                subeSelect.value = "";
            } else {
                subeSelect.disabled = false;
            }
        }

        adminRadio.addEventListener('change', toggleSubeByYetki);
        yoneticiRadio.addEventListener('change', toggleSubeByYetki);

        durumSwitch.addEventListener('change', function() {
            durumHidden.value = this.checked ? '1' : '0';
        });

        modal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const personelId = btn.getAttribute('data-id');
            if (!personelId) return;

            // form id’yi doldur
            document.getElementById('duz_personel_id').value = personelId;

            // önce formu temizle
            document.getElementById('duz_personel_adi').value = '';
            document.getElementById('duz_personel_soyadi').value = '';
            document.getElementById('duz_personel_tel').value = '';
            document.getElementById('duz_personel_mail').value = '';
            document.getElementById('duz_personel_dogumtar').value = '';
            document.getElementById('duz_personel_cinsiyet').value = '';
            document.getElementById('duz_personel_adres').value = '';
            adminRadio.checked = false;
            yoneticiRadio.checked = false;
            subeSelect.value = '';
            durumSwitch.checked = true; // varsayılan aktif
            durumHidden.value = '1';

            // veriyi çek
            fetch('sozlesme-ajax/personel-get.php?id=' + encodeURIComponent(personelId), { cache: 'no-cache' })
                .then(r => r.json())
                .then(d => {
                    // beklenen alanlar: personel_adi, personel_soyadi, personel_tel, personel_mail,
                    // personel_dogumtar (YYYY-MM-DD), personel_cinsiyet (E/K), personel_adres,
                    // yetki (1/2), sube_id (nullable), durum (1/0)
                    document.getElementById('duz_personel_adi').value = d.personel_adi || '';
                    document.getElementById('duz_personel_soyadi').value = d.personel_soyadi || '';
                    document.getElementById('duz_personel_tel').value = d.personel_tel || '';
                    document.getElementById('duz_personel_mail').value = d.personel_mail || '';
                    document.getElementById('duz_personel_dogumtar').value = (d.personel_dogumtar || '').substring(0,10);
                    document.getElementById('duz_personel_cinsiyet').value = d.personel_cinsiyet || '';
                    document.getElementById('duz_personel_adres').value = d.personel_adres || '';

                    if (String(d.yetki) === '1') {
                        adminRadio.checked = true;
                    } else {
                        yoneticiRadio.checked = true;
                    }

                    subeSelect.value = d.sube_id || '';
                    toggleSubeByYetki();

                    durumSwitch.checked = String(d.durum) === '1';
                    durumHidden.value = durumSwitch.checked ? '1' : '0';
                })
                .catch(() => {
                    // veri çekilemezse modalı kapatabilirsin veya uyarı gösterebilirsin
                });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('per-duz-form');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // sayfa yenilenmesin

            const formData = new FormData(form);

            fetch('personel-duzenle.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        icon: data.status === 1 ? 'success' : 'error',
                        title: data.title || (data.status === 1 ? 'Başarılı' : 'Hata'),
                        text: data.message || ''
                    }).then(() => {
                        if (data.status === 1) location.reload(); // sayfayı yenile
                    });
                })
                .catch(() => {
                    Swal.fire('Hata', 'Sunucuya bağlanılamadı.', 'error');
                });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('login_detail');
        const inputId = document.getElementById('sifre_kisi_id');
        const form = document.getElementById('sifre_sifirla_form');

        // Modal açıldığında ilgili personel id’sini al
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const kisiId = button.getAttribute('data-id');
            inputId.value = kisiId;
        });

        // Form gönderilince AJAX ile işlem yap
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch('sozlesme-ajax/per-sifre-sifirla.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        icon: data.status === 1 ? 'success' : 'error',
                        title: data.status === 1 ? 'Başarılı' : 'Hata',
                        text: data.message
                    }).then(() => {
                        if (data.status === 1) location.reload();
                    });
                })
                .catch(() => {
                    Swal.fire('Hata','Sunucuya bağlanılamadı.','error');
                });
        });
    });
</script>
</body>

</html>