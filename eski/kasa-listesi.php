<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";
 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Kasa Giriş / Çıkış Listesi</title>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.1/feather.min.js" crossorigin="anonymous"></script>
    <style>
        .fw-bold{font-weight:700}
        .text-danger{color:#dc3545!important}
        .text-dark{color:#212529!important}
    </style>
</head>
<body class="nav-fixed">
<?php include 'ekler/sidebar.php'; ?>
<div id="layoutSidenav">
    <?php include 'ekler/menu.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <header class="page-header page-header-dark" style="background-color:#007bff; padding-bottom:50px;">
                <div class="container-xl px-4">
                    <div class="page-header-content pt-4">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mt-4">
                                <h1 class="page-header-title">
                                    <div class="page-header-icon"><i data-feather="filter"></i></div>
                                    Kasa Giriş / Çıkış Listesi
                                </h1>
                                <div class="page-header-subtitle">Filtrele, görüntüle, sayfalarda gez.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="container-xl px-4 mt-n10" style="max-width:100%!important;">
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row g-3">
                            <div class="col-12 col-md-2">
                                <label class="form-label" for="startDate">Başlangıç Tarihi</label>
                                <input class="form-control" id="startDate" type="date" />
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label" for="endDate">Bitiş Tarihi</label>
                                <input class="form-control" id="endDate" type="date" />
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="subeSelect">Şube Seçiniz</label>
                                <?php
                                $subeler = "SELECT * FROM sube ORDER BY sube_id ASC";
                                $subess  = $Ydil->get($subeler) ?: [];
                                ?>
                                <select id="subeSelect" class="form-select">
                                    <option value="" selected>Hepsi</option>
                                    <?php foreach ($subess as $row): ?>
                                        <option value="<?= (int)$row['sube_id']; ?>"><?= htmlspecialchars($row['sube_adi']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <button type="button" id="action_type" class="btn btn-primary w-100">
                                    <i class="fa fa-filter me-1"></i> Filtrele
                                </button>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <button type="button" id="clear_button" class="btn btn-secondary w-100">Tüm Zamanları Gör</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Personel</th>
                                <th>Tür Adı</th>
                                <th>Şube</th>
                                <th style="color:green;">Giriş</th>
                                <th style="color:red;">Çıkış</th>
                                <th>Bakiye</th>
                                <th>Açıklama</th>
                                <th>Tarih</th>
                                <th>İşlem</th>
                            </tr>
                            </thead>
                            <tbody id="tbody-content">
                            <tr><td colspan="10" class="text-center text-muted">Filtre uygulanmadı.</td></tr>
                            </tbody>
                        </table>
                        <nav class="mt-3">
                            <ul id="pagination" class="pagination ml-auto pagination-content"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer-admin mt-auto footer-light">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright &copy; Your Website 2025</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a> &middot; <a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Onay Modali -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">İşlemi Onayla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmText">Bu kaydı silmek istediğinize emin misiniz?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="button" id="btnConfirmDelete" class="btn btn-danger">Evet, Sil</button>
            </div>
        </div>
    </div>
</div>

<!-- Mesaj Modali -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="messageModalLabel" class="modal-title">Bilgi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="messageText">İşlem sonucu burada görünecek.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tamam</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>

<script>
    (function () {
        const PER_PAGE = 10;
        const MOCK_DELETE = false; // test için true yaparsan backend'e gitmeden silme simüle edilir

        const els = {
            start: document.getElementById('startDate'),
            end: document.getElementById('endDate'),
            sube: document.getElementById('subeSelect'),
            btn: document.getElementById('action_type'),
            clear: document.getElementById('clear_button'),
            tbody: document.getElementById('tbody-content'),
            pager: document.getElementById('pagination')
        };

        let lastQuery = { start_date: '', end_date: '', sube_id: '' };
        let deleteCtx = { id: null };
        let confirmModal, messageModal;

        function fmtTL(n) {
            const v = Number(n || 0);
            return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v) + ' TL';
        }
        function escapeHtml(s){return String(s ?? '').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}

        function renderRows(rows) {
            if (!rows || rows.length === 0) {
                els.tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Kayıt bulunamadı.</td></tr>';
                return;
            }

            let toplamGiris = 0;
            let toplamCikis = 0;

            const htmlRows = rows.map(r => {
                const giris  = r.giris > 0 ? fmtTL(r.giris) : '';
                const cikis  = r.cikis > 0 ? fmtTL(r.cikis) : '';
                toplamGiris += Number(r.giris || 0);
                toplamCikis += Number(r.cikis || 0);

                const bakiye = fmtTL(r.bakiye);
                const bClass = r.bakiye_neg ? 'text-danger fw-bold' : 'text-dark';

                 const actionTd = `<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="${r.id}">
                    <i class="fa fa-trash"></i> Sil
                  </button>`;

                return `
              <tr>
                <td>${r.id}</td>
                <td>${escapeHtml(r.personel_adi || '-')}</td>
                <td>${escapeHtml(r.tur_adi || '-')}</td>
                <td>${escapeHtml(r.sube_adi || '-')}</td>
                <td>${giris || '&nbsp;'}</td>
                <td>${cikis || '&nbsp;'}</td>
                <td class="${bClass}">${bakiye}</td>
                <td>${escapeHtml(r.aciklama || '')}</td>
                <td>${escapeHtml(r.tarih_fmt || '')}</td>
                <td>${actionTd}</td>
              </tr>`;
            }).join('');

            const toplamBakiye = toplamGiris - toplamCikis;

            const toplamSatir = `
            <tr class="fw-bold bg-light">
                <td colspan="4" class="text-end">TOPLAM:</td>
                <td style="color:green;">${fmtTL(toplamGiris)}</td>
                <td style="color:red;">${fmtTL(toplamCikis)}</td>
                <td>${fmtTL(toplamBakiye)}</td>
                <td colspan="3"></td>
            </tr>
        `;

            els.tbody.innerHTML = htmlRows + toplamSatir;
            wireDeleteButtons();
        }

        function renderPager(total, page) {
            const pages = Math.ceil((total || 0) / PER_PAGE);
            if (pages <= 1) { els.pager.innerHTML = ''; return; }
            let html = '';
            html += `<li class="page-item ${page<=1?'disabled':''}"><a class="page-link" href="#" data-page="${page-1}">Önceki</a></li>`;
            for (let p=1; p<=pages; p++){
                html += `<li class="page-item ${p===page?'active':''}"><a class="page-link custom-pagination" href="#" data-page="${p}">${p}</a></li>`;
            }
            html += `<li class="page-item ${page>=pages?'disabled':''}"><a class="page-link" href="#" data-page="${page+1}">Sonraki</a></li>`;
            els.pager.innerHTML = html;
            els.pager.querySelectorAll('a[data-page]').forEach(a=>{
                a.addEventListener('click',function(e){
                    e.preventDefault();
                    const p = parseInt(this.getAttribute('data-page'),10);
                    if(!isNaN(p)) fetchPage(p);
                });
            });
        }

        async function fetchPage(page=1) {
            const fd = new FormData();
            fd.append('page', String(page));
            fd.append('per_page', String(PER_PAGE));
            fd.append('start_date', lastQuery.start_date || '');
            fd.append('end_date',   lastQuery.end_date   || '');
            fd.append('sube_id',    lastQuery.sube_id    || '');

            const res = await fetch('kasa_listesi_data.php', { method: 'POST', body: fd });
            const j   = await res.json(); // {total, page, rows:[...]}
            renderRows(j.rows || []);
            renderPager(j.total || 0, j.page || 1);
        }

        function wireDeleteButtons(){
            els.tbody.querySelectorAll('.btn-delete').forEach(btn=>{
                btn.addEventListener('click', ()=>{
                    deleteCtx.id = parseInt(btn.dataset.id, 10);
                    document.getElementById('confirmText').textContent =
                        `ID ${deleteCtx.id} numaralı kaydı silmek istediğinize emin misiniz?`;
                    confirmModal.show();
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            messageModal = new bootstrap.Modal(document.getElementById('messageModal'));

            document.getElementById('btnConfirmDelete').addEventListener('click', async () => {
                if (!deleteCtx.id) return;
                try {
                    let data;

                    if (MOCK_DELETE) {
                        await new Promise(r => setTimeout(r, 400));
                        data = { ok: true, message: 'Kayıt silindi (mock).' };
                    } else {
                        const fd = new FormData();
                        fd.append('id', String(deleteCtx.id));
                        const res = await fetch('kasa_sil.php', { method: 'POST', body: fd });
                        data = await res.json().catch(()=>({ ok:false, message:'Geçersiz yanıt' }));
                    }

                    confirmModal.hide();
                    document.getElementById('messageModalLabel').textContent = data.ok ? 'Silindi' : 'Hata';
                    document.getElementById('messageText').textContent = data.message || (data.ok ? 'Kayıt silindi.' : 'Kayıt silinemedi.');
                    messageModal.show();

                    if (data.ok) fetchPage(1);
                } catch (e) {
                    confirmModal.hide();
                    document.getElementById('messageModalLabel').textContent = 'Hata';
                    document.getElementById('messageText').textContent = 'Sunucu hatası.';
                    messageModal.show();
                } finally {
                    deleteCtx.id = null;
                }
            });
        });

        // Filtrele
        els.btn.addEventListener('click', function(){
            lastQuery = {
                start_date: els.start.value || '',
                end_date:   els.end.value   || '',
                sube_id:    els.sube.value  || ''
            };
            fetchPage(1);
        });

        // Temizle
        els.clear.addEventListener('click', function(){
            els.start.value = '';
            els.end.value   = '';
            els.sube.value  = '';
            lastQuery = { start_date:'', end_date:'', sube_id:'' };
            fetchPage(1);
        });
    })();
</script>
</body>
</html>