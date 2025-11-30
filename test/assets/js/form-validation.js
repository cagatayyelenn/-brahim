
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();


  (function(){
  function isValidTCKN(tc){
    if(!/^\d{11}$/.test(tc)) return false;
    if(tc[0] === '0') return false;
    const d = tc.split('').map(Number);
    const sumOdd  = d[0]+d[2]+d[4]+d[6]+d[8];
    const sumEven = d[1]+d[3]+d[5]+d[7];
    const digit10 = ((sumOdd * 7) - sumEven) % 10;
    const digit11 = (d.slice(0,10).reduce((a,b)=>a+b,0)) % 10;
    return d[9] === digit10 && d[10] === digit11;
  }

  window.addEventListener('load', function(){
  const form = document.getElementById('ogrenci-ekle-form');
  const tc   = form.querySelector('[name="ogrenci_tc"]');

  form.addEventListener('submit', function(e){
  // TC: boş bırakılırsa required zaten yakalayacak; doluysa checksum kontrolü
  if (tc.value && !isValidTCKN(tc.value.trim())) {
  tc.setCustomValidity('Geçerli bir T.C. Kimlik Numarası giriniz.');
} else {
  tc.setCustomValidity('');
}
  // Bootstrap native akış
  if (!form.checkValidity()) {
  e.preventDefault();
  e.stopPropagation();
}
  form.classList.add('was-validated');
}, false);
}, false);
})();