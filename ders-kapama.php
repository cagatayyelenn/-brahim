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
    <title>Öğrenci Ders Kapama</title>
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
                                   Öğrenci Ders Kapama Raporu
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
                                <label class="form-label" for="startDate">Ders Arama Başlangıç Tarihi</label>
                                <input class="form-control" id="startDate" type="date" />
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label" for="endDate">Ders Arama  Bitiş Tarihi</label>
                                <input class="form-control" id="endDate" type="date" />
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <button type="button" id="action_type" class="btn btn-primary w-100">
                                    <i class="fa fa-filter me-1"></i> Filtrele
                                </button>
                            </div> 
                        </div>
                    </div>

                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Öğrenci Adı - soyadı</th>
                                <th>Öğretmen Adı - soyadı</th>
                                <th>Ders Tarihi </th>
                                <th>ders Saati</th>
                                <th>işlem</th>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script>
    (function () {
        const PER_PAGE = 10;

        const els = {
            start: document.getElementById('startDate'),
            end:   document.getElementById('endDate'),
            btn:   document.getElementById('action_type'),
            tbody: document.getElementById('tbody-content'),
            pager: document.getElementById('pagination')
        };

        let lastQuery = { start_date: '', end_date: '' };

        function escapeHtml(s){
            return String(s ?? '').replace(/[&<>"']/g, m => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            }[m]));
        }

        function renderRows(rows) {
            if (!rows || rows.length === 0) {
                els.tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Kayıt bulunamadı.</td></tr>';
                return;
            }
            els.tbody.innerHTML = rows.map(r => `
      <tr>
        <td>${r.id}</td>
        <td>${escapeHtml(r.ogrenci || '-')}</td>
        <td>${escapeHtml(r.ogretmen || '-')}</td>
        <td>${escapeHtml(r.ders_tarihi || r.baslangic || '-')}</td>
        <td>${escapeHtml(r.ders_saati || '-')}</td>
        <td>${ r.islem || '-'}</td>
      </tr>
    `).join('');
        }

        function renderPager(total, page) {
            const pages = Math.ceil((total || 0) / PER_PAGE);
            if (pages <= 1) { els.pager.innerHTML = ''; return; }

            let html = '';
            html += `<li class="page-item ${page<=1?'disabled':''}">
               <a class="page-link" href="#" data-page="${page-1}">Önceki</a>
             </li>`;

            for (let p=1; p<=pages; p++){
                html += `<li class="page-item ${p===page?'active':''}">
                 <a class="page-link" href="#" data-page="${p}">${p}</a>
               </li>`;
            }

            html += `<li class="page-item ${page>=pages?'disabled':''}">
               <a class="page-link" href="#" data-page="${page+1}">Sonraki</a>
             </li>`;

            els.pager.innerHTML = html;
            els.pager.querySelectorAll('a[data-page]').forEach(a=>{
                a.addEventListener('click', function(e){
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

            const res = await fetch('ders_atama_data.php', { method: 'POST', body: fd });
            let j;
            try { j = await res.json(); } catch(e){ j = { total:0, page:1, rows:[] }; }

            renderRows(j.rows || []);
            renderPager(j.total || 0, j.page || 1);
        }

        // Filtrele
        els.btn.addEventListener('click', function(){
            const s = els.start.value;
            const e = els.end.value;

            // basit doğrulama
            if (s && e && s > e) {
                alert('Başlangıç tarihi, bitiş tarihinden büyük olamaz.');
                return;
            }

            lastQuery = { start_date: s || '', end_date: e || '' };
            fetchPage(1);
        });

        // İstersen açılışta otomatik son 30 gün:
        // const today = new Date(), d30 = new Date(today); d30.setDate(d30.getDate()-30);
        // els.start.value = d30.toISOString().slice(0,10);
        // els.end.value   = today.toISOString().slice(0,10);
        // lastQuery = { start_date: els.start.value, end_date: els.end.value };
        // fetchPage(1);
    })();
</script>
</body>
</html>