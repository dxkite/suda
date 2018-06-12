<?php 
function suda_panic($error_type, $error_message, $error_code=null)
{
    date_default_timezone_set(defined('DEFAULT_TIMEZONE')?DEFAULT_TIMEZONE:'RPC');
    if (is_null($error_code)) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Status:500 Internal Server Error');
    } else {
        $status = parse_ini_file(__DIR__.'/status.ini');
        header('HTTP/1.1 '.$error_code.' '. $status[$error_code]);
        header('Status: '.$error_code.' '.  $status[$error_code]);
    }
    ob_start();
    include __DIR__ .'/error.php';
    header('Content-Type:text/html; charset=UTF-8');
    header('Content-Length:'.ob_get_length());
    die(ob_get_clean());
}
