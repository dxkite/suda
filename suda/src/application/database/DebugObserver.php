<?php
namespace suda\application\database;

use function implode;
use suda\framework\Debugger;
use suda\orm\statement\Statement;
use suda\orm\statement\QueryAccess;
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

    /**
     * @param QueryAccess $access
     * @param Statement $statement
     * @param $timeSpend
     * @param bool $result
     */
    public function observe(QueryAccess $access, Statement $statement, $timeSpend, bool $result)
    {
        $query = $access->prefix($statement->getString());
        $status = $result ? 'OK' : 'Err';
        if ($result) {
            $effect = $statement->getStatement()->rowCount();
            $this->debug->info('query ['.$status.'] '.$query.' '. number_format($timeSpend, 5).'s and effect '. $effect . ' rows');
        } else {
            $this->debug->error('query ['.$status.'] '.$query.' '. number_format($timeSpend, 5).'s');
            $this->debug->error('query ['.$status.'] '. implode(':', $statement->getStatement()->errorInfo()));
        }
    }
}
