<?php
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';

// Silme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $db->delete('duyurular', $id);
    echo "<script>window.location.href='duyuru-listesi.php';</script>";
    exit;
}

// Ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ekle'])) {
    $baslik = trim($_POST['baslik']);
    $icerik = trim($_POST['icerik']);

    if ($baslik != "" && $icerik != "") {
        $db->insert('duyurular', ['baslik', 'icerik'], [$baslik, $icerik]);
        echo "<script>window.location.href='duyuru-listesi.php';</script>";
        exit;
    }
}

$duyurular = $db->finds('duyurular'); // Tüm duyuruları çek
if (!$duyurular)
    $duyurular = []; // Eğer boşsa veya tablo yoksa
?>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Duyuru Yönetimi</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                        <li class="breadcrumb-item active">Duyurular</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Duyuru Ekleme Formu -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Yeni Duyuru Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" class="form-control" name="baslik" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">İçerik (Kısa Açıklama)</label>
                                <textarea class="form-control" name="icerik" rows="4" required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="ekle" class="btn btn-primary">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Duyuru Listesi -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Duyuru Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Başlık</th>
                                        <th>İçerik</th>
                                        <th>Tarih</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($duyurular as $duyuru): ?>
                                        <tr>
                                            <td>
                                                <?= $duyuru['id'] ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($duyuru['baslik']) ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars(mb_substr($duyuru['icerik'], 0, 50)) ?>...
                                            </td>
                                            <td>
                                                <?= $duyuru['tarih'] ?>
                                            </td>
                                            <td>
                                                <?= $db->confirmDeleteLink('duyurular', $duyuru['id'], 'duyuru-listesi.php') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($duyurular)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Henüz duyuru eklenmemiş.</td>
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

<?php require_once 'alanlar/footer.php'; ?>