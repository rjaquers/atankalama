document.addEventListener('DOMContentLoaded', function () {
    new QRCode(document.getElementById('qr-voucher'), {
        text: VOUCHER_QR_URL,
        width: 180,
        height: 180,
        colorDark : '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    document.getElementById('btnImprimir').addEventListener('click', function () {
        window.print();
    });
});
