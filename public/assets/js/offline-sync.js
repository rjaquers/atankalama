// Starter Kit RKM v6 - Offline forms queue + auto sync (simple, LocalStorage)
// Para operaciones masivas, migrar a IndexedDB.

(function () {
  const KEY = "rkm_offline_queue_v1";
  const statusEl = document.getElementById("rkm-network-status");

  function setStatus() {
    if (!statusEl) return;
    if (navigator.onLine) statusEl.classList.add("d-none");
    else statusEl.classList.remove("d-none");
  }

  function getQueue() {
    try { return JSON.parse(localStorage.getItem(KEY) || "[]"); }
    catch(e) { return []; }
  }

  function setQueue(items) {
    localStorage.setItem(KEY, JSON.stringify(items));
  }

  // Encola payload genérico (lo puedes llamar desde cualquier formulario)
  window.RKM_OFFLINE = {
    enqueue: function(payload){
      const q = getQueue();
      q.push(payload);
      setQueue(q);
    },
    size: function(){
      return getQueue().length;
    }
  };

  async function syncNow() {
    if (!navigator.onLine) return;

    let q = getQueue();
    if (!q.length) return;

    const max = (window.RKM_OFFLINE_MAX_BATCH || 50);
    const batch = q.slice(0, max);

    let okCount = 0;

    for (const item of batch) {
      try {
        const res = await fetch("." + (window.RKM_OFFLINE_ENDPOINT || "/offline-sync/store"), {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(item)
        });
        if (res.ok) okCount++;
      } catch (e) {}
    }

    // remover enviados (simplificado: si fallan, quedan)
    q = q.slice(okCount);
    setQueue(q);

    if (okCount > 0) {
      // aviso al usuario
      try {
        const toast = document.createElement("div");
        toast.className = "toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3";
        toast.setAttribute("role","alert");
        toast.innerHTML = `<div class="d-flex">
          <div class="toast-body">Conexión restaurada. Sincronizados: ${okCount}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
        document.body.appendChild(toast);
        const t = new bootstrap.Toast(toast, { delay: 4000 });
        t.show();
        toast.addEventListener("hidden.bs.toast", ()=>toast.remove());
      } catch(e) {}
    }
  }

  // Detectar conexión
  window.addEventListener("online", () => { setStatus(); syncNow(); });
  window.addEventListener("offline", () => { setStatus(); });

  setStatus();

  // Intento inicial
  if (navigator.onLine) syncNow();
})();
