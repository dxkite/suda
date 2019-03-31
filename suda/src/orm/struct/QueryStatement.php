<?php
namespace suda\orm\struct;

use suda\orm\TableAccess;
use suda\orm\TableStruct;

class QueryStatement extends \suda\orm\statement\QueryStatement
{
    /**
     * 访问操作
     *
     * @var TableAccess
     */
    protected $access;
    
    public function __construct(TableAccess $access, ...$args)
    {
        $this->access = $access;
        parent::__construct(...$args);
    }
    
    /**
     * 取1
     *
     * @return TableStruct|null
     */
    public function one():?TableStruct
    {
        return $this->access->run($this->fetch());
    }

    /**
     * 取全部
     *
     * @return TableStruct[]
     */
    public function all():array
    {
        return $this->access->run($this->fetchAll());
    }
}
