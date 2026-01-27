<?php include "c/fonk.php"; include "c/config.php"; include "c/user.php"; ?>
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
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="startDate">Başlangıç Tarihi</label>
                                <input class="form-control" id="startDate" type="date" />
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="endDate">Bitiş Tarihi</label>
                                <input class="form-control" id="endDate" type="date" />
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="turAdi">Tür Adı</label>
                                <select class="form-control" id="turAdi">
                                    <option value="">Hepsi</option>
                                    <option value="Nakit">Nakit</option>
                                    <option value="Pos">Pos</option>
                                    <option value="Havale">Havale/EFT</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <button type="button" id="action_type" class="btn btn-primary w-100">
                                    <i class="fa fa-filter me-1"></i> Filtrele
                                </button>
                            </div>
                            <div class="col-12 col-md-3 d-flex align-items-end">
                                <button type="button" id="clear_button" class="btn btn-secondary w-100">Tüm Zamanları Gör</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kaynak</th>
                                    <th>Öğrenci</th>
                                    <th>Tür Adı</th>
                                    <th style="color:green;">Giriş</th>
                                    <th style="color:red;">Çıkış</th>
                                    <th>Açıklama</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-content">
                                <tr><td colspan="10" class="text-center text-muted">Filtre uygulanmadı.</td></tr>
                            </tbody>
                        </table>
                        <div id="summary-box" class="mt-3"></div>
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
        end: document.getElementById('endDate'),
        tur: document.getElementById('turAdi'),
        btn: document.getElementById('action_type'),
        clear: document.getElementById('clear_button'),
        tbody: document.getElementById('tbody-content'),
        pager: document.getElementById('pagination'),
        summary: document.getElementById('summary-box')
    };
    let lastQuery = { start_date: '', end_date: '', tur: '' };

    function fmtTL(n) {
        const v = Number(n || 0);
        return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v) + ' TL';
    }
    function escapeHtml(s){
        return String(s ?? '').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function renderRows(rows) {
        if (!rows || rows.length === 0) {
            els.tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Kayıt bulunamadı.</td></tr>';
            els.summary.innerHTML = '';
            return;
        }
        let sums = {};
        els.tbody.innerHTML = rows.map(r => {
            const giris = r.islem_tipi === 'giris' ? fmtTL(r.tutar) : '';
            const cikis = r.islem_tipi === 'cikis' ? fmtTL(r.tutar) : '';
            const tur = r.tur_adi || 'Bilinmiyor';
            if (!sums[tur]) sums[tur] = { giris:0, cikis:0 };
            if (r.islem_tipi === 'giris') sums[tur].giris += Number(r.tutar||0);
            if (r.islem_tipi === 'cikis') sums[tur].cikis += Number(r.tutar||0);
            return `
                <tr>
                    <td>${r.id}</td>
                    <td>${escapeHtml(r.kaynak || '-')}</td>
                    <td>${escapeHtml(r.ogrenci || '-')}</td>
                    <td>${escapeHtml(r.tur_adi || '-')}</td>
                    <td>${giris || '&nbsp;'}</td>
                    <td>${cikis || '&nbsp;'}</td>
                    <td>${escapeHtml(r.aciklama || '')}</td>
                    <td>${escapeHtml(r.tarih || '')}</td>
                </tr>`;
        }).join('');
        
        // Özet kutusu + genel toplam
        let genelGiris = 0, genelCikis = 0;
        Object.values(sums).forEach(v=>{
            genelGiris += v.giris;
            genelCikis += v.cikis;
        });
        const genelBakiye = genelGiris - genelCikis;

        els.summary.innerHTML = `
            <h5>Özet</h5>
            <ul>
                ${Object.entries(sums).map(([tur, v]) =>
                    `<li><strong>${tur}:</strong> Giriş ${fmtTL(v.giris)} | Çıkış ${fmtTL(v.cikis)}</li>`
                ).join('')}
            </ul>
            <div class="mt-2 p-2 border rounded bg-light">
                <strong>Genel Toplam:</strong> 
                Giriş ${fmtTL(genelGiris)} | 
                Çıkış ${fmtTL(genelCikis)} | 
                Bakiye ${fmtTL(genelBakiye)}
            </div>`;
    }

    function renderPager(total, page) {
        const pages = Math.ceil((total || 0) / PER_PAGE);
        if (pages <= 1) {
            els.pager.innerHTML = '';
            return;
        }
        let html = '';
        html += `<li class="page-item ${page<=1?'disabled':''}">
                    <a class="page-link" href="#" data-page="${page-1}">Önceki</a></li>`;
        for (let p=1; p<=pages; p++){
            html += `<li class="page-item ${p===page?'active':''}">
                        <a class="page-link custom-pagination" href="#" data-page="${p}">${p}</a></li>`;
        }
        html += `<li class="page-item ${page>=pages?'disabled':''}">
                    <a class="page-link" href="#" data-page="${page+1}">Sonraki</a></li>`;
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
        fd.append('end_date', lastQuery.end_date || '');
        fd.append('tur', lastQuery.tur || '');
        const res = await fetch('kasa_data.php', { method: 'POST', body: fd });
        const j = await res.json(); // {total, page, rows:[...]}
        renderRows(j.rows || []);
        renderPager(j.total || 0, j.page || 1);
    }

    els.btn.addEventListener('click', function(){
        lastQuery = { 
            start_date: els.start.value || '', 
            end_date: els.end.value || '',
            tur: els.tur.value || ''
        };
        fetchPage(1);
    });
    els.clear.addEventListener('click', function(){
        els.start.value = '';
        els.end.value = '';
        els.tur.value = '';
        lastQuery = { start_date:'', end_date:'', tur:'' };
        fetchPage(1);
    });
})();
</script>
</body>
</html>
