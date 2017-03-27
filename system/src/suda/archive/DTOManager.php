<?php

namespace suda\archive;

use Storage;
use suda\core\{Application,Database};
use suda\tool\Value;

// 数据表对象文件读取器
class DTOManager
{
    public static $dtohead=<<< Table

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

    public static $dtoend=<<< 'End'
    /** End Querys **/
    Query::commit();
    return true;
    } 
    catch (Exception $e)
    {
        echo "\t{$e->getLine()}:\033[31m{$e->getMessage()}\033[0m\r\n";
        Query::rollBack();
        return false;
    }
End;
    public static function parserDto()
    {
        $modules=conf('app.modules');
        foreach ($modules as $module) {
            echo 'parser module '.$module."\r\n";
            self::parserModuleDto($module);
        }
    }
    public static function importData()
    {
    }
    public static function importStruct()
    {
    }
    public static function parserModuleDto(string $module)
    {
        $module_dir=Application::moduleDir($module);
        $dto_path=MODULES_DIR.'/'.$module_dir.'/resource/dto';

        if (!Storage::isDir($dto_path)) {
            print "not exist {$dto_path}\r\n";
            return;
        }

        $create_path=TEMP_DIR.'/db-creater/';
        $phpfile=$create_path.'/'.$module.'-dto.php';
        Storage::path($create_path);
        $tables=Storage::readDirFiles($dto_path, true, '/\.dto$/', true);
        file_put_contents($phpfile, '<?php  /* create:'.date('Y-m-d H:i:s')."*/\r\n".self::$dtohead);
        foreach ($tables as $table) {
            $name=pathinfo($table, PATHINFO_FILENAME);
            $namespace=preg_replace('/\\\\\//', '\\', dirname($table));
            $table_name=self::tablename($namespace, $name);
            $name=ucfirst($name);


            $builder=new DTOReader;
            $builder->load($dto_path.'/'.$table);
            $builder->setName($name);
            $builder->setNamespace($namespace);
            $builder->setTableName($table_name);

            $output=TEMP_DIR.'/db-option/'.preg_replace('/\\\\/', DIRECTORY_SEPARATOR, $namespace).'/'.$name.'.php';
            Storage::path(dirname($output));

            $autopath=$builder->export(DTA_TPL.'/api.tpl', $output);
            $sql=$builder->getCreateSQL();
            $query="(new Query('DROP TABLE IF EXISTS #{{$table_name}}'))->exec();".Database::queryCreateTable(self::sql($sql), $table_name);

            file_put_contents($phpfile, "\r\n".$query."\r\n\r\n", FILE_APPEND);
            echo 'output  manager class template file: '."\033[34m".$autopath."\033[0m\r\n";
        }
        file_put_contents($phpfile, self::$dtoend, FILE_APPEND);
        echo 'output file: '."\033[34m".$phpfile."\033[0m\r\n";
    }


    public static function importModuleData(string $module)
    {
    }
    public static function importModuleStruct(string $module)
    {
    }
    public static function backupOld(string $module)
    {
    }

    protected static function sql(string $sql)
    {
        return preg_replace('/CREATE TABLE `(.+?)` /', 'CREATE TABLE `#{$1}` ', $sql);
    }

    protected static function tablename($namespace, $name)
    {
        if ($namespace==='.') {
            return $name;
        }
        if (preg_match('/'.preg_quote(DIRECTORY_SEPARATOR.$name).'$/i', $namespace)) {
            $namespace=preg_replace('/'.preg_quote(DIRECTORY_SEPARATOR.$name).'$/i', '', $namespace);
        }
        return ($name===$namespace?$name:preg_replace_callback('/(\\\\|[A-Z])/', function ($match) {
            if ($match[0]==='\\') {
                return '_';
            } else {
                return '_'.strtolower($match[0]);
            }
        }, $namespace.'\\'.$name));
    }
}
