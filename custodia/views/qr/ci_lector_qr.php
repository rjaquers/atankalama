<
! DOCTYPE html >
<html lang = 'es' >
<head >
    <meta charset = 'UTF-8' >
    <title > Lector QR – CI Chile </title >
    <link href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel = 'stylesheet' >

    <! --Librería de lectura QR-- >
    <script src = 'https://unpkg.com/html5-qrcode' ></script >

    <style > #reader {
width: 320px;
            margin: auto;
        }
    </style >
</head >

<body class='container py-4' >

<h3 class='text-center' > Escanear QR – Cédula de Identidad </h3 >
<p class='text-center' > Apunte la cámara al QR del carnet.Se procesará automáticamente.</p >

<div id = 'reader' ></div >

<div class='mt-4 text-center' >
    <div id = 'resultado' class='alert alert-info d-none' ></div >
</div >

<script >
    function procesarQR(qrData) {
        // Mostrar el QR capturado
        let r = document.getElementById('resultado');
        r.classList.remove('d-none');
        r.innerHTML = 'QR capturado: <strong>' + qrData + '</strong><br>Enviando al servidor...';

        // Enviar a tu API MVC
        fetch('/api/ci/validar', {
            method: 'POST',
            body: JSON.stringify({ qr: qrData }),
            headers: {
            'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            r.innerHTML += '<br>Respuesta PHP: <strong>' + JSON.stringify(data) + '</strong>';
        })
        .catch(e => {
            r.innerHTML += '<br>Error: ' + e;
        });
    }

    // Inicializar cámara
    new Html5Qrcode('reader').start(
        { facingMode: 'environment' }, // Usa cámara trasera si existe
        {
        },
        procesarQR
    );

    fetch('/colaciones/qr/validar', {
        method: 'POST',
        body: JSON.stringify({ qr: qrData }),
        headers: { 'Content-Type': 'application/json' }
    })

</script >

</body >
</html >
