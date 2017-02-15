<?php

require_once __DIR__ .'/suda-console.php';

$params=array_slice($argv, 2);
if (isset($argv[1])) {
    print "\033[36mcall \033[34m".$argv[1]."(\033[32m".implode(',', $params)."\033[34m)\033[0m\r\n";
    print "\033[33m-----------------------------------\033[0m\r\n";
    print "\033[33m# Function echo\033[0m\r\n";
    print "\033[33m-----------------------------------\033[0m\r\n";
    $return=(new suda\tool\Command($argv[1]))->exec($params);
    print "\033[33m# return value\033[0m\r\n";
    print "\033[33m-----------------------------------\033[0m\r\n";
    var_dump($return);
} else {
    print "\033[31mplease enter caller string\r\n\033[0m";
}