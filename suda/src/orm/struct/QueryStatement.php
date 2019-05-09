<?php
namespace suda\orm\struct;

use ReflectionException;
use suda\orm\exception\SQLException;
use suda\orm\statement\QueryAccess;

class QueryStatement extends \suda\orm\statement\QueryStatement
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
     * @throws ReflectionException
     */
    public function one(?string $class = null, array $args = [])
    {
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
     * @throws ReflectionException
     */
    public function all(?string $class = null, array $args = []):array
    {
        return $this->access->run($this->wantAll($class, $args));
    }

    /**
     * 取1
     *
     * @param string|null $class
     * @param array $args
     * @return mixed
     * @throws SQLException
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    public function fetchAll(?string $class = null, array $args = []):array
    {
        return $this->all($class, $args);
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
