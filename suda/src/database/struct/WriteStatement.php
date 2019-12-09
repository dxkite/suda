<?php
namespace suda\database\struct;

use suda\database\exception\SQLException;
use suda\database\TableAccess;

class WriteStatement extends \suda\database\statement\WriteStatement
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
            $access->getStruct()->getRealTableName($access->getSource()->write()),
            $access->getStruct(),
            $access->getMiddleware()
        );
    }

    /**
     * 返回影响行数
     *
     * @return int
     * @throws SQLException
     */
    public function rows():int
    {
        return $this->access->run($this->wantRows());
    }

    /**
     * 返回是否成功
     *
     * @return boolean
     * @throws SQLException
     */
    public function ok():bool
    {
        return $this->access->run($this->wantOk());
    }

    /**
     * 返回ID
     *
     * @return string
     * @throws SQLException
     */
    public function id():string
    {
        return $this->access->run($this->wantId());
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
