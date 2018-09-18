<?php

define('DATA_DIR', __DIR__.'/data');
define('APP_PUBLIC', __DIR__.'/data/public');

require_once __DIR__ .'/../system/suda-console.php';



function get_all_hook_point(string $path)
{
    $files=Storage::readDirFiles($path, true, '/\.(php|json)$/');
    $hooks = [];
    foreach ($files as $file) {
        $hookfile=get_hook_point($file);
        foreach ($hookfile as $hook) {
            if (!in_array($hook, $hooks)) {
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
            if (!in_array($match[3], $hooks)) {
                $hooks[] = $match[3];
            }
        }
    }
    return $hooks;
}

function export_hooks(string $path, string $export)
{
    $hooks = get_all_hook_point($path);
    sort($hooks);
    file_put_contents($export, '');
    foreach ($hooks as $hook) {
        file_put_contents($export, $hook.PHP_EOL, FILE_APPEND);
    }
}

function replace_hooks(string $path, string $hookfile)
{
    $files=Storage::readDirFiles($path, true, '/\.(php|json)$/');
    $hookread = file($hookfile);
    $hooks =[];
    foreach ($hookread as $line) {
        if (preg_match('/^(.+?)\s+(.+?)$/', $line, $match)) {
            list($line, $target, $replace) = $match;
            $hooks['\''.trim($target).'\'']='\''.trim($replace).'\'';
            $hooks['"'.trim($target).'"']='"'.trim($replace).'"';
        }
    }
    // var_dump($hooks);
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $content = str_replace(array_keys($hooks), array_values($hooks), $content);
        file_put_contents($file, $content);
    }
}
 

$param=getopt('d:e:r:', ['dir:','export:','replace:']);

if (array_key_exists('d', $param) || array_key_exists('dir', $param)) {
    $dir = $param['d'] ??  $param['dir'];
    if (array_key_exists('e', $param) || array_key_exists('export', $param)) {
        $export = $param['e'] ??  $param['export'];
        export_hooks($dir, $export);
    } elseif (array_key_exists('r', $param) || array_key_exists('replace', $param)) {
        $replace = $param['r'] ??  $param['replace'];
        replace_hooks($dir, $replace);
    } else {
        print '  [!] -r --replace or -e --export must exist';
    }
} else {
    print '  [!] -d or --dir is must exsit'.PHP_EOL;
}

