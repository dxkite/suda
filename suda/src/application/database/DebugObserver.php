<?php
namespace suda\application\database;

use suda\framework\Debugger;
use suda\orm\statement\Statement;
use suda\orm\connection\observer\Observer;

class DebugObserver implements Observer
{
    /**
     * 调试记录
     *
     * @var Debugger
     */
    protected $debug;

    public function __construct(Debugger $debug)
    {
        $this->debug = $debug;
    }

    public function observe(Statement $statement, $timeSpend, bool $result)
    {
        $query = $statement->getString();
        $status = $result ? 'OK' : 'Err';
        $this->info('query['.$status.'] process cost '.$query.' '. number_format($pass, 5).'s');
    }
}
