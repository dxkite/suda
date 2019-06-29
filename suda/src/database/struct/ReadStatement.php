<?php
namespace suda\database\struct;

use suda\database\exception\SQLException;
use suda\database\TableAccess;

class ReadStatement extends \suda\database\statement\ReadStatement
{
    /**
     * 访问操作
     *
     * @var TableAccess
     */
    protected $access;

    public function __construct(TableAccess $access)
    {
        $this->access = $access;
        parent::__construct(
            $access->getSource()->write()->rawTableName($access->getStruct()->getName()),
            $access->getStruct(),
            $access->getMiddleware()
        );
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
        return $this->access->run($this->limit(0,1)->wantOne($class, $args));
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
     * Get 访问操作
     *
     * @return  TableAccess
     */
    public function getAccess():TableAccess
    {
        return $this->access;
    }
}
