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
     * 条件
     *
     * @param string|array $where
     * @param array $whereBinder
     * @return self
     */
    public function where($where, ...$args)
    {
        if (\is_array($where)) {
            $where = $this->aliasKeyField($where);
            $this->whereArray($where, $args[0] ?? []);
        } elseif (is_array($args[0])) {
            $this->whereStringArray($where, $args[0]);
        } else {
            list($string, $array) = $this->prepareQueryMark($where, $args);
            $this->whereStringArray($string, $array);
        }
        return $this;
    }

    /**
     * 处理输入的键
     *
     * @param array $fields
     * @return array
     */
    protected function aliasKeyField(array $fields)
    {
        $values = [];
        foreach ($fields as $name => $value) {
            $index = $this->access->getMiddleware()->inputName($name);
            $values[$index] = $value;
        }
        return $values;
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
