<?php
namespace suda\orm\statement;

use PDOStatement;
use suda\orm\Binder;
use suda\orm\statement\PrepareTrait;

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
     * @var int|null
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
     * @var string|null
     */
    protected $string = null;

    /**
     * PDOStatement
     *
     * @var PDOStatement|null
     */
    protected $statement = null;

    public function __construct(string $sql, ...$args)
    {
        if (count($args) === 1 && \is_array($args[0])) {
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
     * 是否滚动
     *
     * @return boolean|null
     */
    public function scroll(bool $set = null):?bool
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
        if ($this->string === null) {
            $this->prepare();
        }
        return trim($this->string);
    }

    /**
     * 获取SQL字符串
     *
     * @return void
     */
    public function prepare()
    {
        // noop
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
}
