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
     * @return array|null
     */
    public function one():?array
    {
        return $this->access->run($this->fetch());
    }

    /**
     * 取全部
     *
     * @return array
     */
    public function all():array
    {
        return $this->access->run($this->fetchAll());
    }

    /**
     * Get 访问操作
     *
     * @return  TableAccess
     */ 
    public function getAccess():TableAccess
    {
        return $this->access;
    }
}
