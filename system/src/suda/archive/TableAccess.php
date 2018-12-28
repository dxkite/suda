<?php
namespace suda\archive;

use suda\archive\creator\Table as TableSQLCreator;
use suda\core\Storage;
use suda\tool\ArrayHelper;
use suda\exception\TableException;

/**
 * 表创建器
 * 用于创建和数据表的链接，如果表不存在则创建
 */
abstract class TableAccess
{
    // 数据库连接
    protected $connection;
    /**
     * 数据表当前列
     *
     * @var array|null
     */
    protected $fields=null;
    protected $primaryKey;
    protected $tableName;
    protected $cachePath;
    protected $creator;
    /**
     * 数据表所有当前列
     *
     * @var array|null
     */
    protected $allFields = null;
    /**
     * 设置导出列大小
     *
     * @var array|null
     */
    protected $exportFields = null;

    /**
     * 设置导出数据分块大小
     *
     * @var integer
     */
    protected $exportBlockSize=2000;

    public function __construct(string $tableName, Connection $connection =null)
    {
        // 默认ID为表主键
        $this->primaryKey[]='id';
        $this->tableName  = $tableName;
        $this->connection = $connection ?? Connection::getDefaultConnection();
        $this->connection->connect();
        $this->cachePath  = CACHE_DIR.'/database/'.$this->connection->name.'/fields/'.$this->tableName.'.php';
        // 读取类名作为表名
        $this->initTableFields();
    }

    /**
     * 创建数据表
     *
     * @return boolean
     */
    public function createTable()
    {
        return $this->initFromTable($this->getCreator());
    }
    
    public function getCreateSql():string
    {
        return (string)$this->getCreator();
    }

    public function getCreator()
    {
        if (is_null($this->creator)) {
            $this->creator=$this->onBuildCreator(new TableSQLCreator($this->tableName, 'utf8'));
        }
        return $this->creator;
    }


    abstract protected function onBuildCreator($table);
    

    public function begin()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollBack()
    {
        $this->connection->rollBack();
    }

    
    /**
     * 清空数据表
     *
     * @return int 返回影响的数据行数目
     */
    public function truncate():int
    {
        return (new RawQuery($this->connection, 'TRUNCATE TABLE `#{'.$this->tableName.'}`;'))->exec();
    }
  
    /**
     * 删除数据表
     *
     * @return int 返回影响的数据行数目
     */
    public function drop():int
    {
        return (new RawQuery($this->connection, 'DROP TABLE IF EXISTS `#{'.$this->tableName.'}`;'))->exec();
    }

    /**
     * 导出数据到文件
     *
     * @param string $path
     * @return bool|int
     */
    public function export(string $path)
    {
        $offset=0;
        storage()->path(dirname($path));
        while ($data=$this->getDataStringLimit($this->exportBlockSize, $offset)) {
            $offset+=$this->exportBlockSize;
            $base64=base64_encode($data);
            $sha1=sha1($base64);
            storage()->put($path, $this->tableName.','.time().','.$sha1.',base64;'.$base64.PHP_EOL, FILE_APPEND);
        }
        return true;
    }

    /**
     * 从导出文件中恢复数据
     *
     * @param string $path
     * @return bool|int
     */
    public function import(string $path)
    {
        if (storage()->exist($path)) {
            $dataFile=  storage()->get($path);
            $dataBase64 = preg_split('/\r?\n/', $dataFile);
            $num=0;
            try {
                $this->begin();
                foreach ($dataBase64 as $dataCode) {
                    if (!empty($dataCode)) {
                        try {
                            list($head, $data)=explode(';', $dataCode);
                            list($name, $time, $sha1, $dataType)=explode(',', $head);
                        } catch (\Exception $e) {
                            return false;
                        }
                        if (sha1($data)!=$sha1 || $time >time() || $name!=$this->tableName) {
                            return false;
                        }
                        $num+= (new RawQuery($this->connection, base64_decode($data)))->exec();
                    }
                }
                $this->commit();
                return $num;
            } catch (\Exception $e) {
                $this->rollBack();
            }
        }
        return false;
    }
    
    protected function checkPrimaryKey($value)
    {
        if (count($this->primaryKey)===1) {
            return [ $this->primaryKey[0]=>$value];
        } else {
            // 检查主键完整性
            foreach ($this->primaryKey as $key) {
                if (!isset($value[$key])) {
                    $message='primary key  is multipled, check '.$key.' in fields';
                    $debug=debug_backtrace();
                    throw new TableException(__($message), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
                }
            }
            return $value;
        }
    }

    /**
     * 检查参数列
     *
     * @param array $values
     */
    protected function checkFields(array $values)
    {
        foreach ($values as $key) {
            if (!in_array($key, $this->fields)) {
                throw new TableException(__('field $0 is not exsits in table', $key));
            }
        }
    }

    
    /**
     * 获取主键
     *
     * @return array
     */
    public function getPrimaryKey():array
    {
        return $this->primaryKey;
    }

    /**
     * 设置主键
     *
     * @param array $keys
     * @return TableAccess
     */
    public function setPrimaryKey(array $keys)
    {
        $this->primaryKey=$keys;
        return $this;
    }

    /**
     * 设置表名
     *
     * @param string $name
     * @return TableAccess 
     */
    public function setTableName(string $name)
    {
        $this->tableName = $name;
        return $this;
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public function getTableName():string
    {
        return $this->tableName;
    }

    /**
     * 设置表列
     *
     * @param array|null $fields
     * @return TableAccess 
     */
    public function setFields(?array $fields=null)
    {
        if (is_null($fields)) {
            if (is_null($this->allFields)) {
                $this->initTableFields();
            }
            $this->fields = $this->allFields;
            return $this;
        }
        $this->fields=$fields;
        return $this;
    }

    /**
     * 获取全部的列
     *
     * @return array
     */
    public function getFields():array
    {
        return $this->fields;
    }

    /**
     * 从数据表创建器创建数据表
     *
     * @param TableSQLCreator $table
     * @return boolean
     */
    protected function initFromTable(TableSQLCreator $table):bool
    {
        (new RawQuery($this->connection, $table))->exec();
        $this->primaryKey=$table->getPrimaryKeyName();
        $this->fields=$table->getFieldsName();
        return true;
    }
    
    /**
     * 初始化数据表字段
     *
     * @return void
     */
    protected function initTableFields()
    {
        if (file_exists($this->cachePath) && !conf('debug')) {
            $fieldsinfo=require $this->cachePath;
            $this->setFields($fieldsinfo['fields']);
            $this->setPrimaryKey($fieldsinfo['primaryKey']);
            $this->allFields = $fieldsinfo['fields'];
        } else {
            if (!$this->initFromDatabase()) {
                $this->createTable();
            }
            $this->cacheDbInfo();
        }
    }

    /**
     * 从数据表创建字段
     *
     * @return boolean
     */
    protected function initFromDatabase()
    {
        $fields=[];
        $this->primaryKey=[];
        try {
            $columns=(new RawQuery($this->connection, 'show columns from #{'.$this->getTableName().'};'))->fetchAll();
        } catch (\suda\exception\SQLException  $e) {
            return false;
        }
        if (is_array($columns)) {
            foreach ($columns as $column) {
                $fields[]=$column['Field'];
                if ($column['Key']==='PRI') {
                    $this->primaryKey[]=$column['Field'];
                }
            }
            $this->setFields($fields);
            return true;
        }
        return false;
    }

    protected function cacheDbInfo()
    {
        $info =[];
        $info['fields']=$this->getFields();
        $info['primaryKey']=$this->getPrimaryKey();
        if (cache()->enable()) {
            Storage::path(dirname($this->cachePath));
            ArrayHelper::export($this->cachePath, '_fieldinfos', $info);
        }
    }

    /**
     * 获取导出数据
     * 有限制的获取到户数据
     *
     * @param integer|null $limit
     * @param integer|null $offset
     * @return string|null
     */
    protected function getDataStringLimit(?int $limit=null, ?int $offset=null):?string
    {
        $table=$this->tableName;
        $limitCondition=';';
        if (!is_null($limit)) {
            $limitCondition='LIMIT ';
            if (!is_null($offset)) {
                $limitCondition.=$offset.',';
            }
            $limitCondition.=$limit.';';
        }
        $q=new RawQuery($this->connection, 'SELECT * FROM `#{'.$table.'}` WHERE 1 '. $limitCondition, [], true);
        if (is_null($this->exportFields)) {
            $columns=(new RawQuery($this->connection, 'SHOW COLUMNS FROM `#{'.$table.'}`;'))->fetchAll();
            $key='(';
            foreach ($columns  as $column) {
                $key.='`'.$column['Field'].'`,';
            }
        } else {
            $key='(';
            foreach ($this->exportFields  as $field) {
                $key.='`'.$field.'`,';
            }
        }
        $key=rtrim($key, ',').')';
        if ($q) {
            $sqlout='INSERT INTO `#{'.$table.'}` '.$key.' VALUES ';
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
                    if (is_null($val)) {
                        $columns.='NULL,';
                    } else {
                        $columns.='\''.addslashes($val).'\',';
                    }
                }
                $columns=rtrim($columns, ',');
                $sql.= $columns;
                $sql.=')';
                $sqlout.=$sql;
            }
            if ($first) {
                return null;
            }
            return $sqlout;
        }
        return null;
    }
}
