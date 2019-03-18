<?php
namespace suda\orm\statement;

use suda\orm\Binder;
use suda\orm\exception\SQLException;

trait PrepareTrait
{
    
    /**
     * 准备选择列
     *
     * @param string|array $wants
     * @return string
     */
    public function prepareWants($wants):string
    {
        if (is_string($wants)) {
            $fields = $wants;
        } else {
            $field = [];
            foreach ($wants as $want) {
                $field[] = "`$want`";
            }
            $fields = implode(',', $field);
        }
        return $fields;
    }

    /**
     * 准备条件列
     *
     * @param array $where
     * @return array
     */
    protected function parepareWhere(array $where)
    {
        $and = [];
        $binders = [];
        foreach ($where as $name => $value) {
            $_name = Binder::index($name);
            // in cause
            if ($value instanceof \ArrayObject) {
                list($sql, $in_binder) = $this->prepareIn($name, $value);
                $and[] = $sql;
                $binders = array_merge($binders, $in_binder);
            } elseif (\is_array($value)) {
                list($op, $val) = $value;
                $and[] = "`{$name}` {$op} :{$_name}";
                $binders[] = new Binder($_name, $val);
            } else {
                $and[] = "`{$name}`=:{$_name}";
                $binders[] = new Binder($_name, $value);
            }
        }
        return [\implode(' AND ', $and), $binders];
    }

    /**
     * 准备In
     *
     * @param string $name
     * @param \ArrayObject $values
     * @return array
     */
    protected function prepareIn(string $name, \ArrayObject $values)
    {
        if (count($values) <= 0) {
            throw new SQLException('on field '.$name.' value can\'t be empty array');
        }
        $names = [];
        $binders = [];
        foreach ($values as $key => $value) {
            $_name = Binder::index($name);
            $binders[] = new Binder($_name, $value);
            $names[] = ':'.$_name;
        }
        $sql = $name.' IN ('.implode(',', $names).')';
        return [$sql,$binders];
    }

    /**
     * 准备更新
     *
     * @param array $data
     * @return array
     */
    protected function prepareUpdateSet(array $data)
    {
        $binders = [];
        $sets = [];
        foreach ($data as $name => $value) {
            $_name = Binder::index($name);
            $binders[] = new Binder($_name, $value, $name);
            $sets[] = "`{$name}`=:{$_name}";
        }
        return [\implode(',', $sets), $binders];
    }
}
