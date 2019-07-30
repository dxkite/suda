<?php

namespace suda\application\database;

use suda\framework\Debugger;
use suda\database\statement\Statement;
use suda\database\connection\Connection;
use suda\database\statement\QueryAccess as StatementQueryAccess;
use suda\database\connection\observer\Observer;

/**
 * Class DebugObserver
 * @package suda\application\database
 */
class DebugObserver implements Observer
{
    /**
     * @var int
     */
    private $count;

    /**
     * 调试记录
     *
     * @var Debugger
     */
    protected $debug;

    /**
     * DebugObserver constructor.
     * @param Debugger $debug
     */
    public function __construct(Debugger $debug)
    {
        $this->debug = $debug;
        $this->count = 0;
    }

    /**
     * @param StatementQueryAccess $access
     * @param Connection $connection
     * @param Statement $statement
     * @param float $timeSpend
     * @param bool $result
     */
    public function observe(StatementQueryAccess $access, Connection $connection, Statement $statement, float $timeSpend, bool $result)
    {
        $this->count++;
        $query = $connection->prefix($statement->getString());
        $this->debug->recordTiming('query', $timeSpend, $this->count . ' queries');
        $status = $result ? 'OK' : 'Err';
        if ($result) {
            $effect = $statement->getStatement()->rowCount();
            $this->debug->info(sprintf(
                "query [%s] %s %ss",
                $status,
                $query,
                number_format($timeSpend, 5)
            ));
            $this->debug->info(sprintf("query effect %s rows", $effect));
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
        $binder = $statement->getBinder();
        foreach ($binder as $item) {
            if ($item->getKey() !== null) {
                $value = $access->getMiddleware()->input($item->getKey(), $item->getValue());
            } else {
                $value = $item->getValue();
            }
            $this->debug->debug(sprintf("query value :%s = %s", $item->getName(), json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
        }
    }

    /**
     * 链接数据库
     *
     * @param float $timeSpend
     */
    public function connectDatabase(float $timeSpend)
    {
        $this->debug->info('connection database cost {time}s', ['time' => number_format($timeSpend, 4)]);
        $this->debug->recordTiming('db', $timeSpend, 'connection database');
    }
}
