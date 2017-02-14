<?php


require_once __DIR__ .'/../suda-console.php';


defined('DTA_TPL') or define('DTA_TPL', __DIR__ .'/tpl');
use template\Compiler as TplBuilder;
use archive\DTOReader;

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
    if ($namespace==='.') return $name;
    return ($name===$namespace?$name:preg_replace_callback('/(\\\\|[A-Z])/', function ($match) {
        if ($match[0]==='\\') {
            return '_';
        } else {
            return '_'.strtolower($match[0]);
        }
    }, $namespace.'\\'.$name));
}

function queryCreateSQL(string $sql){
    return preg_replace('/CREATE TABLE `(.+?)` /','CREATE TABLE `#{$1}` ',$sql);
}
compileAll();

$params=array_slice($argv, 1);
$src=isset($params[0])?$params[0]:DATA_DIR.'/dto';
$dist=isset($params[1])?$params[1]:DATA_DIR.'/db';
$outsql=isset($params[2])?$params[2]:DATA_DIR.'/dbcreator.sql';
$querysql=isset($params[3])?$params[3]:DATA_DIR.'/dbcreator.php';
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
    Storage::mkdirs(dirname($output));
    $builder->export(DTA_TPL.'/api.tpl', $output);
    $sql=$builder->getCreateSQL();
    $query="(new Query('DROP TABLE IF EXISTS #{{$table_name}}'))->exec();".Database::queryCreateTable(queryCreateSQL($sql),$table_name);
    file_put_contents($outsql, "\r\n".$sql."\r\n\r\n", FILE_APPEND);
    file_put_contents($querysql, "\r\n".$query."\r\n\r\n", FILE_APPEND);
}
    file_put_contents($querysql, $end , FILE_APPEND);