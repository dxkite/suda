<?php

namespace suda\orm\connection\observer;

use suda\orm\statement\Statement;
use suda\orm\connection\Connection;
use suda\orm\statement\QueryAccess;


interface Observer
{
    /**
     * @param QueryAccess $access
     * @param Connection $connection
     * @param Statement $statement
     * @param $timeSpend
     * @param bool $result
     */
    public function observe(QueryAccess $access, Connection $connection, Statement $statement, $timeSpend, bool $result);
}
