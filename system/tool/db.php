<?php
// 获取选项
$options=getopt('i::b::k:f:g::h::');
/** 
-b 备份
-k 选择性导出
-i 导出安装sql
-f 导入文件
*/
if (isset($options['h']) || count($options)===0){
$help=<<<'help'

Usage: db -bkifgrosp 

  -b backup database
  -k set install sql file keep tables
  -i create install database file
  -f import database from  php file
  ---------------------------------------
  -g generate dto file
    -r raw dto directory 
    -o output directory
    -s set output sql path
    -p set output php path

help;
    echo $help;
    exit(0);
}
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

if ($backup) {
    Storage::path(DATA_DIR.'/backup');
    $time=date('Y_m_d_H_i_s');
    Database::export(DATA_DIR.'/backup/backup_'.$time.'.php');
    Database::exportSQL(DATA_DIR.'/backup/backup_'.$time.'.sql');
    echo 'backup to '.DATA_DIR.'/backup/'."\r\n";
}

if (isset($options['i'])) {
    $time=date('Y_m_d_H_i_s');
    Storage::path(TEMP_DIR.'/database/');
    Database::export($bkphp=TEMP_DIR.'/database/install_temp.php', $keep);
    Database::exportSQL($bksql=TEMP_DIR.'/database/install_temp.sql', $keep);
    $php=Storage::get($bkphp);
    $php=preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=0', $php);
    Storage::put(DATA_DIR.'/install.php', $php);
    $sql=Storage::get($bksql);
    $sql=preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=0', $sql);
    Storage::put(DATA_DIR.'/install.sql', $sql);
    echo 'created install database file'."\r\n";
}

if (isset($options['f'])){
    print 'import file>'.$options['f']."\r\n";
    Database::import($options['f']);
}

defined('DTA_TPL') or define('DTA_TPL', SYS_RES.'/tpl');
use suda\template\Compiler as TplBuilder;
use suda\archive\DTOReader;

function compileAll()
{
    $files=Storage::readDirFiles(DTA_TPL, true, '/\.raw$/');
    foreach ($files as $file) {
        $text=(new TplBuilder())->compileText(file_get_contents($file));
        $to=preg_replace('/\.raw/', '.tpl', $file);
        file_put_contents($to, $text);
    }
}

function tablename($namespace, $name)
{
    if ($namespace==='.') {
        return $name;
    }
    return ($name===$namespace?$name:preg_replace_callback('/(\\\\|[A-Z])/', function ($match) {
        if ($match[0]==='\\') {
            return '_';
        } else {
            return '_'.strtolower($match[0]);
        }
    }, $namespace.'\\'.$name));
}

function queryCreateSQL(string $sql)
{
    return preg_replace('/CREATE TABLE `(.+?)` /', 'CREATE TABLE `#{$1}` ', $sql);
}

function generate() {
    compileAll();

    $params=getopt('r:o:s:p:m:');
    $module=isset($params['m'])?$params['m']:'';
    $src=isset($params['r'])?$params['r']: $module?MODULES_DIR.'/'.$module.'/resource/dto':DATA_DIR.'/dto';
    if (!is_dir($src)){
        echo 'no such dir:'.$src;
    }
    $path=$module?MODULES_DIR.'/'.$module.'/resource/':DATA_DIR;
    Storage::path($path);
    $dist=isset($params['o'])?$params['o']: $path.'/db';
    $outsql=isset($params['s'])?$params['s']: $path.'/'.$module.'_creator.sql';
    $querysql=isset($params['p'])?$params['p']: $path.'/'.$module.'_creator.php';
    $tables=Storage::readDirFiles($src, true, '/\.dto$/', true);
    $head=<<< Table

    try {
    /** Open Transaction Avoid Error **/
    Query::beginTransaction();


    \$effect=(\$create=new Query('CREATE DATABASE IF NOT EXISTS '.Config::get('database.name').';'))->exec();
    if (\$create->erron()==0){
            echo 'Create Database '.Config::get('database.name').' Ok,effect '.\$effect.' rows'."\\r\\n";
        }
        else{
            die('Database '.Config::get('database.name').'create filed!');   
        }

Table;

    $end=<<< 'End'
    /** End Querys **/
    Query::commit();
    return true;
    } 
    catch (Exception $e)
    {
        Query::rollBack();
    return false;
    }
End;
    file_put_contents($outsql, '-- create:'.date('Y-m-d H:i:s')."\r\n");
    file_put_contents($querysql, '<?php  /* create:'.date('Y-m-d H:i:s')."*/\r\n".$head);

    foreach ($tables as $table) {
        $name=pathinfo($table, PATHINFO_FILENAME);
        $namespace=preg_replace('/\\\\\//', '\\', dirname($table));
        $table_name=tablename($namespace, $name);
        if ($namespace!==$name) {
            $namespace='dto\\'.$namespace;
        } else {
            $namespace='dto';
        }
        
        $name=ucfirst($name);
        $builder=new DTOReader;
        $builder->load($src.'/'.$table);
        $builder->setName($name);
        $builder->setNamespace($namespace);
        $builder->setTableName($table_name);
        $output=TEMP_DIR.'/'.preg_replace('/\\\\/', DIRECTORY_SEPARATOR, $namespace).'/'.$name.'.php';
        Storage::path(dirname($output));
        $builder->export(DTA_TPL.'/api.tpl', $output);
        $sql=$builder->getCreateSQL();
        $query="(new Query('DROP TABLE IF EXISTS #{{$table_name}}'))->exec();".Database::queryCreateTable(queryCreateSQL($sql), $table_name);
        file_put_contents($outsql, "\r\n".$sql."\r\n\r\n", FILE_APPEND);
        file_put_contents($querysql, "\r\n".$query."\r\n\r\n", FILE_APPEND);
    }
    file_put_contents($querysql, $end, FILE_APPEND);
}

if (isset($options['g'])){
    generate();
}
