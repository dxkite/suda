<?php

use suda\archive\DTOManager;

defined('DTA_TPL') or define('DTA_TPL', SYSTEM_RESOURCE.'/tpl');
$opt=getopt('m:g::p::b::i::d::', ['backup::','import::','data::']);

$module=$opt['m']??null;

if (isset($opt['g']) || isset($opt['p'])) {
    if ($module) {
        DTOManager::parserModuleDto($module);
    } else {
        DTOManager::parserDto();
    }
}

if (isset($opt['b']) || isset($opt['backup'])) {
    $struct=isset($opt['struct']);
    if ($module) {
        DTOManager::backupModuleData($module, $struct);
    } else {
        DTOManager::backup($struct);
    }
}


if (isset($opt['i']) || isset($opt['import'])) {
    if ($module) {
        DTOManager::importModuleStruct($module);
    } else {
        DTOManager::importStruct();
    }
}

if (isset($opt['d']) || isset($opt['data'])) {
    if ($module) {
        DTOManager::importModuleData($module);
    } else {
        DTOManager::importData();
    }
}


if (count($opt)<=0) {
        $help=<<<'help'
Usage: db -gbid
    -m  set module  

    -g
    -p      parser dto data

    -b 
    --backup backup data 

    -i
    -import  import struct

    -d 
    -data  import data 

help;
    echo $help;
}

