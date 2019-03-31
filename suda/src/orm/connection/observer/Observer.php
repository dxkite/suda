<?php
namespace suda\orm\connection\observer;

use suda\orm\statement\Statement;
use suda\orm\statement\QueryAccess;


interface Observer
{
    public function observe(QueryAccess $access, Statement $statement, $timeSpend, bool $result);
}
