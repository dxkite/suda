<?php

namespace suda\database\connection\observer;

use suda\database\statement\Statement;
use suda\database\connection\Connection;
use suda\database\statement\QueryAccess;


class NullObserver implements Observer
{

    /**
     * @param QueryAccess $access
     * @param Connection $connection
     * @param Statement $statement
     * @param float $timeSpend
     * @param bool $result
     * @return void
     */
    public function observe(QueryAccess $access, Connection $connection, Statement $statement, float $timeSpend, bool $result)
    {
        // noop
    }

    /**
     * 链接数据库
     *
     * @param float $timeSpend
     */
    public function connectDatabase(float $timeSpend)
    {
        // noop
    }
}
