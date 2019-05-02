<?php
namespace suda\orm\statement;

use function is_array;
use PDOStatement;
use suda\orm\Binder;
use suda\orm\middleware\Middleware;

abstract class Statement
{
    use PrepareTrait;
    
    const WRITE = 0;
    const READ = 1;

    const FETCH_ONE = 0;
    const FETCH_ALL = 1;

    /**
     * 语句类型
     *
     * @var int|null
     */
    protected $type = null;

    /**
     * 获取类型
     *
     * @var int
     */
    protected $fetch = null;

    /**
     * 滚动
     *
     * @var boolean|null
     */
    protected $scroll = null;

    /**
     * 绑定
     *
     * @var Binder[]
     */
    protected $binder = [];

    /**
     * SQL语句
     *
     * @var string
     */
    protected $string;

    /**
     * PDOStatement
     *
     * @var PDOStatement|null
     */
    protected $statement = null;

    /**
     * Query
     *
     * @var Query
     */
    protected $query;

    const RET_ROWS = 1;
    const RET_LAST_INSERT_ID = 2;
    const RET_BOOL = 3;
    /**
     * 返回类型
     *
     * @var int
     */
    protected $returnType = Statement::RET_BOOL;

    /**
     * 类
     *
     * @var string
     */
    protected $fetchClass;

    /**
     * 参数
     *
     * @var array
     */
    protected $fetchClassArgs = [];

    /**
     * 数据处理中间件
     *
     * @var Middleware
     */
    protected $middleware;

    /**
     * Statement constructor.
     * @param string $sql
     * @param mixed ...$args
     */
    public function __construct(string $sql, ...$args)
    {
        if (count($args) === 1 && is_array($args[0])) {
            $this->create($sql, $args[0]);
        } else {
            list($this->string, $this->binder) = $this->prepareQueryMark($sql, $args);
        }
    }
    
    protected function create(string $sql, array $parameter)
    {
        $this->string = $sql;
        $this->binder = $this->mergeBinder($this->binder, $parameter);
    }

    public function isRead(bool $set = null):bool
    {
        if ($set !== null) {
            $this->type = self::READ;
        }
        return $this->type === self::READ;
    }

    public function isWrite(bool $set = null):bool
    {
        if ($set !== null) {
            $this->type = self::WRITE;
        }
        return $this->type === self::WRITE;
    }

    /**
     * 判断是否为一条
     *
     * @return boolean
     */
    public function isFetchOne(bool $set = null):bool
    {
        if ($set !== null) {
            $this->fetch = self::FETCH_ONE;
        }
        return $this->fetch === self::FETCH_ONE;
    }

    /**
     * 判断是否为一条
     *
     * @return boolean
     */
    public function isFetch():bool
    {
        return $this->fetch !== null;
    }

    /**
     * 判断是否获取多条
     *
     * @return boolean
     */
    public function isFetchAll(bool $set = null):bool
    {
        if ($set !== null) {
            $this->fetch = self::FETCH_ALL;
        }
        return $this->fetch === self::FETCH_ALL;
    }
    
    /**
     * 设置记录类
     *
     * @param string|null $class
     * @param array $args
     * @return $this
     */
    public function setFetchType(?string $class = null, array $args = [])
    {
        $this->fetchClass = $class;
        $this->fetchClassArgs = $args;
        return $this;
    }

    /**
     * 获取取值类
     *
     * @return string|null
     */
    public function getFetchClass():?string
    {
        return $this->fetchClass ?? null;
    }

    /**
     * 是否滚动
     *
     * @return boolean|null
     */
    public function isScroll(bool $set = null):?bool
    {
        if ($set !== null) {
            $this->scroll = true;
        }
        return $this->scroll;
    }

    /**
     * 获取SQL字符串
     *
     * @return string
     */
    public function getString()
    {
        return $this->getQuery()->getQuery();
    }

    /**
     * 准备查询对象
     *
     * @return Query
     */
    protected function prepareQuery():Query
    {
        return new Query($this->string, $this->binder);
    }

    /**
     * 准备查询对象
     *
     * @return Query
     */
    public function prepare():Query
    {
        return $this->query = $this->prepareQuery();
    }

    /**
     * 获取查询对象
     *
     * @return Query
     */
    public function getQuery():Query
    {
        if ($this->query === null) {
            $this->query = $this->prepare();
        }
        return $this->query;
    }

    /**
     * 获取绑定信息
     *
     * @return Binder[]
     */
    public function getBinder()
    {
        return $this->binder;
    }

    public function __toString()
    {
        return $this->getString();
    }
    

    /**
     * Get PDOStatement
     *
     * @return  PDOStatement
     */
    public function getStatement():?PDOStatement
    {
        return $this->statement;
    }

    /**
     * Set PDOStatement
     *
     * @param  PDOStatement  $statement  PDOStatement
     *
     * @return  self
     */
    public function setStatement(PDOStatement $statement)
    {
        $this->statement = $statement;

        return $this;
    }

    
    /**
     * Get 返回类型
     *
     * @return  int
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Set 返回类型
     *
     * @param  int  $returnType  返回类型
     *
     * @return  self
     */
    public function setReturnType(int $returnType)
    {
        $this->returnType = $returnType;

        return $this;
    }

    /**
     * Get 参数
     *
     * @return  array
     */
    public function getFetchClassArgs()
    {
        return $this->fetchClassArgs;
    }

    /**
     * Get 数据处理中间件
     *
     * @return  Middleware
     */ 
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Set 数据处理中间件
     *
     * @param  Middleware  $middleware  数据处理中间件
     *
     * @return  self
     */ 
    public function setMiddleware(Middleware $middleware)
    {
        $this->middleware = $middleware;

        return $this;
    }
}
