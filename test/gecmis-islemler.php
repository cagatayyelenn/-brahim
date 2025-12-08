<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();
$pageTitle = "İşlem Geçmişi";

// Şube ID'sini al (Admin değilse sadece kendi şubesi)
$sube_id = (int) ($_SESSION['sube_id'] ?? 0);
$yetki = (int) ($_SESSION['yetki'] ?? 0); // 1: Yönetici varsayalım

// Filtreler
$filter_tarih = $_GET['tarih'] ?? '';
$where = "WHERE 1=1";

// Eğer yönetici değilse sadece kendi şubesini görsün
// (Yetki seviyesi kontrolünü projenize göre ayarlayın, şimdilik sube_id > 0 ise ekliyoruz)
if ($sube_id > 0 && $yetki != 1) { // Örn: 1. seviye yönetici ise tümünü görsün, değilse şube
    $where .= " AND i.sube_id = '{$sube_id}'";
}

// Tarih filtresi
if (!empty($filter_tarih)) {
    // Tarih formatı: YYYY-MM-DD varsayıyoruz veya daterangepicker kullanılıyorsa ona göre
    // Örnek: Bugün
    $where .= " AND DATE(i.tarih) = '{$filter_tarih}'";
}

// Logları Çek
// Personel tablosu (personel1) ile join yapıyoruz
$sql = "SELECT i.*, p.personel_adi, p.personel_soyadi 
        FROM islem_gecmisi i 
        LEFT JOIN personel1 p ON i.personel_id = p.personel_id 
        $where 
        ORDER BY i.id DESC LIMIT 500";
$logs = $db->get($sql);

?>

<?php
$page_styles[] = ['href' => 'assets/css/animate.css'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
?>

<div class="page-wrapper">
    <div class="content content-two">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="mb-1">İşlem Geçmişi</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">Anasayfa</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">İşlem Geçmişi</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h4 class="text-dark">Son İşlemler</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Personel</th>
                                        <th>İşlem Türü</th>
                                        <th>Tablo</th>
                                        <th>Açıklama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($logs): ?>
                                        <?php foreach ($logs as $row):
                                            // Renklendirme
                                            $badgeClass = 'bg-secondary';
                                            if ($row['islem_turu'] == 'EKLEME')
                                                $badgeClass = 'bg-success';
                                            if ($row['islem_turu'] == 'GÜNCELLEME')
                                                $badgeClass = 'bg-warning text-dark';
                                            if ($row['islem_turu'] == 'SİLME')
                                                $badgeClass = 'bg-danger';

                                            $personel = $row['personel_adi'] . ' ' . $row['personel_soyadi'];
                                            if (trim($personel) == '')
                                                $personel = 'ID: ' . $row['personel_id'];
                                            ?>
                                            <tr>
                                                <td><?= date('d.m.Y H:i', strtotime($row['tarih'])) ?></td>
                                                <td><?= htmlspecialchars($personel) ?></td>
                                                <td><span
                                                        class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['islem_turu']) ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($row['tablo']) ?></td>
                                                <td><?= htmlspecialchars($row['aciklama']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Kayıt bulunamadı.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script data-cfasync="false" src="assets/js/jquery-3.7.1.min.js"></script>
<script data-cfasync="false" src="assets/js/bootstrap.bundle.min.js"></script>
<script data-cfasync="false" src="assets/js/feather.min.js"></script>
<script data-cfasync="false" src="assets/js/jquery.slimscroll.min.js"></script>
<script data-cfasync="false" src="assets/js/script.js"></script>
</body>

</html>