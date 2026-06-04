<?php

require __DIR__.'/vendor/autoload.php';
echo class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class) ? 'OK' : 'FAIL';
