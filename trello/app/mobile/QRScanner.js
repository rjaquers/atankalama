// Starter Kit RKM v6 - QRScanner helper (cliente)
// Requiere html5-qrcode (CDN) incluido en layout cuando se usa el scanner.

function rkmStartQrScanner(readerId, onResult) {
  if (typeof Html5Qrcode === "undefined") {
    console.error("Html5Qrcode no está disponible. Verifica CDN.");
    return;
  }

  const scanner = new Html5Qrcode(readerId);

  scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    (decodedText) => {
      try { onResult(decodedText); } catch(e) {}
    }
  ).catch(err => console.error(err));

  return scanner;
}
