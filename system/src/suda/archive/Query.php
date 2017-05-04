<?php
namespace suda\archive;

use PDO;
use suda\core\{Config,Storage};

// 数据库查询方案
class Query
{
    protected static $pdo=null;
    protected static $prefix=null;
    protected $stmt=null;
    // 查询语句
    protected $query=null;
    // 模板值
    protected $values=null;
    protected $scroll=false;
    // 使用的数据库
    protected $database=null;
    protected $dbchange=false;
    protected static $good=true;

    // TODO :  支持超大查询 max_allowed_packet

    public function __construct(string $query, array $binds=[], bool $scroll=false)
    {
        self::connectPdo();
        $this->query=$query;
        $this->values=$binds;
        $this->scroll=$scroll;
    }

    public function fetch(int $fetch_style = PDO::FETCH_ASSOC)
    {
        if ($this->stmt) {
            return $this->stmt->fetch($fetch_style);
        } else {
            if (self::lazyQuery($this->query, $this->values)) {
                return $this->stmt->fetch($fetch_style);
            }
        }
        return false;
    }

    public function fetchObject(string $class='stdClass')
    {
        if ($this->stmt) {
            return $this->stmt->fetchObject($class);
        } else {
            if (self::lazyQuery($this->query, $this->values)) {
                return $this->stmt->fetchObject($class);
            }
        }
        return false;
    }
    
    public function fetchAll(int $fetch_style = PDO::FETCH_ASSOC)
    {
        if (self::lazyQuery($this->query, $this->values)) {
            return $this->stmt->fetchAll($fetch_style);
        }
        return false;
    }
    
    public function exec():int
    {
        if (self::lazyQuery($this->query, $this->values)) {
            return $this->stmt->rowCount();
        }
        return 0;
    }

    public function values(array $values)
    {
        $this->values=array_merge($this->values, $values);
        return $this;
    }

    public function query(string $query, array $array=[])
    {
        $this->query=$query;
        $this->values=$array;
        $this->stmt=null;
        return $this;
    }
    public function use(string $name=null)
    {
        $this->database=$name;
        $this->dbchange=true;
        return $this;
    }
    // 获取错误
    public function error()
    {
        if ($this->stmt) {
            return $this->stmt->errorInfo();
        }
        return false;
    }
    public function erron():string
    {
        if ($this->stmt) {
            return $this->stmt->errorCode();
        }
        return false;
    }
    public static function lastInsertId():int
    {
        return self::$pdo->lastInsertId();
    }
    protected function auto_prefix(string $query)
    {
        return preg_replace('/#{(\S+?)}/', self::$prefix.'$1', $query);
    }
    protected function lazyQuery(string $query, array $array=[])
    {
        $query=self::auto_prefix($query);
        // 调整数据表
        if ($this->database && $this->dbchange) {
            if (self::$pdo->query('USE '.$this->database)) {
                $this->dbchange=false;
                $this->database=null;
            } else {
                die('Could not select database:'.$this->database);
            }
        } elseif (is_null($this->database)) {
            if (self::$pdo->query('USE '.Config::get('database.name', 'test'))) {
                $this->database=Config::get('database.name', 'test');
            }
        }

        if ($this->scroll) {
            $stmt=self::$pdo->prepare($query, [PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL]);
        } else {
            $stmt=self::$pdo->prepare($query);
        }
        foreach ($array as $key=> $value) {
            $key=':'.ltrim($key, ':');
            if (is_array($value)) {
                $tmp =$value;
                $value = $tmp[0];
                $type = $tmp[1];
            } else {
                $type=is_numeric($value)?PDO::PARAM_INT:PDO::PARAM_STR;
            }
            $stmt->bindValue($key, $value, $type);
        }

        $return=$stmt->execute();
        // TODO: To Log This
        // var_dump($return,$stmt,$stmt->errorInfo());
        Storage::mkdirs($path=DATA_DIR.'/logs');
        // 检查成功
        $path=realpath($path);
        if ($return) {
            if (Config::get('debug')) {
                Storage::put($path.'/query_'.date('Y_m_d').'.query', date('Y-m-d H:i:s ').$stmt->queryString.' '.$stmt->errorInfo()[2]."\r\n", FILE_APPEND);
            }
        } else {
            Storage::put($path.'/query_'.date('Y_m_d').'.error', date('Y-m-d H:i:s ').$stmt->queryString.' '.$stmt->errorInfo()[2]."\r\n", FILE_APPEND);
            if (!conf('database.ignoreError',false)){
                throw new \Exception($stmt->errorInfo()[2], intval($stmt->errorCode()));
            }
        }
        $this->stmt=$stmt;
        return $return;
    }
    protected static function connectPdo()
    {
        // 链接数据库
        if (!self::$pdo) {
            $pdo='mysql:host='.Config::get('database.host', 'localhost').';charset='.Config::get('database.charset', 'utf8').';port='.Config::get('database.port',3306);
            self::$prefix=Config::get('database.prefix', '');
            try {
                self::$pdo = new PDO($pdo, Config::get('database.user', 'root'), Config::get('database.passwd', 'root'));
            } catch (\PDOException $e) {
                self::$good=false;
            }
        }
    }
    public function good() :bool
    {
        return self::$good;
    }
    // 事务系列
    public static function begin()
    {
        return self::beginTransaction();
    }
    // 事务系列
    public static function beginTransaction()
    {
        self::connectPdo();
        return self::$pdo->beginTransaction();
    }
    public static function commit()
    {
        self::connectPdo();
        return  self::$pdo->commit();
    }

    public static function rollBack()
    {
        self::connectPdo();
        return  self::$pdo->rollBack();
    }

    public function quote($string)
    {
        return self::$pdo->quote($string);
    }

    public function arrayQuote(array $array)
    {
        $temp = array();
        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : self::$pdo->quote($value);
        }
        return implode($temp, ',');
    }
}
