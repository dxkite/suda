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
     * @param string|null $class
     * @param array $args
     * @return mixed
     */
    public function one(?string $class = null, array $args = [])
    {
        return $this->access->run($this->wantOne($class, $args));
    }
  
    /**
     * 取全部
     *
     * @param string|null $class
     * @param array $args
     * @return array
     */
    public function all(?string $class = null, array $args = []):array
    {
        return $this->access->run($this->wantAll($class, $args));
    }
  
    /**
     * 取1
     *
     * @param string|null $class
     * @param array $args
     * @return mixed
     */
    public function fetch(?string $class = null, array $args = [])
    {
        return $this->one($class, $args);
    }
  
    /**
     * 取全部
     *
     * @param string|null $class
     * @param array $args
     * @return array
     */
    public function fetchAll(?string $class = null, array $args = []):array
    {
        return $this->all($class, $args);
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
