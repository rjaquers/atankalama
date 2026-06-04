// Starter Kit RKM v6 - UI helpers
(function(){
  const el = document.getElementById("btnRefreshNoti");
  if (el) {
    el.addEventListener("click", async () => {
      try {
        const res = await fetch("./api/notification/latest");
        const json = await res.json();
        const list = document.getElementById("notiList");
        if (!list) return;

        list.innerHTML = "";
        (json.data || []).forEach(n => {
          const li = document.createElement("li");
          li.className = "list-group-item";
          li.innerHTML = `<div class="fw-bold">${escapeHtml(n.title||'')}</div>
                          <div class="text-muted small">${escapeHtml(n.message||'')}</div>
                          <div class="small">${escapeHtml(n.created_at||'')} · ${escapeHtml(n.channel||'')}</div>`;
          list.appendChild(li);
        });
      } catch(e) {}
    });
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }
})();
