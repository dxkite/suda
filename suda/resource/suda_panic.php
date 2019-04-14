<?php
function suda_panic($error_type, $error_message, $error_code = 500)
{
    date_default_timezone_set(defined('DEFAULT_TIMEZONE')?DEFAULT_TIMEZONE:'PRC');
    header('HTTP/1.1 500 Internal Server Error');
    header('Status:500 Internal Server Error');
    ob_start();
    $error_sort_type = strpos($error_type, '\\') === false ? $error_type : \substr($error_type, \strrpos($error_type, '\\') + 1);
    include __DIR__ .'/error.php';
    header('Content-Type:text/html; charset=UTF-8');
    header('Content-Length:'.ob_get_length());
    die(ob_get_clean());
}
