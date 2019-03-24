<?php
namespace suda\orm;

use suda\orm\TableAccess;

/**
 * 提供了对数据表的操作
 */
class TableOperator extends TableAccess
{
    /**
     * 插入数据
     *
     * @param array $data
     * @return integer
     */
    public function insert(array $data):int
    {
        if (count($this->primaryKey) > 0) {
            return $this->run($this->write($data)->id());
        }
        return $this->run($this->write($data)->rows());
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
        return $this->run($this->write($values)->where($where, $parameter)->rows());
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
        if ($where !== null) {
            return $this->run($this->delete()->where($where, $parameter)->rows());
        }
        return $this->run($this->delete()->rows());
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
        $query = $this->read($wants);
        if ($page !== null) {
            $query->page($page, $row);
        }
        return $query;
    }
}
