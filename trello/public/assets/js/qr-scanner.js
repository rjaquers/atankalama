// Starter Kit RKM v6 - QR Scanner wrapper
let rkmScanner = null;

(function(){
  const startBtn = document.getElementById("btnQrStart");
  const stopBtn  = document.getElementById("btnQrStop");
  const out      = document.getElementById("qrValue");

  if (!startBtn || !stopBtn) return;

  startBtn.addEventListener("click", async () => {
    if (typeof Html5Qrcode === "undefined") return;

    if (rkmScanner) return;

    rkmScanner = new Html5Qrcode("qr-reader");

    rkmScanner.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: 250 },
      (decodedText) => {
        if (out) out.value = decodedText;
      }
    ).catch(()=>{ rkmScanner = null; });
  });

  stopBtn.addEventListener("click", async () => {
    if (!rkmScanner) return;
    try {
      await rkmScanner.stop();
      await rkmScanner.clear();
    } catch(e) {}
    rkmScanner = null;
  });
})();
