<?php

define('DATA_DIR', __DIR__.'/data');
define('APP_PUBLIC', __DIR__.'/data/public');

require_once __DIR__ .'/../system/suda-console.php';



function get_all_conf_point(string $path)
{
    $files=Storage::readDirFiles($path, true, '/\.php$/');
    $confs = [];
    foreach ($files as $file) {
        $conffile=get_conf_point($file);
        foreach ($conffile as $conf) {
            if (!in_array($conf, $confs)) {
                $confs[]=$conf;
            }
        }
    }
    return $confs;
}

function get_conf_point(string $file)
{
    $content = file($file);
    $confs = [];
    foreach ($content as $line) {
        if (preg_match('/(conf|Config\:\:get)\((\'|")(.+?)\2/i', $line, $match)) {
            if (!in_array($match[3], $confs)) {
                $confs[] = $match[3];
            }
        }
    }
    return $confs;
}

function export_confs(string $path, string $export)
{
    $confs = get_all_conf_point($path);
    sort($confs);
    file_put_contents($export, '');
    foreach ($confs as $conf) {
        file_put_contents($export, $conf.PHP_EOL, FILE_APPEND);
    }
}

function replace_confs(string $path, string $conffile)
{
    $files=Storage::readDirFiles($path, true, '/\.php$/');
    $confread = file($conffile);
    $confs =[];
    foreach ($confread as $line) {
        if (preg_match('/^(.+?)\s+(.+?)$/', $line, $match)) {
            list($line, $target, $replace) = $match;
            $confs['\''.trim($target).'\'']='\''.trim($replace).'\'';
            $confs['"'.trim($target).'"']='"'.trim($replace).'"';
        }
    }
    // var_dump($confs);
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $content = str_replace(array_keys($confs), array_values($confs), $content);
        file_put_contents($file, $content);
    }
}
 

$param=getopt('d:e:r:', ['dir:','export:','replace:']);

if (array_key_exists('d', $param) || array_key_exists('dir', $param)) {
    $dir = $param['d'] ??  $param['dir'];
    if (array_key_exists('e', $param) || array_key_exists('export', $param)) {
        $export = $param['e'] ??  $param['export'];
        export_confs($dir, $export);
    } elseif (array_key_exists('r', $param) || array_key_exists('replace', $param)) {
        $replace = $param['r'] ??  $param['replace'];
        replace_confs($dir, $replace);
    } else {
        print '  [!] -r --replace or -e --export must exist';
    }
} else {
    print '  [!] -d or --dir is must exsit'.PHP_EOL;
}

