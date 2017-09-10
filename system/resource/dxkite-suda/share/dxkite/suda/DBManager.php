<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */

namespace dxkite\suda;

use suda\core\Application;
use suda\core\Query;
use suda\core\Storage;
use suda\tool\ArrayHelper;
use suda\archive\DTOReader;

class DBManager
{
    protected $name=null;
    
    protected $dirname=null;

    protected static $instance=null;
    protected static $root=DATA_DIR.'/backup';
    protected static $dtohead=<<< 'Table'
    try {
    Query::beginTransaction();
    $effect=($create=new Query('CREATE DATABASE IF NOT EXISTS `'.conf('database.name').'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;'))->exec();
    if ($create->erron()==0){
        dxkite\suda\DBManager::log('Create Database '.conf('database.name').' Ok,effect '.$effect.' rows');
    }
    else{
        dxkite\suda\DBManager::log('Database '.conf('database.name').'create filed!');
        _D()->error('Database '.conf('database.name').'create filed!');
    }
Table;

    public static $dtoend=<<< 'End'
    Query::commit();
    return true;
    } 
    catch (Exception $e)
    {
        _D()->logException($e);
        dxkite\suda\DBManager::log($e->getLine().':'.$e->getMessage());
        Query::rollBack();
        return false;
    }
End;

    private function __construct()
    {
        if ($laster=self::selectLaster()) {
            self::archive($laster);
        }
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new self;
        }
        return self::$instance;
    }


    /**
     * 选择文档
     *
     * @param string $name 文档名
     * @return $this
     */
    public function archive(string $name=null)
    {
        if (is_null($name)) {
            $name=$this->name??time();
        }
        $this->name=$name;
        $this->dirname=self::$root.'/'.$name;
        return $this;
    }
    
    /**
     * 选择最后的备份文档
     *
     * @return null/string
     */
    public static function selectLaster()
    {
        $readDirs=Storage::readDirs(self::$root);
        if (is_array($readDirs)) {
            sort($readDirs);
            return array_pop($readDirs);
        }
        return null;
    }

    public function parseDTOs(array $modules=null)
    {
        $modules= $modules ?? Application::getModules();
        foreach ($modules as $module) {
            self::log('parse module dtos > '.$module);
            self::parseModuleDTOs($module);
        }
        return $this;
    }

    public function createTables(array $modules=null)
    {
        $modules= $modules ?? Application::getModules();
        foreach ($modules as $module) {
            self::log('create module tables > '.$module);
            self::createTable($module);
        }
        return $this;
    }
    
    
    public static function read(string $backup)
    {
        $config_path=self::$root.'/'.$backup.'/config.php';
        if (Storage::exist($config_path)) {
            $config=include $config_path;
            foreach ($config['module'] as $index=>$name) {
                $module_dir=Application::getModuleDir($name);
                $datafile=self::$root.'/'.$backup.'/data/'.$module_dir.'.php';
                if (file_exists($datafile)) {
                    $config['module_size'][$index]=filesize($datafile);
                } else {
                    $config['module_size'][$index]=0;
                }
            }
            return $config;
        }
        return  false;
    }


    public static function readList()
    {
        $readDirs=Storage::readDirs(self::$root);
        $config=[];
        foreach ($readDirs as $dir) {
            $config_path=self::$root.'/'.$dir.'/config.php';
            if (Storage::exist($config_path)) {
                $conf=include $config_path;
                if (isset($conf['module'])) {
                    $config[$dir]= $conf;
                } else {
                    Storage::rmdirs(self::$root.'/'.$dir);
                }
            }
        }
        return $config;
    }

    public function backupTables(array $modules=null)
    {
        $modules= $modules ?? Application::getModules();
        // 新建备份
        self::archive();
        $config_path=$this->dirname .'/config.php';
        foreach ($modules as $module) {
            $config['time']=intval($this->name);
            $config['module']=$modules;
            Storage::path($this->dirname);
            ArrayHelper::export($config_path, '_config', $config);
            self::log('backup module > '.$module);
            self::parseModuleDTOs($module);
            // 数据表不存在
            if (!(new Query('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=\''.conf('database.name').'\''))->fetch()) {
                self::createTable($module);
            }
            self::backupModule($module);
        }
        return $this;
    }

    public function deleteTables(array $modules=null)
    {
        if (is_null($modules) && $this->dirname) {
            self::log('delete > '.$this->dirname);
            Storage::rmdirs($this->dirname);
            return $this;
        }

        $config_path=$this->dirname.'/config.php';
        if (Storage::exist($config_path)) {
            $config= include $config_path;
            foreach ($modules as $module) {
                _D()->info($module, array_search($module, $config['module']));
                if (($key=array_search($module, $config['module']))!==false) {
                    self::log('delete data > '.$module);
                    unset($config['module'][$key]);
                    $module_dir=Application::getModuleDir($module);
                    $datafile=$this->dirname.'/data/'.$module_dir.'.php';
                    $tablefile=$this->dirname.'/table/'.$module_dir.'.php';
                    $createfile=$this->dirname.'/create/'.$module_dir.'.php';
                    Storage::remove($datafile);
                    Storage::remove($tablefile);
                    Storage::remove($createfile);
                }
            }
            ArrayHelper::export($this->dirname.'/config.php', '_config', $config);
        }
        return $this;
    }

    public function importTables(array $modules=null)
    {
        $modules= $modules ?? Application::getModules();
        foreach ($modules as $module) {
            $module_dir=Application::getModuleDir($module);
            $datafile=$this->dirname.'/data/'.$module_dir.'.php';
            if (Storage::exist($datafile)) {
                self::log('import module >  '.$module);
                self::execFile($datafile);
            } else {
                _D()->warning("file no found :${datafile}");
            }
        }
        return $this;
    }


    public function backupModule(string $module)
    {
        $module_dir=Application::getModuleDir($module);
        $tablefile=$this->dirname.'/table/'.$module_dir.'.php';
        if (Storage::exist($tablefile)) {
            Storage::path($this->dirname.'/data/');
            $datafile=$this->dirname.'/data/'.$module_dir.'.php';
            $tables=include $tablefile;
            file_put_contents($datafile, '<?php  /* create:'.date('Y-m-d H:i:s')."*/\r\n".self::$dtohead."\r\n".str_repeat('#', 64)."\r\n");
            foreach ($tables as $table) {
                self::log('backup '.$module.' table > '.$table);
                $data=self::createDataString($table);
                file_put_contents($datafile, '/* table '.$table.'*/'.$data."\r\n", FILE_APPEND);
            }
            file_put_contents($datafile,  str_repeat('#', 64)."\r\n".self::$dtoend, FILE_APPEND);
            return true;
        }
        return false;
    }

    public static function execFile(string $file)
    {
        if (Storage::exist($file)) {
            return require $file;
        }
        return false;
    }

    public function createTable(string $module)
    {
        $module_dir=Application::getModuleDir($module);
        $path=file_exists($this->dirname)?$this->dirname:TEMP_DIR.'/db';
        $create=$path.'/create/'.$module_dir.'.php';
        if (Storage::exist($create)) {
            self::log('create module tables > '.$create);
            self::execFile($create);
        } else {
            _D()->warning("file no found :${create}");
        }
    }


    public function parseModuleDTOs(string $module)
    {
        $module_dir=Application::getModuleDir($module);
        $table_names=[];
        $dto_path=Application::getModulePath($module).'/resource/dto';
        if (!Storage::isDir($dto_path)) {
            _D()->warning("not exist {$dto_path}\r\n");
            return;
        }
        $create= $this->dirname.'/create/'.$module_dir.'.php';
        Storage::path(dirname($create));
        // 支持 DTO SQL
        $tables=Storage::readDirFiles($dto_path, true, '/\.(dto|sql)$/i', true);
        file_put_contents($create, '<?php  /* create:'.date('Y-m-d H:i:s')."*/\r\n".self::$dtohead."\r\n".str_repeat('#', 64)."\r\n");
        if (file_exists($path=$dto_path.'/__addon_before.sql')) {
            $sqls=file_get_contents($path);
            $query=self::createQueryMessage($sqls, 'query addon before sql');
            file_put_contents($create, '/* __addon__before */'.$query."\r\n", FILE_APPEND);
        }
        foreach ($tables as $table) {
            // DTO File
            if (preg_match('/\.dto$/i', $table)) {
                self::log('parse dto > '.$dto_path.'/'.$table);
                $name=pathinfo($table, PATHINFO_FILENAME);
                $namespace=preg_replace('/\\\\\//', '\\', dirname($table));
                $table_name=self::tablename($namespace, $name);
                $name=ucfirst($name);
                $builder=new DTOReader;
                $builder->load($dto_path.'/'.$table);
                $builder->setName($name);
                $builder->setTableName($table_name);
                $table_names[]=$table_name;
                
                // 创建键列
                $cmtablefields=TEMP_DIR.'/db/fields/'.$table_name.'.php';
                
                $info['fields']=$builder->getFields();
                $info['primaryKey']=key($builder->getPrimaryKey());
                Storage::path(dirname($cmtablefields));
                ArrayHelper::export($cmtablefields, '_fieldinfos',$info);
                self::log('output file > '.$cmtablefields);

                $sql=$builder->getCreateSQL();
                $query=self::createQuery("DROP TABLE IF EXISTS #{{$table_name}}").self::createQueryMessage(self::sqlNameChange($sql), 'create table '.$table_name);
                file_put_contents($create, '/* table '.$table_name.'*/'.$query."\r\n", FILE_APPEND);
            
            } else {
                self::log('parse sql > '.$dto_path.'/'.$table);
                $name=pathinfo($table, PATHINFO_FILENAME);
                $namespace=preg_replace('/\\\\\//', '\\', dirname($table));
                $table_name=self::tablename($namespace, $name);
                $sql=Storage::get($dto_path.'/'.$table);
                $sql=preg_replace('/CREATE\s+TABLE\s+([`"\'])?'.$name.'(?(1)["\'`])/i', 'CREATE TABLE `#{'.$table_name.'}` ', $sql);
                $query=self::createQuery("DROP TABLE IF EXISTS #{{$table_name}}").self::createQueryMessage($sql, 'create table '.$table_name);
                file_put_contents($create, '/* table '.$table_name.'*/'.$query."\r\n", FILE_APPEND);
            }
        }
        if (file_exists($path=$dto_path.'/__addon_after.sql')) {
            $sqls=file_get_contents($path);
            $query=self::createQueryMessage($sqls, 'query addon after sql');
            file_put_contents($create, '/* __addon_after */'.$query."\r\n", FILE_APPEND);
        }
        $tablefile=$this->dirname.'/table/'.$module_dir.'.php';
        Storage::path(dirname($tablefile));
        ArrayHelper::export($tablefile, '_tables', $table_names);

        $cmtablefile=TEMP_DIR.'/db/table/'.$module_dir.'.php';
        Storage::path(dirname($cmtablefile));
        ArrayHelper::export($cmtablefile, '_tables', $table_names);

        file_put_contents($create, str_repeat('#', 64)."\r\n".self::$dtoend, FILE_APPEND);
        
        $cmcreate=TEMP_DIR.'/db/create/'.$module_dir.'.php';
        Storage::path(dirname($cmcreate));
        Storage::copy($create,$cmcreate);
        
        self::log('output file > '.$cmcreate);
        self::log('output file > '.$create);
        self::log('output tablefile > '.$cmtablefile);
        self::log('output tablefile > '.$tablefile);
        return true;
    }

    public static function log(string $message)
    {
        _D()->trace($message);
        echo $message.'<br/>';
        echo str_repeat(' ', 4096);
        flush();
        ob_flush();
    }

    public static function getTableStruct(string $table)
    {
        $table_info=($q=new Query("show create table {$table};"))->fetch();
        if ($table_info) {
            return $table_info['Create Table'];
        }
        return false;
    }

    protected static function createQueryMessage(string $sql, string $message)
    {
        _D()->trace($sql, $message);
        $data=base64_encode($sql);
        $message=base64_encode($message);
        $name=md5($sql);
        $string='$rows=($_'.$name.'=new Query(base64_decode(\''.$data.'\')))->exec();';
        $string.='dxkite\suda\DBManager::log($_'.$name.'->erron()==0? base64_decode(\''.$message.'\'). " effect {$rows} rows"  :\'query\'.base64_decode(\''.$data.'\').\' error\');';
        return $string;
    }

    protected static function createQuery(string $sql)
    {
        $data=base64_encode($sql);
        return ' (new Query(base64_decode(\''.$data.'\')))->exec();';
    }

    protected static function tablename($namespace, $name)
    {
        if ($namespace==='.') {
            return $name;
        }
        if (preg_match('/'.preg_quote(DIRECTORY_SEPARATOR.$name, '/').'$/i', $namespace)) {
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

    protected static function sqlNameChange(string $sql)
    {
        return preg_replace('/CREATE\s+TABLE\s+([`])?(\w+)(?(1)[`])/i', 'CREATE TABLE `#{$2}` ', $sql);
    }

    public static function createDataString(string $table)
    {
        if ($sql=self::getTableDataString(conf('database.prefix').$table)) {
            return self::createQueryMessage($sql, 'inport table '.$table.' data');
        }
        return '/* ' .$table .' : value is empty */';
    }

    public static function getTableDataString(string $table)
    {
        $q=new Query('SELECT * FROM '.$table.' WHERE 1;', [], true);
        $columns=(new Query('SHOW COLUMNS FROM '.$table.';'))->fetchAll();
        $key='(';
        foreach ($columns  as $column) {
            $key.='`'.$column['Field'].'`,';
        }
        $key=rtrim($key, ',').')';
        if ($q) {
            $sqlout='INSERT INTO `'.$table.'` '.$key.' VALUES ';
            $first=true;
            while ($values=$q->fetch()) {
                $sql='';
                if ($first) {
                    $first=false;
                } else {
                    $sql.=',';
                }
                $sql.='(';
                $columns='';
                foreach ($values as $val) {
                    $columns.='\''.addslashes($val).'\',';
                }
                $columns=rtrim($columns, ',');
                $sql.= $columns;
                $sql.=')';
                $sqlout.=$sql;
            }
            if ($first) {
                return false;
            }
            return $sqlout;
        }
        return false;
    }
}
