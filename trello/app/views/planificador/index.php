<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="plan-container">

  <div class="plan-header">
    <button class="btn btn-sm btn-outline-light" id="btn-prev" title="Mes anterior">
      <i class="bi bi-chevron-left"></i>
    </button>
    <h6 class="text-white mb-0 fw-bold" id="plan-title" style="min-width:160px;text-align:center"></h6>
    <button class="btn btn-sm btn-outline-light" id="btn-next" title="Mes siguiente">
      <i class="bi bi-chevron-right"></i>
    </button>
    <button class="btn btn-sm btn-outline-light ms-2" id="btn-hoy">Hoy</button>

    <div class="ms-auto d-flex align-items-center gap-2">
      <span class="plan-legend"><span class="plan-leg-dot" style="background:#22c55e"></span> &gt;3 días</span>
      <span class="plan-legend"><span class="plan-leg-dot" style="background:#f59e0b"></span> 1-3 días</span>
      <span class="plan-legend"><span class="plan-leg-dot" style="background:#ef4444"></span> Hoy</span>
      <span class="plan-legend"><span class="plan-leg-dot" style="background:#94a3b8"></span> Vencida</span>
    </div>
  </div>

  <div class="plan-grid-wrap">

    <div class="plan-dow-header">
      <?php foreach (['Lu','Ma','Mi','Ju','Vi','Sá','Do'] as $d): ?>
        <span><?= $d ?></span>
      <?php endforeach; ?>
    </div>

    <div id="plan-grid"></div>

  </div>
</div>

<!-- Modal de tarjeta -->
<div class="modal fade" id="tarjetaModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" id="tarjetaModalContent">
      <div class="modal-body text-center py-5">
        <div class="spinner-border text-secondary" role="status"></div>
      </div>
    </div>
  </div>
</div>

<script>
const BASE = '<?= BASE_URL ?>';

const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

let tarjetasData = <?= json_encode(array_values($tarjetas), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const _today = new Date();
let curYear  = _today.getFullYear();
let curMonth = _today.getMonth();

document.addEventListener('DOMContentLoaded', () => {
  initTarjetaModal();

  setModalHooks({
    onCardSaved: (tid, titulo, fecha) => {
      const idx = tarjetasData.findIndex(t => t.id == tid);
      if (idx >= 0) {
        tarjetasData[idx].titulo = titulo;
        tarjetasData[idx].fecha_vencimiento = fecha || null;
      }
      hideCardModal();
      renderCalendar();
    },
    onCardArchived: (tid) => {
      tarjetasData = tarjetasData.filter(t => t.id != tid);
      hideCardModal();
      renderCalendar();
    },
  });

  document.getElementById('btn-prev').addEventListener('click', () => {
    curMonth--;
    if (curMonth < 0) { curMonth = 11; curYear--; }
    renderCalendar();
  });
  document.getElementById('btn-next').addEventListener('click', () => {
    curMonth++;
    if (curMonth > 11) { curMonth = 0; curYear++; }
    renderCalendar();
  });
  document.getElementById('btn-hoy').addEventListener('click', () => {
    curYear  = _today.getFullYear();
    curMonth = _today.getMonth();
    renderCalendar();
  });

  renderCalendar();
});

function renderCalendar() {
  document.getElementById('plan-title').textContent = MESES[curMonth] + ' ' + curYear;

  const grid        = document.getElementById('plan-grid');
  grid.innerHTML    = '';

  const firstDay    = new Date(curYear, curMonth, 1);
  const startDow    = (firstDay.getDay() + 6) % 7; // 0=Lun, 6=Dom
  const daysInMonth = new Date(curYear, curMonth + 1, 0).getDate();
  const daysInPrev  = new Date(curYear, curMonth, 0).getDate();

  // Indexar tarjetas por día (YYYY-MM-DD)
  const chipsByKey = {};
  tarjetasData.forEach(t => {
    if (!t.fecha_vencimiento) return;
    const key = t.fecha_vencimiento.substring(0, 10);
    if (!chipsByKey[key]) chipsByKey[key] = [];
    chipsByKey[key].push(t);
  });

  const todayKey = isoDate(_today);

  for (let i = 0; i < 42; i++) {
    let day, isOther = false, dateKey;

    if (i < startDow) {
      day = daysInPrev - startDow + i + 1;
      isOther = true;
      dateKey = isoDate(new Date(curYear, curMonth - 1, day));
    } else if (i - startDow >= daysInMonth) {
      day = i - startDow - daysInMonth + 1;
      isOther = true;
      dateKey = isoDate(new Date(curYear, curMonth + 1, day));
    } else {
      day     = i - startDow + 1;
      dateKey = isoDate(new Date(curYear, curMonth, day));
    }

    const isToday = dateKey === todayKey;

    const cell = document.createElement('div');
    cell.className = 'plan-day' + (isOther ? ' plan-day-other' : '') + (isToday ? ' plan-day-today' : '');

    const numEl = document.createElement('div');
    numEl.className = 'plan-day-num';
    numEl.textContent = day;
    cell.appendChild(numEl);

    const chips = chipsByKey[dateKey] || [];
    if (chips.length > 0) {
      const wrap = document.createElement('div');
      wrap.className = 'plan-chips';
      const MAX = 3;
      chips.slice(0, MAX).forEach(t => wrap.appendChild(buildChip(t, dateKey)));
      if (chips.length > MAX) {
        const more = document.createElement('div');
        more.className = 'plan-more';
        more.textContent = '+' + (chips.length - MAX) + ' más';
        wrap.appendChild(more);
      }
      cell.appendChild(wrap);
    }

    grid.appendChild(cell);
  }
}

function buildChip(t, dateKey) {
  const sema  = semaforoClase(dateKey);
  const chip  = document.createElement('div');
  chip.className   = 'plan-chip ' + sema;
  chip.dataset.id  = t.id;
  chip.style.cssText = `background:${t.fondo_color}18;border-left:3px solid ${semaforoColor(sema)}`;
  chip.title = t.tablero_nombre + ' › ' + t.lista_nombre + '\n#' + t.numero + ' ' + t.titulo;

  chip.innerHTML = `<span class="plan-chip-num">#${t.numero}</span>
                    <span class="plan-chip-title">${escHtml(t.titulo)}</span>`;
  chip.addEventListener('click', () => openCardModal(t.id));
  return chip;
}

function semaforoClase(dateKey) {
  const diff = (new Date(dateKey + 'T23:59:59') - new Date()) / 86400000;
  if (diff < 0)  return 'chip-gris';
  if (diff < 1)  return 'chip-rojo';
  if (diff <= 3) return 'chip-amarillo';
  return 'chip-verde';
}

function semaforoColor(cls) {
  return { 'chip-verde': '#22c55e', 'chip-amarillo': '#f59e0b',
           'chip-rojo': '#ef4444',  'chip-gris': '#94a3b8' }[cls] || '#94a3b8';
}

function isoDate(d) {
  return d.getFullYear() + '-'
       + String(d.getMonth() + 1).padStart(2, '0') + '-'
       + String(d.getDate()).padStart(2, '0');
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
