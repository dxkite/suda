<?php

namespace dxkite\suda;

use suda\core\{Storage,Application,Database};
use suda\tool\{Value,ArrayHelper};

// 数据表对象文件读取器
class DTOManager
{
    public static $dtohead=<<< Table

    try {
    /** Open Transaction Avoid Error **/
    Query::beginTransaction();


    \$effect=(\$create=new Query('CREATE DATABASE IF NOT EXISTS '.conf('database.name').';'))->exec();
    if (\$create->erron()==0){
            echo 'Create Database '.conf('database.name').' Ok,effect '.\$effect.' rows'."\\r\\n";
        }
        else{
            die('Database '.conf('database.name').'create filed!');   
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
    public static function parserDto(string $module=null)
    {
        $modules=Application::getModules();
        Storage::rmDirs(TEMP_DIR.'/database/');
        foreach ($modules as $module) {
            echo 'parser module '.$module."\r\n";
            self::parserModuleDto($module);
        }
    }

    public static function importData()
    {
        $modules=Application::getModules();
        foreach ($modules as $module) {
            echo 'importData '.$module."\r\n";
            self::importModuleData($module);
        }
    }

    public static function importStruct()
    {
        $modules=Application::getModules();
        foreach ($modules as $module) {
            echo 'importStruct '.$module."\r\n";
            self::importModuleStruct($module);
        }
    }
    public static function parserModuleDto(string $module)
    {
        $module_dir=Application::getModuleDir($module);
        $dto_path=MODULES_DIR.'/'.$module_dir.'/resource/dto';

        if (!Storage::isDir($dto_path)) {
            print "not exist {$dto_path}\r\n";
            return;
        }
        $create_path=TEMP_DIR.'/database/structs';
        $table_path=TEMP_DIR.'/database/table-info';
        $table_names=[];
        $phpfile=$create_path.'/'.$module.'-dto.php';
        Storage::path($create_path);
        $tables=Storage::readDirFiles($dto_path, true, '/\.dto$/', true);
        file_put_contents($phpfile, '<?php  /* create:'.date('Y-m-d H:i:s')."*/\r\n".self::$dtohead);
        foreach ($tables as $table) {
            $name=pathinfo($table, PATHINFO_FILENAME);
            $namespace=preg_replace('/\\\\\//', '\\', dirname($table));
            $table_name=self::tablename($namespace, $name);
            $name=ucfirst($name);

            if ($namespace!==$name) {
                $namespace='dto\\'.$namespace;
            } else {
                $namespace='dto';
            }
            $builder=new DTOReader;
            $builder->load($dto_path.'/'.$table);
            $builder->setName($name);
            $builder->setNamespace($namespace);
            $builder->setTableName($table_name);
            $table_names[]=$table_name;
            
            $output=TEMP_DIR.'/database/php/'.preg_replace('/\\\\/', DIRECTORY_SEPARATOR, $namespace).'/'.$name.'.php';
            Storage::path(dirname($output));

            $autopath=$builder->export(DTA_TPL.'/api.tpl', $output);
            $sql=$builder->getCreateSQL();
            $query="(new Query('DROP TABLE IF EXISTS #{{$table_name}}'))->exec();".Database::queryCreateTable(self::sql($sql), $table_name);

            file_put_contents($phpfile, "\r\n".$query."\r\n\r\n", FILE_APPEND);
            echo 'output  manager class template file: '."\033[34m".$autopath."\033[0m\r\n";
        }
        Storage::path($table_path);
        ArrayHelper::export($table_path.'/'.$module.'.php', '_tables', $table_names);
        file_put_contents($phpfile, self::$dtoend, FILE_APPEND);
        echo 'output file: '."\033[34m".$phpfile."\033[0m\r\n";
    }

    public static function backupModuleData(string $module=null, bool $struct=false)
    {
        Storage::path(DATA_DIR.'/backup/');
        $tables=[];
        if ($module) {
            if (!$tables=self::modulesTables($module)) {
                return false;
            }
        }
        $bk=$module?:'app-data';
        Database::export($querysql=DATA_DIR.'/backup/'.$bk.'.php', $tables, $struct);
        Database::exportSQL($outsql=DATA_DIR.'/backup/'.$bk.'.sql', $tables, $struct);
        echo 'backup to '."\033[34m".DATA_DIR.'/backup/'."\033[0m\r\n";
        echo 'output sql  file: '."\033[34m".$outsql."\033[0m\r\n";
        echo 'output file: '."\033[34m".$querysql."\033[0m\r\n";
    }

    public static function importModuleData(string $module)
    {
        $file=DATA_DIR.'/backup/'.$module.'.php';
        if (Storage::exist($file)) {
            Database::import($file);
        } else {
            echo "file no found :${file} \r\n";
        }
    }

    public static function importModuleStruct(string $module)
    {
        $file=TEMP_DIR.'/database/structs/'. $module.'-dto.php';
        if (Storage::exist($file)) {
            Database::import($file);
        } else {
            echo "file no found :${file} \r\n";
        }
    }

    public static function backup(bool $struct=falses)
    {
        $modules=Application::getModules();
        Storage::path(DATA_DIR.'/backup/');
        Storage::copydir(DATA_DIR.'/backup/', TEMP_DIR.'/backup/'.time());
        Storage::rmdirs(DATA_DIR.'/backup/');
        foreach ($modules as $module) {
            echo 'backup module '.$module."\r\n";
            self::backupModuleData($module, $struct);
        }
    }

    protected static function modulesTables(string $module)
    {
        if (file_exists($path=TEMP_DIR.'/database/table-info/'.$module.'.php')) {
            return require $path;
        }
        return false;
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
        if (preg_match('/'.preg_quote(DIRECTORY_SEPARATOR.$name,'/').'$/i', $namespace)) {
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
