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

// Ekleme ve Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslik = trim($_POST['baslik']);
    $icerik = trim($_POST['icerik']);
    
    // Güncelleme
    if(isset($_POST['guncelle_id'])) {
        $id = intval($_POST['guncelle_id']);
        $durum = intval($_POST['durum']);
        
        if ($baslik != "" && $icerik != "" && $id > 0) {
            $db->update('duyurular', ['baslik', 'icerik', 'durum'], [$baslik, $icerik, $durum], 'id', $id);
            echo "<script>window.location.href='duyuru-listesi.php';</script>";
            exit;
        }
    } 
    // Ekleme
    elseif (isset($_POST['ekle'])) {
        if ($baslik != "" && $icerik != "") {
            $db->insert('duyurular', ['baslik', 'icerik'], [$baslik, $icerik]);
            echo "<script>window.location.href='duyuru-listesi.php';</script>";
            exit;
        }
    }
}

$duyurular = $db->get("SELECT * FROM duyurular ORDER BY id DESC");
if (!$duyurular) $duyurular = [];
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
                                            <td><?= $duyuru['id'] ?></td>
                                            <td><?= htmlspecialchars($duyuru['baslik']) ?></td>
                                            <td><?= htmlspecialchars(mb_substr($duyuru['icerik'], 0, 50)) ?>...</td>
                                            <td><?= $duyuru['tarih'] ?></td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info me-2 edit-btn" 
                                                        data-id="<?= $duyuru['id'] ?>" 
                                                        data-baslik="<?= htmlspecialchars($duyuru['baslik']) ?>" 
                                                        data-icerik="<?= htmlspecialchars($duyuru['icerik']) ?>" 
                                                        data-durum="<?= $duyuru['durum'] ?>" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal">
                                                    <i class="ti ti-edit"></i> Düzenle
                                                </button>
                                                <?= $db->confirmDeleteLink('duyurular', $duyuru['id'], 'duyuru-listesi.php') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($duyurular)): ?>
                                        <tr><td colspan="5" class="text-center">Henüz duyuru eklenmemiş.</td></tr>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duyuru Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="editForm">
                    <input type="hidden" name="guncelle_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" class="form-control" name="baslik" id="edit_baslik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İçerik</label>
                        <textarea class="form-control" name="icerik" id="edit_icerik" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-select" name="durum" id="edit_durum">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'alanlar/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const baslik = this.getAttribute('data-baslik');
            const icerik = this.getAttribute('data-icerik');
            const durum = this.getAttribute('data-durum');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_baslik').value = baslik;
            document.getElementById('edit_icerik').value = icerik;
            document.getElementById('edit_durum').value = durum;
        });
    });
});
</script>