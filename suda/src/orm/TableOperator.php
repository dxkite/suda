<?php
namespace suda\orm;

use suda\orm\TableAccess;
use suda\orm\struct\Fields;
use suda\orm\statement\Statement;
use suda\orm\statement\ReadStatement;
use suda\orm\statement\WriteStatement;

/**
 * 提供了对数据表的操作
 */
class TableOperator
{

    /**
     * 数据表操作
     *
     * @var TableAccess
     */
    protected $access;

    /**
     * 主键
     *
     * @var Fields[]
     */
    protected $primaryKey;


    /**
     * 创建表操作器
     *
     * @param \suda\orm\TableAccess $table
     */
    public function __construct(TableAccess $table) {
        $this->access = $table;
        $this->preparePrimaryKey();
    }

    /**
     * 插入数据
     *
     * @param array $data
     * @return integer
     */
    public function insert(array $data):int
    {
        $table = $this->access;
        if (count($this->primaryKey) > 0) {
            return $table->run($table->write($data)->id());
        }
        return $table->run($table->write($data)->rows());
    }

    /**
     * 更新
     *
     * @param string|array $values
     * @param string|array $where
     * @param array $parameter
     * @return integer
     */
    public function update($values, $where, array $parameter = []) :int
    {
        $table = $this->access;
        return $table->run($table->write($values)->where($where, $parameter)->rows());
    }

    /**
     * 删除
     *
     * @param null|array|string $where
     * @param array $parameter
     * @return integer
     */
    public function delete($where = null, array $parameter = []) :int
    {
        $table = $this->access;
        if ($where !== null) {
            return $table->run($table->delete()->where($where, $parameter)->rows());
        }
        return $table->run($table->delete()->rows());
    }

    /**
     * 运行SQL语句
     *
     * @param \suda\orm\statement\Statement $statement
     * @return mixed
     */
    public function run(Statement $statement) {
        return $this->access->run($statement);
    }
    /**
     * 写
     *
     * @param mixed ...$args
     * @return WriteStatement
     */
    public function write(...$args):WriteStatement
    {
        return $this->access->write(...$args);
    }

    /**
     * 读
     *
     * @param mixed ...$args
     * @return ReadStatement
     */
    public function read(...$args):ReadStatement
    {
        return $this->access->read(...$args);
    }

    /**
     * 列表获取
     *
     * @param integer|null $page
     * @param integer $row
     * @param string|array $wants
     * @return array
     */
    public function list(?int $page, int $row = 10, $wants = '*'):array
    {
        $query = $this->access->read($wants);
        if ($page !== null) {
            $query->page($page, $row);
        }
        return $this->access->run($query->fetchAll());
    }

    protected function preparePrimaryKey() {
        foreach ($this->access->getStruct()->getFields() as $name => $field) {
            if ($field->isPrimary()) {
                $this->primaryKey[$name] = $field;
            }
        }
    }
}
