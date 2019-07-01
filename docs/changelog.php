<?php

$content = shell_exec('git log --pretty=format:%s '.$argv[1].'..'.$argv[2]);


function match_array(string $content, array $in_array)
{
    foreach ($in_array as $key) {
        if (strpos($content, $key) !== false) {
            return true;
        }
    }
    return false;
}

function create_name(string $name, string $text)
{
    if (strpos($name, '修') !== false) {
        return '修正';
    }
    if (match_array($text, ['优化']) !== false) {
        return '优化';
    }
    if (match_array($text, ['删除']) !== false) {
        return '删除';
    }
    return $name;
}


$lines = explode("\n", $content);
$info = [];

foreach ($lines as $line) {
    $msg = trim($line);
    if (strpos($msg, '：')) {
        list($name, $text) = explode('：', $msg, 2);
        $name = create_name($name, $text);
        if (match_array($text, ['README', 'docs', '文档', '注释']) === false) {
            $info[$name][] = $text;
        }
    }
}

$textOutput = '';

foreach ($info as $name => $logs) {
    if (count($logs)) {
        $textOutput .= '- '. $name. PHP_EOL;
        $logs = array_unique($logs);
        foreach ($logs as $log) {
            $textOutput .= '    - '. $log. PHP_EOL;
        }
    }
}


file_put_contents($argv[3] ?? 'changelog', $textOutput);
