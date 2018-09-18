<?php

define('DATA_DIR', __DIR__.'/data');
define('APP_PUBLIC', __DIR__.'/data/public');

require_once __DIR__ .'/../system/suda-console.php';



function get_all_hook_point(string $path)
{
    $files=Storage::readDirFiles($path, true, '/\.php$/');
    $hooks = [];
    foreach ($files as $file) {
        $hookfile=get_hook_point($file);
        foreach ($hookfile as $hook) {
            if (!in_array($hook,$hooks)) {
                $hooks[]=$hook;
            }
        }
    }
    return $hooks;
}

function get_hook_point(string $file)
{
    $content = file($file);
    $hooks = [];
    foreach ($content as $line) {
        if (preg_match('/(?:Hook\:\:|hook\(\)\-\>)exec(\w+)?\((\'|")(.+)\2/i', $line, $match)) {
            if (!in_array($match[3],$hooks)){
                $hooks[] = $match[3];
            }
        }
    }
    return $hooks;
}

function export_hooks(string $path)
{
    $hooks = get_all_hook_point(SYSTEM_DIR);
    file_put_contents($path,'');
    foreach ($hooks as $hook) {
        file_put_contents($path, $hook.PHP_EOL, FILE_APPEND);
    }
}

function replace_hooks(string $path,string $hookfile)
{
    $files=Storage::readDirFiles($path, true, '/\.php$/');
    $hookread = file($hookfile);
    $hooks =[];
    foreach ($hookread as $line) {
        if (preg_match('/^(.+?)\s+(.+?)$/',$line,$match)) {
            list($line,$target,$replace) = $match;
            $hooks['\''.trim($target).'\'']='\''.trim($replace).'\'';
            $hooks['"'.trim($target).'"']='\''.trim($replace).'\'';
        }
    }
    // var_dump($hooks);
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $content = str_replace(array_keys($hooks),array_values($hooks),$content);
        file_put_contents($file,$content);
    }
}

// export_hooks(__DIR__.'/hooks.txt');

replace_hooks(SYSTEM_DIR,__DIR__.'/hooks.replace.txt');

