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
            $access->getStruct()->getRealTableName($access->getSource()->write()),
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
        if ($this->isScroll() === false && $this->hasLimit() === false) {
            $this->limit(0, 1);
        }
        return $this->access->run($this->wantOne($class, $args));
    }

    /**
     * 取一列
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws SQLException
     */
    public function field(string $name, $default = null)
    {
        $row = $this->one();
        return $row[$name] ?? $default;
    }

    /**
     * 取数组的一列
     * @param string $name
     * @return array
     * @throws SQLException
     */
    public function allField(string $name)
    {
        $row = $this->all();
        return array_column($row, $name);
    }

    /**
     * @return bool
     */
    private function hasLimit()
    {
        return strlen($this->limit) > 0;
    }

    /**
     * 取全部
     *
     * @param string|null $class
     * @param array $args
     * @return array
     * @throws SQLException
     */
    public function all(?string $class = null, array $args = []): array
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
    public function fetchAll(?string $class = null, array $args = []): array
    {
        return $this->all($class, $args);
    }

    /**
     * 统计
     *
     * @return int
     * @throws SQLException
     */
    public function count() {
        $query = clone $this;
        $query->orderBy = '';
        $query->limit = '';
        $totalQuery = new QueryStatement($this->getAccess(), sprintf("SELECT count(*) as count from (%s) as total", $query->getString()),
            $this->getBinder());
        $totalQuery->wantType(null);
        $data = $totalQuery->one();
        return intval($data['count']);
    }

    /**
     * Get 访问操作
     *
     * @return  TableAccess
     */
    public function getAccess(): TableAccess
    {
        return $this->access;
    }
}
