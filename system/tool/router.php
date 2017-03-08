<?php
require_once __DIR__ .'/../suda-console.php';
// 获取选项
$options=getopt('a::m:u:c:o::h::t:j::',['router:']);
$help=<<<'HELP'
Usage: --router name [-a] [-o]  [-m POST,GET..] -u url -c class

    -a create URL as admin
    -m request method,use `,` to split 
    -u the request url, use {name:type} to assign args
    -c the called class when request
    -o disavaliable ob cache
    -t the tag name of url

HELP;

if (isset($options['h']) || !isset($options['u']) || !isset($options['c'])){
    echo $help;
    exit;
}

$method=isset($options['m'])?explode(',',strtoupper($options['m'])):[];
$admin=isset($options['a']);
$json=isset($options['j']);
$url=$options['u'];
$class=$options['c'];
$tag=$options['router'];

$ob= !isset($options['o']);

if ($return=Router::visit($method,$url,$class,$tag,$ob,$admin,$json)){
    echo 'created response:'.$class ."\r\n";
    echo 'class file at :' ."\033[34m " . Storage::abspath($return['class']) ."\033[0m\r\n";
    if (isset( $return['template'])){
        echo 'template file at :'."\033[34m " .   Storage::abspath($return['template']) ."\033[0m\r\n";
    }
}else{
    echo 'something error';
}