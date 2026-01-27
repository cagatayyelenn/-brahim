    (function () {
    'use strict';

    // ---- TCKN checksum ----
    function isValidTCKN(tc) {
    if (!/^\d{11}$/.test(tc)) return false;
    if (tc[0] === '0') return false;
    const d = tc.split('').map(Number);
    const sumOdd  = d[0] + d[2] + d[4] + d[6] + d[8];
    const sumEven = d[1] + d[3] + d[5] + d[7];
    const digit10 = ((sumOdd * 7) - sumEven) % 10;
    const digit11 = (d.slice(0, 10).reduce((a, b) => a + b, 0)) % 10;
    return d[9] === digit10 && d[10] === digit11;
}

    // ---- Yardımcılar ----
    function onlyDigits(el) {               // rakam dışını temizle (kullanıcı deneyimi)
    const v = el.value.replace(/\D+/g, '');
    if (v !== el.value) el.value = v;
}
    function setValidity(el, ok, msg) {     // Bootstrap native geri bildirim
    if (ok) {
    el.setCustomValidity('');
    el.classList.remove('is-invalid');
    el.classList.add('is-valid');
} else {
    el.setCustomValidity(msg || 'Geçersiz değer');
    el.classList.add('is-invalid');
    el.classList.remove('is-valid');
}
}
    function clearValidity(el) {
    el.setCustomValidity('');
    el.classList.remove('is-invalid', 'is-valid');
}

    window.addEventListener('load', function () {
    const form = document.getElementById('ogrenci-ekle-form');

    const elStdTC  = form.querySelector('[name="ogrenci_tc"]');
    const elParTC  = form.querySelector('[name="veli_tc"]');         // artık görünür
    const elMode   = form.querySelectorAll('input[name="veli_durumu"]');
    const guardianSection = document.getElementById('guardian-details-section');

    // ---- Guardian toggle (göster/gizle + required/disable + temizlik) ----
    function toggleGuardian() {
    const isSelf = form.querySelector('input[name="veli_durumu"]:checked').value === '1';
    guardianSection.style.display = isSelf ? 'none' : '';
    guardianSection.querySelectorAll('input,select,textarea').forEach(function (inp) {
    inp.disabled = isSelf;
    // Veli alanlarını sadece görünürken required yap
    if (['veli_tc','veli_adi','veli_soyadi','veli_tel','veli_mail','veli_adres'].includes(inp.name)) {
    inp.required = !isSelf;
}
    if (isSelf) {
    inp.value = '';
    clearValidity(inp);
}
});
}
    elMode.forEach(r => r.addEventListener('change', toggleGuardian));
    toggleGuardian(); // ilk açılış

    // ---- Anlık (real-time) doğrulama: yazarken kontrol ----
    if (elStdTC) {
    elStdTC.addEventListener('input', function () {
    onlyDigits(elStdTC);
    if (!elStdTC.value) {             // required olduğu için boşsa invalid gözüksün istiyorsanız, bu bloğu kaldırmayın.
    setValidity(elStdTC, false, 'Bu alan zorunludur.');
    return;
}
    const ok = isValidTCKN(elStdTC.value);
    setValidity(elStdTC, ok, 'Geçerli bir T.C. Kimlik Numarası giriniz.');
});
}

    if (elParTC) {
    elParTC.addEventListener('input', function () {
    if (elParTC.disabled) return;     // öğrenci kendi ise veli alanları kapalı
    onlyDigits(elParTC);
    if (!elParTC.value) {
    setValidity(elParTC, false, 'Bu alan zorunludur.');
    return;
}
    const ok = isValidTCKN(elParTC.value);
    setValidity(elParTC, ok, 'Geçerli bir T.C. Kimlik Numarası giriniz.');
});
}

    // ---- Submit anında nihai kontrol (Bootstrap native ile entegre) ----
    form.addEventListener('submit', function (e) {
    // Öğrenci TC
    if (elStdTC) {
    const okStd = !!elStdTC.value && isValidTCKN(elStdTC.value);
    setValidity(elStdTC, okStd, 'Geçerli bir T.C. Kimlik Numarası giriniz.');
}

    // Veli TC (sadece görünürken)
    if (elParTC && !elParTC.disabled) {
    const okPar = !!elParTC.value && isValidTCKN(elParTC.value);
    setValidity(elParTC, okPar, 'Geçerli bir T.C. Kimlik Numarası giriniz.');
}

    // Bootstrap’in form-validation.js’i checkValidity() çağırıyor; biz yine de garantiye alalım:
    if (!form.checkValidity()) {
    e.preventDefault();
    e.stopPropagation();
}
    form.classList.add('was-validated');
}, false);
});
})();