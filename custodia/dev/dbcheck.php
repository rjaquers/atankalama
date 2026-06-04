<?php

declare(strict_types=1);
require_once __DIR__.'/../connections/conec6.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    $db = db();
    echo "db() OK\n";
    echo 'host_info: '.$db->host_info."\n";
    $res = $db->query('SELECT DATABASE() AS dbname, 1 AS uno');
    $row = $res ? $res->fetch_assoc() : null;
    echo 'DATABASE(): '.($row['dbname'] ?? '(null)')."\n";
    echo 'SELECT 1: '.($row['uno'] ?? '(null)')."\n";
} catch (\Throwable $e) {
    echo 'EXCEPTION: '.$e->getMessage()."\n";
}
