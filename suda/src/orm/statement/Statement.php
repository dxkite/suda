<?php
namespace suda\orm\statement;

use PDOStatement;
use suda\archive\creator\Binder;

class Statement
{
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
        $this->string = $sql;
        if (func_get_args() == 2 && is_array($args[0])) {
            foreach ($args[0] as $key => $value) {
                if ($value instanceof Binder) {
                    $this->binder[] = $value;
                } else {
                    $this->binder[] = new Binder($key, $value);
                }
            }
        } else {
            list($this->string, $this->binder) = $this->matchBinder($sql, $args);
        }
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
     * @return boolean
     */
    public function scroll(bool $set = null):bool
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
        return $this->string;
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

    /**
     * 连接多个语句
     *
     * @param Statement|string ...$args
     * @return Statement
     */
    public static function concat(...$args):Statement
    {
        $statement = new Statement;
        foreach ($args as $item) {
            $statement->string .= $item;
            if ($args instanceof Statement) {
                $statement->binder = array_merge($statement->binder, $item->binder);
            }
        }
    }

    /**
     * 问号匹配
     *
     * @param string $sql
     * @param array $bind
     * @return array
     */
    protected function matchBinder(string $sql, array $bind)
    {
        if (substr_count($sql, '?') !== count($bind)) {
            throw new SQLException('bind number is not equals to ?');
        }
        $index = 0;
        $binders = [];
        $sql = \preg_replace('/?/', $sql, function ($match) use ($bind, &$index, &$binders) {
            if ($bind[$index] instanceof Binder) {
                $binders[] = $binder;
                return $bind[$index]->getName();
            } else {
                $name = Binder::index($index);
                $binder = new Binder($name, $bind[$index]);
                $binders[] = $binder;
                $index++;
                return $name;
            }
        });
        return [$sql, $binders];
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
