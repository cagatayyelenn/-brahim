<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
// Sınıf ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sinif_ekle'])) {
    $sinif_adi   = trim($_POST['sinif_adi']);
    $sinif_durum = isset($_POST['sinif_durum']) ? 1 : 0;

    if ($sinif_adi !== '') {
        $insert = $db->insert('sinif', ['sinif_adi', 'sinif_durum'], [$sinif_adi, $sinif_durum]);

        if ($insert['status'] == 1) {
            $db->swalToggle('success', 'Başarılı!', 'Sınıf başarıyla eklendi.', 'sinif-bilgisi.php');
        } else {
            $db->swalToggle('error', 'Hata!', 'Kayıt eklenirken bir hata oluştu.');
        }
    } else {
        $db->swalToggle('warning', 'Uyarı!', 'Lütfen Sınıf adını giriniz.');
    }
}
// Sınıf ekeleme işlemi Sonu

//Sınıf silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_sinif'])) {
    $sinifId = (int)($_POST['sinif_id'] ?? 0);

    if ($sinifId > 0) {
        $sonuc = $db->delete('sinif', $sinifId, 'sinif_id');
        if ($sonuc['status'] == 1) {
            $db->swalToggle('success', 'Silindi', 'Sınıf başarıyla silindi.', 'sinif-bilgisi.php');
        } else {
            $db->swalToggle('error', 'Hata', 'Silme işlemi başarısız oldu.');
        }
    } else {
        $db->swalToggle('warning', 'Uyarı', 'Geçersiz Sınıf ID.');
    }
}
//Sınıf silme işlemi sonu

//Sınıfları veritabanından çekme
$siniflar = $db->finds('sinif');
//sıınfları veritabanından çekme sonu
?>

<?php
$pageTitle = "Sınıf Bilgisi";
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>


<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Sınıf Bilgisi</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Sınıf Bilgisi</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">

                <div class="pe-1 mb-2">
                    <button type="button" class="btn btn-outline-light bg-white btn-icon me-1"
                            data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Print"
                            data-bs-original-title="Print">
                        <i class="ti ti-printer"></i>
                    </button>
                </div>
                <div class="dropdown me-2 mb-2">
                    <a href="javascript:void(0);"
                       class="dropdown-toggle btn btn-light fw-medium d-inline-flex align-items-center"
                       data-bs-toggle="dropdown">
                        <i class="ti ti-file-export me-2"></i>Dışa Aktar
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                    class="ti ti-file-type-pdf me-1"></i>PDF Olarak </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                    class="ti ti-file-type-xls me-1"></i>Excel Olarak </a>
                        </li>
                    </ul>
                </div>
                <div class="mb-2">
                    <a href="countries.html#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sinif_ekle"><i
                            class="ti ti-square-rounded-plus-filled me-2"></i>Sınıf Ekle</a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
                <h4 class="mb-3">Sınıf Listesi</h4>
                <div class="d-flex align-items-center flex-wrap">


                </div>
            </div>

            <div class="card-body p-0 py-3">
                <!-- Country List -->
                <div class="custom-datatable-filter
						table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                        <tr>
                            <th class="no-sort">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th>ID</th>
                            <th>Sınıf Adı</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($siniflar)): ?>
                            <?php foreach ($siniflar as $index => $s): ?>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($s['sinif_adi']) ?></td>
                                    <td>
                                        <?php if ($s['sinif_durum'] == 1): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="#"
                                           class="btn btn-sm btn-danger btn-delete-sinif"
                                           data-id="<?= $s['sinif_id'] ?>"
                                           data-name="<?= htmlspecialchars($s['sinif_adi']) ?>"
                                           data-bs-toggle="modal"
                                           data-bs-target="#sinif_sil">
                                            Sil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Henüz Sınıf eklenmemiş.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /Country List -->
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->

<!-- Add Country -->
<div class="modal fade" id="sinif_ekle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Sınıf Bilgisi Ekle</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Sınıf Adı</label>
                                <input type="text" class="form-control" name="sinif_adi" required>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5>Durumu</h5>
                                    <p>Yayında olması için aktif edin</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="sinif_durum" value="1" id="sinif_durum_switch">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">İptal</a>
                    <button type="submit" name="sinif_ekle" class="btn btn-primary">Sınıf Bilgisini Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Country -->

<!-- Edit Country -->
<div class="modal fade" id="edit_country">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Sınıf Bilgisi Düzenle</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form action="countries.html">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Sınıf Adı</label>
                                <input type="text" class="form-control" placeholder="Enter Country Name" value="United States">
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5>Durumu</h5>
                                    <p>Yayında olmadı için aktif edin </p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch-sm2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="countries.html#" class="btn btn-light me-2" data-bs-dismiss="modal">İptal</a>
                    <button type="submit" class="btn btn-primary">Sınıf bilgisini Düzenle</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Edit Country -->

<!-- Delete Modal -->
<div class="modal fade" id="sinif_sil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-body text-center">
          <span class="delete-icon">
            <i class="ti ti-trash-x fs-1"></i>
          </span>
                    <h4>Silme İşlemi Yapıyorsunuz</h4>
                    <p><strong id="deletesinifName">-</strong> Sınıfı silmek istediğinize emin misiniz?</p>
                    <input type="hidden" name="sinif_id" id="deletesinifId">
                    <input type="hidden" name="delete_sinif" value="1">
                    <div class="d-flex justify-content-center mt-3">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Hayır</button>
                        <button type="submit" class="btn btn-danger">Evet, sil</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Delete Modal -->

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.btn-delete-sinif').forEach(function(btn){
            btn.addEventListener('click', function(){
                document.getElementById('deletesinifId').value = this.dataset.id;
                document.getElementById('deletesinifName').textContent = this.dataset.name;
            });
        });
    });
</script>

</body>

</html>