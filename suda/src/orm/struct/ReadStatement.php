<?php
namespace suda\orm\struct;

use suda\orm\TableAccess;
use suda\orm\TableStruct;


class ReadStatement extends \suda\orm\statement\ReadStatement
{
    /**
     * 访问操作
     *
     * @var TableAccess
     */
    protected $access;

    public function __construct(TableAccess $access)
    {
        $this->access = $access;
        parent::__construct(
            $access->getSource()->write()->rawTableName($access->getStruct()->getName()),
            $access->getStruct()
        );
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
