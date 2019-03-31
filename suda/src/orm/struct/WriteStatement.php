<?php
namespace suda\orm\struct;

use suda\orm\TableAccess;

class WriteStatement extends \suda\orm\statement\WriteStatement
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
     * 返回影响行数
     *
     * @return int
     */
    public function rows():int {
        return $this->access->run($this->getRows());
    }

    /**
     * 返回是否成功
     *
     * @return boolean
     */
    public function ok():bool {
        return $this->access->run($this->getOk());
    }

    /**
     * 返回ID
     *
     * @return string
     */
    public function id():string {
        return $this->access->run($this->getId());
    }
}
