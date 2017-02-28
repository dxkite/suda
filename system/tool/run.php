<?php
require_once __DIR__ .'/../suda-console.php';
$get=getopt('m:c:');
if (isset($get['m']) && isset($get['c'])){
    Application::activeModule($get['m']);
    (new \suda\tool\Command($get['c']))->exec();
}else{
    echo 'lack of arguments!';
}