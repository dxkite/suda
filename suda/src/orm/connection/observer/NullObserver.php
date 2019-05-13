<?php
namespace suda\orm\connection\observer;

use suda\orm\statement\Statement;
use suda\orm\connection\Connection;
use suda\orm\statement\QueryAccess;


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
