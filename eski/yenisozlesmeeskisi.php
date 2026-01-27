<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";

$gelendeger = $_GET['ogrid'];

if (empty($_SESSION['student_id'])) {
  $_SESSION['student_id']= $gelendeger;
}


$ogrbilgisi = $_SESSION['student_id'];
$birimler="SELECT * FROM `birim` ORDER BY `birim`.`birim_id` ASC";
$birims=$Ydil->get($birimler);

$ogrler="SELECT * FROM `ogrenci` WHERE `ogrenci_id` = $ogrbilgisi; ";
$ogrs=$Ydil->getone($ogrler);
$alan_id=$ogrs['alan_id'];

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Yeni Sözleşme Oluşturma</title>
        <link href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    </head>
    <body class="nav-fixed">
      <?php include 'ekler/sidebar.php'; ?>
        <div id="layoutSidenav">
            <?php include 'ekler/menu.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                     <div class="container-xl px-4 mt-5">
                        <div class="row">
                            <div class="col-xl-6 col-md-6 mb-4">
                                 <div class="card border-start-lg border-start-primary h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="small fw-bold text-primary mb-1">Öğrenci Bilgisi</div>
                                                <div class="h5"><b><?= $ogrs['ogrenci_adi'] ?> <?= $ogrs['ogrenci_soyadi'] ?></b> için yeni sözleşme

                                                    <input type="hidden" id="stdnt_id" value="<?= $ogrbilgisi;?>"/>
                                                    <input type="hidden" id="stdnt_alan_id" value="<?= $alan_id;?>"/>
                                                </div>
                                            </div>
                                            <div class="ms-2"><i class="fas fa-user-plus fa-2x text-gray-200"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-md-6 mb-4">
                                 <div class="card border-start-lg border-start-info h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="small fw-bold text-info mb-1">Sözleşme Bilgisi</div>
                                                <div class="h5"><b><?= $ogrs['ogrenci_adi'] ?></b> adlı öğrencinin daha önceden sözleşmesi bulunmamaktadır.</div>
                                            </div>
                                            <div class="ms-2"><i class="fas fa-save fa-2x text-gray-200"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form method="post"  id="taksitForm">

                          <div class="row">
                            <div class="col-lg-3 mb-4">
                                <div class="card mb-4">
                                    <div class="card-header">Sözleşme Oluşturma</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="small mb-1">Birim</label>
                                            <select class="form-select" aria-label="Default select example">
                                                <option selected disabled>Birim Seçiniz</option>
                                                <?php foreach ($birims as $birimss ) {  ?>
                                                <option value="<?= $birimss['birim_id']; ?>"><?= $birimss['birim_adi']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="small mb-1">Birim Fiyatı</label>
                                            <input class="form-control" id="birimFiyat" type="text" placeholder="Lütfen Birim Fiyatı Giriniz" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="small mb-1">Miktar</label>
                                            <input class="form-control" id="miktar" type="text" placeholder="Lütfen Miktar Giriniz" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="fw-bold text-success">Toplam Tutar</label>
                                            <input class="form-control text-end text-success" id="toplamTutar" type="text" disabled>
                                        </div>
                                        <button class="btn btn-primary" type="button" id="btnSozlesme">Sözleşme Oluştur</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9 mb-8" style="display:none;" id="sozlesmealani">
                                <div class="card shadow-sm border-0 rounded-3 mb-4">
                                    <div class="card-header bg-primary text-white fw-bold">Sözleşme Tutarları</div>
                                    <div class="card-body">
                                        <div class="row mb-8 mt-4">
                                            <div class="col-md-4">
                                                <label class="fw-bold text-secondary">Birim Tutar</label>
                                                <input type="text" class="form-control text-end" id="birimTutar" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold text-secondary">Tutar</label>
                                                <input type="text" class="form-control text-end" id="tutar" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold text-success">Toplam Tutar</label>
                                                <input type="text" class="form-control text-end text-success" id="toplamTutar1" disabled>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="text-primary fw-bold">Peşinat Bilgileri</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Peşinat Tutarı</label>
                                                <input type="text" class="form-control text-end" id="pesinatTutari">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Ödeme Türü</label>
                                                <select class="form-select">
                                                    <option selected disabled value="">Ödeme Türü Seçiniz</option>
                                                    <option value="nakit">NAKİT</option>
                                                    <option value="kredikarti">KREDİ KARTI</option>
                                                    <option value="bankahavalesi">BANKA HAVALESİ</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Kasa</label>
                                                <select class="form-select">
                                                    <option selected disabled value="">Kasa Türü Seçiniz</option>
                                                    <option value="banka">BANKA KASA</option>
                                                    <option value="cek-senet">ÇEK-SENET KASA</option>
                                                    <option value="nakit">NAKİT KASA</option>
                                                    <option value="pos">POS KASA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <h5 class="text-primary fw-bold">Taksit Bilgileri</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Taksit Toplamı</label>
                                                <input type="text" class="form-control text-end" id="taksitToplami" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Taksit Sayısı</label>
                                                <input type="text" class="form-control text-end" id="taksitSayisi">
                                                <span class="text-danger">Taksit sayısı girmezse 1 olacaktır</span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fw-bold">Başlama Tarihi</label>
                                                <input type="date" class="form-control text-end" value="" id="dateInput">
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row mb-12">
                                            <div class="col-md-12 text-end">
                                                <button class="btn btn-primary" type="button" id="btntaksit">Taksit Oluştur</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 mb-8 " >
                                    <div class="card mb-4">
                                        <div class="card-header">Taksit Sayısı</div>
                                        <div class="card-body p-0"> </div>
                                        <div class="card border-start-lg border-start-primary h-100">
                                            <div class="card-body">
                                                <div class="table-responsive table-billing-history">
                                                    <table class="table mb-0" id="taksitTable">
                                                        <thead>
                                                            <tr>
                                                                <th class="border-gray-200" scope="col">Taksit No</th>
                                                                <th class="border-gray-200" scope="col">Tarih</th>
                                                                <th class="border-gray-200" scope="col">Tutar</th>
                                                                <th class="border-gray-200" scope="col">Ödeme Türü</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Dinamik olarak eklenen tr'ler burada olacak -->
                                                        </tbody>
                                                    </table>
                                                    <hr>
                                                    <div class="row mb-12">
                                                        <div class="col-md-12 text-end">
                                                            <input id="action_type" type="hidden" name="action_type" value="add"/>
                                                             <button class="btn btn-primary" id="submitButton" type="button">Sözleşme Oluştur</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                          </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>

        <script>
        // Butona tıklandığında çalışacak fonksiyon
        document.getElementById('submitButton').addEventListener('click', function () {
            // Tabloyu ve tbody'yi seç
            let tbody = document.querySelector('#taksitTable tbody');
            let rows = tbody.querySelectorAll('tr');  // Tablodaki tüm satırları al
            let dataToSend = [];  // Verileri tutacak dizi

            // Her satırdaki verileri al
            rows.forEach(function (row) {
                let cells = row.querySelectorAll('td');
                let rowData = {
                    no: cells[0].textContent,  // Taksit No
                    date: cells[1].querySelector('input').value,  // Tarih
                    amount: cells[2].querySelector('input').value,  // Tutar
                    paymentMethod: cells[3].querySelector('select').value  // Ödeme Türü
                };

                dataToSend.push(rowData);  // Satır verisini diziye ekle
            });

            // Gizli input'tan action_type'ı al
            let actionType = document.getElementById('action_type').value;
            $.ajax({
              url:"your_php_file.php",
              type:"POST",
              contentType:"application/json",
              data:JSON.stringify({musteri_id:$("#stdnt_id").val(),siparis_id:$("stdnt_alan_id").val(),action_type:actionType,taksitData:dataToSend}),
              success:function(response){
                console.log(response);
              }
            })

        });

        </script>

        <script>
                // Tabloyu seç
        const table = document.getElementById('taksittablosu');

        // Tabloyu satırlara ayır
        const rows = table.rows;  // tablodaki tüm satırlar

        // Tablo başlıklarını atla (ilk satır başlık kısmıdır)
        for (let i = 1; i < rows.length; i++) {
          // Her satırdaki hücrelere (td'lere) eriş
          const cells = rows[i].cells;

          // Her hücreyi al ve console'a yazdır
          const id = cells[0].textContent;
          const ad = cells[1].textContent;
          const yas = cells[2].textContent;

          console.log(`ID: ${id}, Ad: ${ad}, Yaş: ${yas}`);
        }
        </script>


        <script>
        document.addEventListener('DOMContentLoaded', function () {

          // Sayı biçimlendirme fonksiyonu
          function formatNumber(value) {
              if (value) {
                  return value.toLocaleString('tr-TR');
              }
              return '';
          }

          // Birim Fiyatı ve Miktar için sayısal formatlama
          document.getElementById('birimFiyat').addEventListener('input', function () {
              let birimFiyat = this.value.replace(/[^\d]/g, ''); // Sadece sayıları al
              this.value = formatNumber(birimFiyat);
          });

          document.getElementById('miktar').addEventListener('blur', function () {
              let birimFiyat = parseFloat(document.getElementById('birimFiyat').value.replace(/[^\d]/g, '')) || 0;
              let miktar = parseFloat(this.value.replace(/[^\d]/g, '')) || 0;

              // Toplam Tutar hesaplama
              let toplamTutar = birimFiyat * miktar;
              document.getElementById('toplamTutar').value = formatNumber(toplamTutar);
          });

          // Sözleşme oluştur butonuna tıklanırsa
          document.getElementById('btnSozlesme').addEventListener('click', function () {
              let birimFiyat = parseFloat(document.getElementById('birimFiyat').value.replace(/[^\d]/g, '')) || 0;
              document.getElementById('birimTutar').value = formatNumber(birimFiyat);

              let toplamTutar = parseFloat(document.getElementById('toplamTutar').value.replace(/[^\d]/g, '')) || 0;
              document.getElementById('tutar').value = formatNumber(toplamTutar);
              document.getElementById('toplamTutar1').value = formatNumber(toplamTutar);

              document.getElementById('sozlesmealani').style.display = 'block';
          });

          // Peşinat tutarı düzenleme
          document.getElementById('pesinatTutari').addEventListener('input', function () {
              let pesinatTutari = this.value.replace(/[^\d]/g, ''); // Sadece sayıları al
              this.value = formatNumber(pesinatTutari);
          });

          // Taksit toplamı hesaplama
          document.getElementById('pesinatTutari').addEventListener('blur', function () {
              let toplamTutar1 = parseFloat(document.getElementById('toplamTutar1').value.replace(/[^\d]/g, '')) || 0;
              let pesinatTutari = parseFloat(this.value.replace(/[^\d]/g, '')) || 0;

              // Taksit toplamı
              let taksitToplami = toplamTutar1 - pesinatTutari;
              document.getElementById('taksitToplami').value = formatNumber(taksitToplami);
          });

          // Taksit oluştur butonuna tıklanırsa
          document.getElementById('btntaksit').addEventListener('click', function () {
              // Gerekli verileri al
              let taksitToplami = parseFloat(document.getElementById('taksitToplami').value.replace(/[^\d]/g, '')) || 0;
              let taksitSayisi = parseInt(document.getElementById('taksitSayisi').value) || 1; // Eğer taksit sayısı yoksa 1
              let startDate = new Date(document.getElementById('dateInput').value); // Başlangıç tarihi

              // Eğer başlangıç tarihi girilmediyse, bugünün tarihi alınsın
              if (isNaN(startDate)) {
                  startDate = new Date();
              }

              // Eğer taksit sayısı 0 veya negatifse, 1 olacak
              taksitSayisi = Math.max(taksitSayisi, 1);

              let taksitTutar = taksitToplami / taksitSayisi;

              // tbody içindeki tr'yi çoğaltma
              let tbody = document.querySelector('table tbody');

              // Geçici olarak "deneme" yazdır
              tbody.innerHTML = ''; // 'deneme' yazacak

              // Burada dinamik <tr> elemanlarını eklemeye devam edebiliriz
              setTimeout(function() {
                  tbody.innerHTML = ''; // 'deneme' yazısını temizle
                  // Her taksit için tr elemanı ekleme
                  for (let i = 0; i < taksitSayisi; i++) {
                      let tr = document.createElement('tr');

                      // Taksit No
                      let tdNo = document.createElement('td');
                      tdNo.className = 'align-center';
                      tdNo.style.width = '10%';
                      tdNo.textContent = i + 1;

                      // Tarih
                      let tdDate = document.createElement('td');
                      tdDate.style.width = '40%';
                      tdDate.style.borderRight = '2px solid #616060';
                      let divDate = document.createElement('div');
                      divDate.className = 'd-flex justify-content-between align-items-center';
                      let inputDate = document.createElement('input');
                      inputDate.type = 'date';
                      inputDate.className = 'form-control';
                      inputDate.style.width = '140px';

                      // Tarih ayarları: Başlangıç tarihi + i ay
                      let date = new Date(startDate); // Başlangıç tarihini kopyala
                      date.setMonth(date.getMonth() + i); // Her taksitte bir ay artacak

                      inputDate.value = date.toISOString().split('T')[0]; // YYYY-MM-DD formatı

                      let editLinkDate = document.createElement('a');
                      editLinkDate.className = 'btn btn-link btn-sm';
                      editLinkDate.textContent = 'Düzenle';
                      divDate.appendChild(inputDate);
                      divDate.appendChild(editLinkDate);
                      tdDate.appendChild(divDate);

                      // Tutar
                      let tdTutar = document.createElement('td');
                      tdTutar.style.width = '40%';
                      tdTutar.style.borderRight = '2px solid #616060';
                      let divTutar = document.createElement('div');
                      divTutar.className = 'd-flex justify-content-between align-items-center';
                      let inputTutar = document.createElement('input');
                      inputTutar.type = 'text';
                      inputTutar.className = 'form-control';
                      inputTutar.style.width = '140px';
                      inputTutar.style.textAlign = 'right';
                      inputTutar.value = formatNumber(taksitTutar); // formatNumber fonksiyonu burada kullanılmalı
                      let editLinkTutar = document.createElement('a');
                      editLinkTutar.className = 'btn btn-link btn-sm';
                      editLinkTutar.textContent = 'Düzenle';
                      divTutar.appendChild(inputTutar);
                      divTutar.appendChild(editLinkTutar);
                      tdTutar.appendChild(divTutar);

                      // Ödeme Türü
                      let tdPayment = document.createElement('td');
                      tdPayment.style.width = '40%';
                      let selectPayment = document.createElement('select');
                      selectPayment.className = 'form-control';
                      selectPayment.style.width = '182px';
                      selectPayment.innerHTML = `
                          <option value="nakit" selected="selected" >NAKİT</option>
                          <option value="kredikarti">KREDİ KARTI</option>
                          <option value="ceksenet">ÇEK-SENET</option>
                          <option value="bankahavalesi">BANKA HAVALESİ</option>
                      `;
                      tdPayment.appendChild(selectPayment);

                      // Tr'yi tbody'ye ekle
                      tr.appendChild(tdNo);
                      tr.appendChild(tdDate);
                      tr.appendChild(tdTutar);
                      tr.appendChild(tdPayment);
                      tbody.appendChild(tr);
                  }
              }, 1000); // 1 saniye sonra dinamik satırlar eklenir
          });

          });

        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>-->
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="assets/demo/chart-pie-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
        <script src="js/litepicker.js"></script>
    </body>
</html>
