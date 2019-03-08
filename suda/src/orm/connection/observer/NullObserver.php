<?php
namespace suda\orm\connection\observer;

use suda\orm\statement\Statement;


class NullObserver implements Observer
{
    public function observe(Statement $statement, $timeSpend, bool $result) {
        // noop
    }
}
