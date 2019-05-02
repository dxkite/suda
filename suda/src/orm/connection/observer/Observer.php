<?php
namespace suda\orm\connection\observer;

use suda\orm\statement\Statement;
use suda\orm\statement\QueryAccess;


interface Observer
{
    /**
     * @param QueryAccess $access
     * @param Statement $statement
     * @param $timeSpend
     * @param bool $result
     * @return mixed
     */
    public function observe(QueryAccess $access, Statement $statement, $timeSpend, bool $result);
}
