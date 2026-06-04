<?php
if (($_GET['k'] ?? '') !== 'rjq2026') { http_response_code(403); exit; }
session_start();
header('Content-Type: text/plain');
echo 'SESSION: ' . print_r($_SESSION, true);
echo 'COOKIE: '  . print_r($_COOKIE, true);
echo 'session.name: '        . ini_get('session.name') . "\n";
echo 'session.cookie_path: ' . ini_get('session.cookie_path') . "\n";
echo 'session.save_path: '   . ini_get('session.save_path') . "\n";
