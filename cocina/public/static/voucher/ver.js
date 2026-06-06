document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('qrcode');
    const url = window.location.href;
    new QRCode(el, {
        text: url,
        width: 120,
        height: 120,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
});
