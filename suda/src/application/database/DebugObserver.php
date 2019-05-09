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
            $this->debug->info(sprintf(
                "query [%s] %s %ss and effect %s rows",
                $status,
                $query,
                number_format($timeSpend, 5),
                $effect
            ));
        } else {
            $this->debug->error(sprintf(
                "query [%s] %s %ss",
                $status,
                $query,
                number_format($timeSpend, 5)
            ));
            $this->debug->error(sprintf(
                "query [%s] %s",
                $status,
                implode(':', $statement->getStatement()->errorInfo())
            ));
        }
    }
}
