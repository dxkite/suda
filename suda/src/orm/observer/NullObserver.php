<?php
namespace suda\orm\observer;

use suda\orm\statement\Statement;


class NullObserver implements Observer
{
    public function observe(Statement $statement, $timeSpend, $result) {
        // noop
    }
}
