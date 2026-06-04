@echo off
REM ─────────────────────────────────────────────────────────────────────────
REM  Lanzador del Tótem de Vouchers - Hotel Atankalama
REM
REM  Lanza Chrome/Edge con --kiosk-printing para que al llamar window.print()
REM  la impresión vaya DIRECTAMENTE a la impresora por defecto del sistema
REM  SIN mostrar el diálogo de impresión del sistema operativo.
REM
REM  REQUISITO: Configurar la impresora térmica de 80mm como impresora
REM             predeterminada en Windows (Panel de control > Dispositivos).
REM ─────────────────────────────────────────────────────────────────────────

SET URL=https://www.atankalama.com/cocina/public/index.php?page=voucher/kiosko

REM --- Intentar con Chrome ---
SET CHROME="C:\Program Files\Google\Chrome\Application\chrome.exe"
IF EXIST %CHROME% (
    start "" %CHROME% --kiosk-printing --new-window "%URL%"
    EXIT /B
)

SET CHROME="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
IF EXIST %CHROME% (
    start "" %CHROME% --kiosk-printing --new-window "%URL%"
    EXIT /B
)

REM --- Intentar con Edge ---
SET EDGE="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
IF EXIST %EDGE% (
    start "" %EDGE% --kiosk-printing --new-window "%URL%"
    EXIT /B
)

SET EDGE="C:\Program Files\Microsoft\Edge\Application\msedge.exe"
IF EXIST %EDGE% (
    start "" %EDGE% --kiosk-printing --new-window "%URL%"
    EXIT /B
)

REM --- Fallback: abrir con el navegador predeterminado (sin kiosk-printing) ---
echo ADVERTENCIA: No se encontro Chrome ni Edge. Abriendo con navegador por defecto.
echo El dialogo de impresion del SO aparecera normalmente.
start "" "%URL%"
