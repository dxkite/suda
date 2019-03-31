<?php
namespace suda\orm\connection\observer;

use suda\orm\statement\Statement;
use suda\orm\statement\QueryAccess;


class NullObserver implements Observer
{
    public function observe(QueryAccess $access, Statement $statement, $timeSpend, bool $result) {
        // noop
    }
}
