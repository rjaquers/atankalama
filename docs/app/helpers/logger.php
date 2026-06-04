<?php
function app_log($msg)
{
    $file = APP_ROOT . "/logs/app.log";
    $line = date("Y-m-d H:i:s") . " | " . $msg . PHP_EOL;
    @file_put_contents($file, $line, FILE_APPEND);
}
