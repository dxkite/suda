<?php
namespace suda\orm\observer;

use suda\framework\Debugger;
use suda\orm\statement\Statement;

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
        $status = $result ? 'success' : 'fail';
        $this->info('query['.$status.'] process cost '.$query.' '. number_format($pass, 5).'s');
    }
}
