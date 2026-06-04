// ==============================
// Buscador rápido de productos
// ==============================
document.addEventListener('DOMContentLoaded', () => {

    const input   = document.getElementById('product-search');
    const results = document.getElementById('product-results');

    // Si no existe el buscador en la vista, salir sin errores
    if (!input || !results) {
        return;
    }

    let controller = null;
    let debounceTimer = null;

    input.addEventListener('input', () => {
        const q = input.value.trim();

        // Limpiar resultados si el texto es muy corto
        if (q.length < 2) {
            results.innerHTML = '';
            results.classList.add('d-none');
            return;
        }

        // Debounce simple (evita demasiadas llamadas)
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {

            // Cancelar petición anterior si existe
            if (controller) {
                controller.abort();
            }
            controller = new AbortController();

            fetch(`index.php?page=products&action=search&q=${encodeURIComponent(q)}`, {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(res => res.text()) // ⚠️ primero texto, no JSON
                .then(text => {
                    let data;

                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Respuesta NO es JSON:', text);
                        results.classList.add('d-none');
                        return;
                    }

                    results.innerHTML = '';

                    if (!Array.isArray(data) || data.length === 0) {
                        results.classList.add('d-none');
                        return;
                    }

                    data.forEach(p => {
                        const a = document.createElement('a');
                        a.href = `index.php?page=products&action=view&id=${p.id}`;
                        a.className = 'list-group-item list-group-item-action';
                        a.innerHTML = `
                        <div class="fw-semibold">${p.name}</div>
                        <small class="text-muted">${p.description || ''}</small>
                    `;
                        results.appendChild(a);
                    });

                    results.classList.remove('d-none');
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error('Error búsqueda productos:', err);
                    }
                });

        }, 300); // ⏱️ 300ms debounce
    });

    // Cerrar resultados al hacer click fuera
    document.addEventListener('click', (e) => {
        if (!results.contains(e.target) && e.target !== input) {
            results.classList.add('d-none');
        }
    });

});
