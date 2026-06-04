<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3><i class="fa-solid fa-calendar text-primary"></i> Calendario de Ocupación</h3>
    <p class="text-muted mb-0">Vista gráfica de reservas de espacios</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= BASE_URL ?>/bookings" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-list"></i> Lista
    </a>
    <?php if (AuthService::hasPermission('bookings_create')): ?>
      <a href="<?= BASE_URL ?>/bookings/create" class="btn btn-atk btn-sm">
        <i class="fa-solid fa-plus"></i> Nueva Reserva
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- Filtro de espacio -->
<div class="card mb-4">
  <div class="card-body py-2">
    <div class="row g-2 align-items-center">
      <div class="col-md-3">
        <label class="form-label small mb-0 fw-bold">Filtrar por espacio</label>
        <select id="filterSpace" class="form-select form-select-sm">
          <option value="">Todos los espacios</option>
          <?php foreach ($spaces as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['code'] . ' — ' . $s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-9">
        <div class="d-flex gap-2 justify-content-end">
          <span class="badge bg-success">Confirmada</span>
          <span class="badge bg-info">En Uso</span>
          <span class="badge bg-dark">Finalizada</span>
          <span class="badge bg-danger">Cancelada</span>
          <span class="badge bg-warning text-dark">No Asistió</span>
          <span class="badge" style="background: #adb5bd;">Bloqueado</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Calendario -->
<div class="card">
  <div class="card-body">
    <div id="spaceCalendar" style="min-height: 650px;"></div>
  </div>
</div>

<!-- FullCalendar CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
  .fc-event-finalized {
    opacity: 0.65;
    border-style: dashed !important;
  }
  .fc-event:hover {
    opacity: 1 !important;
    transform: scale(1.02);
    transition: all 0.2s ease;
    z-index: 99;
  }
</style>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const calendarEl = document.getElementById('spaceCalendar');
  let spaceFilter = '';

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    locale: 'es',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    buttonText: {
      today: 'Hoy',
      month: 'Mes',
      week: 'Semana',
      day: 'Día'
    },
    slotMinTime: '07:00:00',
    slotMaxTime: '23:00:00',
    allDaySlot: false,
    height: 'auto',
    nowIndicator: true,
    navLinks: true,
    eventTimeFormat: {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    },
    events: function(fetchInfo, successCallback, failureCallback) {
      const start = fetchInfo.startStr.split('T')[0];
      const end = fetchInfo.endStr.split('T')[0];
      let url = `<?= BASE_URL ?>/bookings/calendarData?start=${start}&end=${end}`;
      if (spaceFilter) url += `&space_id=${spaceFilter}`;

      fetch(url)
        .then(r => r.json())
        .then(data => successCallback(data))
        .catch(err => failureCallback(err));
    },
    eventClick: function(info) {
      if (info.event.url) {
        info.jsEvent.preventDefault();
        window.location.href = info.event.url;
      }
    },
    eventDidMount: function(info) {
      // Tooltip
      const props = info.event.extendedProps || {};
      if (props.folio) {
        info.el.title = `${props.folio} — ${props.space} (${props.status})`;
      }
    }
  });

  calendar.render();

  // Filtrar por espacio
  document.getElementById('filterSpace').addEventListener('change', function() {
    spaceFilter = this.value;
    calendar.refetchEvents();
  });
});
</script>
