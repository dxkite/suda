<?php
require_once __DIR__ .'/suda-console.php';
// 获取选项
$options=getopt('x:i:o:k:b::s:');
/** 
-b 备份
-x 导出
-o 导出的文件
-k 选择性导出
-s 导出成sql
*/
// 获取保存的列表
if (isset($options['k'])) {
    $keep=explode(',', $options['k']);
    Storage::put(TEMP_DIR.'/db.keep', serialize($keep));
} elseif (Storage::exist(TEMP_DIR.'/db.keep')) {
    $keep=unserialize(Storage::get(TEMP_DIR.'/db.keep'));
} else {
    $keep=[];
}

$backup=isset($options['b']);
// $output=


function export(array $keep=[])
{
    Storage::mkdirs(TEMP_DIR.'/database');
    $time=date('Y_m_d_H_i_s');
    Database::export($bkphp=TEMP_DIR.'/database/datebase_'.$time.'.php', $keep);
    Database::exportSQL($bksql=TEMP_DIR.'/database/datebase_'.$time.'.sql', $keep);
    $php=Storage::get($bkphp);
    $php=preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=0', $php);
    Storage::put(DATA_DIR.'/install.php', $php);
    $sql=Storage::get($bksql);
    $sql=preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=0', $sql);
    Storage::put(DATA_DIR.'/install.sql', $sql);
    echo 'created install database file';
}
