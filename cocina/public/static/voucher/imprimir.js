document.addEventListener('DOMContentLoaded', function () {
    const qrOpts = {
        width: 180,
        height: 180,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    };

    QR_URLS.forEach(function(item) {
        const el = document.getElementById('qr-' + item.id);
        if (el) new QRCode(el, { ...qrOpts, text: item.url });
    });

    function marcarComoImpresos() {
        const formData = new FormData();
        formData.append('comanda_id', COMANDA_ID);
        fetch('index.php?page=voucher/marcarImpresos', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Tracking de impresión actualizado:', data);
        })
        .catch(error => {
            console.error('Error al actualizar tracking:', error);
        });
    }

    document.getElementById('btnImprimir').addEventListener('click', function() {
        marcarComoImpresos();
        window.print();
    });

    let soloNuevos = false;
    document.getElementById('btnFiltroNuevos').addEventListener('click', function () {
        soloNuevos = !soloNuevos;
        this.classList.toggle('btn-outline-warning', !soloNuevos);
        this.classList.toggle('btn-warning',         soloNuevos);
        this.innerHTML = soloNuevos
            ? '<i class="bi bi-funnel-fill me-1"></i>Mostrando sin imprimir'
            : '<i class="bi bi-funnel me-1"></i>Solo sin imprimir';

        const todos = document.querySelectorAll('.voucher-thermal');
        let visibles = 0;
        todos.forEach(v => {
            const ocultar = soloNuevos && v.dataset.impreso === '1';
            v.style.display = ocultar ? 'none' : '';
            if (!ocultar) visibles++;
        });
        document.getElementById('nVisible').textContent = visibles;
    });
});
