<?php
namespace suda\orm\observer;

use suda\orm\statement\Statement;


interface Observer
{
    public function observe(Statement $statement, $timeSpend, bool $result);
}
