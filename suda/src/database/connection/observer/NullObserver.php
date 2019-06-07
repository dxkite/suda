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
     * @param $timeSpend
     * @param bool $result
     * @return void
     */
    public function observe(QueryAccess $access, Connection $connection, Statement $statement, $timeSpend, bool $result)
    {
        // noop
    }
}
