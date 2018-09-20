<?php
require_once __DIR__ .'/../../system/suda-console.php';



function get_all_lang_point(string $path)
{
    $files=Storage::readDirFiles($path, true, '/\.(php|json)$/');
    $langs = [];
    foreach ($files as $file) {
        $langfile=get_lang_point($file);
        foreach ($langfile as $lang) {
            if (!in_array($lang, $langs)) {
                $langs[]=$lang;
            }
        }
    }
    return $langs;
}

function get_lang_point(string $file)
{
    $content = file($file);
    $langs = [];
    foreach ($content as $line) {
        if (preg_match('/__\((\'|")(.+?)(?<!\\\\)\1/i', $line, $match)) {
            if (!in_array($match[2], $langs)) {
                $langs[] = $match[1].$match[2].$match[1];
            }
        }
    }
    return $langs;
}

function export_langs(string $path, string $export)
{
    $langs = get_all_lang_point($path);
    sort($langs);
    $export_arr = array_combine($langs,$langs);
    file_put_contents($export,'<?php return '.PHP_EOL.var_export($export_arr,true).';');
}

function replace_langs(string $path, string $langfile)
{
    $files=Storage::readDirFiles($path, true, '/\.(php|json)$/');
    $langs = include $langfile;
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $content = str_replace(array_keys($langs), array_values($langs), $content);
        file_put_contents($file, $content);
    }
}
 

$param=getopt('d:e:r:', ['dir:','export:','replace:']);

if (array_key_exists('d', $param) || array_key_exists('dir', $param)) {
    $dir = $param['d'] ??  $param['dir'];
    if (array_key_exists('e', $param) || array_key_exists('export', $param)) {
        $export = $param['e'] ??  $param['export'];
        export_langs($dir, $export);
    } elseif (array_key_exists('r', $param) || array_key_exists('replace', $param)) {
        $replace = $param['r'] ??  $param['replace'];
        replace_langs($dir, $replace);
    } else {
        print '  [!] -r --replace or -e --export must exist';
    }
} else {
    print '  [!] -d or --dir is must exsit'.PHP_EOL;
}

