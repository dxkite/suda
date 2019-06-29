<?php
namespace suda\database\struct;

use ReflectionException;
use suda\database\exception\SQLException;
use suda\database\statement\QueryAccess;


class QueryStatement extends \suda\database\statement\QueryStatement
{
    /**
     * 访问操作
     *
     * @var QueryAccess
     */
    protected $access;

    public function __construct(QueryAccess $access, string $query, ...$parameter)
    {
        $this->access = $access;
        parent::__construct($query, ...$parameter);
    }

    /**
     * 取1
     *
     * @param string|null $class
     * @param array $args
     * @return mixed
     * @throws SQLException
     */
    public function one(?string $class = null, array $args = [])
    {
        $this->setType(static::READ);
        $value = $this->access->run($this->wantOne($class, $args));
        if (is_array($value)) {
            return $value;
        }
        return null;
    }

    /**
     * 取全部
     *
     * @param string|null $class
     * @param array $args
     * @return array
     * @throws SQLException
     */
    public function all(?string $class = null, array $args = []):array
    {
        $this->setType(static::READ);
        return $this->access->run($this->wantAll($class, $args));
    }

    /**
     * 取1
     *
     * @param string|null $class
     * @param array $args
     * @return mixed
     * @throws SQLException
     */
    public function fetch(?string $class = null, array $args = [])
    {
        return $this->one($class, $args);
    }

    /**
     * 取全部
     *
     * @param string|null $class
     * @param array $args
     * @return array
     * @throws SQLException
     */
    public function fetchAll(?string $class = null, array $args = []):array
    {
        return $this->all($class, $args);
    }

    /**
     * 返回影响行数
     *
     * @return int
     * @throws SQLException
     */
    public function rows():int
    {
        $this->returnType = WriteStatement::RET_ROWS;
        $this->setType(static::WRITE);
        return $this->access->run($this);
    }

    /**
     * 返回是否成功
     *
     * @return boolean
     * @throws SQLException
     */
    public function ok():bool
    {
        $this->returnType = WriteStatement::RET_BOOL;
        $this->setType(static::WRITE);
        return $this->access->run($this);
    }

    /**
     * 返回ID
     *
     * @return string
     * @throws SQLException
     */
    public function id():string
    {
        $this->returnType = WriteStatement::RET_LAST_INSERT_ID;
        $this->setType(static::WRITE);
        return $this->access->run($this);
    }

    /**
     * Get 访问操作
     *
     * @return  QueryAccess
     */
    public function getAccess():QueryAccess
    {
        return $this->access;
    }
}
