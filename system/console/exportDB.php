<?php

require_once __DIR__ .'/../suda-console.php';


Storage::mkdirs(TEMP_DIR.'/database');
$time=date('Y_m_d_H_i_s');
Database::export($bkphp=TEMP_DIR.'/database/datebase_'.$time.'.php',['site_setting']);
Database::exportSQL($bksql=TEMP_DIR.'/database/datebase_'.$time.'.sql',['site_setting']);
$php=Storage::get($bkphp);
$php=preg_replace('/AUTO_INCREMENT=\d+/','AUTO_INCREMENT=0',$php);
Storage::put(RESOURCE_DIR.'/install.php',$php);
$sql=Storage::get($bksql);
$sql=preg_replace('/AUTO_INCREMENT=\d+/','AUTO_INCREMENT=0',$sql);
Storage::put(RESOURCE_DIR.'/install.sql',$sql);
echo 'created install database file';