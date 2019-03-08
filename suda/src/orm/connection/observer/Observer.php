<?php
namespace suda\orm\connection\observer;

use suda\orm\statement\Statement;


interface Observer
{
    public function observe(Statement $statement, $timeSpend, bool $result);
}
