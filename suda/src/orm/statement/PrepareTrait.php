<?php
namespace suda\orm\statement;

use function array_key_exists;
use ArrayObject;
use function implode;
use function is_array;
use function preg_replace_callback;
use function str_replace;
use suda\orm\Binder;
use suda\orm\exception\SQLException;

/**
 * Trait PrepareTrait
 * @package suda\orm\statement
 */
trait PrepareTrait
{

    /**
     * 准备选择列
     *
     * @param string|array $reads
     * @param string $table
     * @return string
     */
    protected function prepareReadFields($reads, string $table = ''):string
    {
        if (is_string($reads)) {
            $fields = $reads;
        } else {
            $field = [];
            $prefix = strlen($table) ?"`{$table}`." :'';
            foreach ($reads as $want) {
                $field[] = $prefix."`$want`";
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
     * @throws SQLException
     */
    protected function parepareWhere(array $where)
    {
        $and = [];
        $binders = [];
        foreach ($where as $name => $value) {
            $_name = Binder::index($name);
            // in cause
            if ($value instanceof ArrayObject) {
                list($sql, $in_binder) = $this->prepareIn($name, $value);
                $and[] = $sql;
                $binders = array_merge($binders, $in_binder);
            } elseif (is_array($value)) {
                list($op, $val) = $value;
                $op = trim($op);
                $and[] = "`{$name}` {$op} :{$_name}";
                $binders[] = new Binder($_name, $val);
            } else {
                $and[] = "`{$name}`=:{$_name}";
                $binders[] = new Binder($_name, $value);
            }
        }
        return [implode(' AND ', $and), $binders];
    }

    /**
     * @param string $where
     * @param array $whereBinder
     * @return array
     * @throws SQLException
     */
    protected function parepareWhereString(string $where, array $whereBinder)
    {
        foreach ($whereBinder as $name => $value) {
            if (is_array($value) || $value instanceof ArrayObject) {
                list($inSQL, $binders) = $this->prepareInParameter($value, $name);
                $whereBinder = array_merge($whereBinder, $binders);
                $name = ltrim($name, ':');
                $where = str_replace(':'.$name, $inSQL, $where);
            }
        }
        return [$where, $whereBinder];
    }


    /**
     * 准备In
     *
     * @param string $name
     * @param ArrayObject|array $values
     * @return array
     * @throws SQLException
     */
    protected function prepareIn(string $name, $values)
    {
        list($inSQL, $binders) = $this->prepareInParameter($values, $name);
        $sql = $name.' IN ('.$inSQL.')';
        return [$sql,$binders];
    }

    /**
     * @param $values
     * @param string $name
     * @return array
     * @throws SQLException
     */
    protected function prepareInParameter($values, string $name)
    {
        if (count($values) <= 0) {
            throw new SQLException('on field '.$name.' value can\'t be empty array');
        }
        $names = [];
        $binders = [];
        foreach ($values as $value) {
            $_name = Binder::index($name);
            $binders[] = new Binder($_name, $value);
            $names[] = ':'.$_name;
        }
        return [implode(',', $names), $binders];
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
        return [implode(',', $sets), $binders];
    }

    /**
     * 编译 ? 字符
     *
     * @param string $sql
     * @param array $parameter
     * @return array
     */
    protected function prepareQueryMark(string $sql, array $parameter)
    {
        $binders = [];
        $query = preg_replace_callback('/\?/', function ($match) use (&$binders, $parameter) {
            $index = count($binders);
            if (array_key_exists($index, $parameter)) {
                $name = Binder::index($index);
                if (is_array($parameter[$index]) || $parameter[$index] instanceof ArrayObject) {
                    list($inSQL, $inBinders) = $this->prepareInParameter($parameter[$index], $index);
                    $binders = array_merge($binders, $inBinders);
                    return $inSQL;
                } else {
                    $binder = new Binder($name, $parameter[$index]);
                    $binders[] = $binder;
                    return ':'.$binder->getName();
                }
            }
            return $match[0];
        }, $sql);
        return [$query, $binders];
    }

    /**
     * 合并绑定工具
     *
     * @param Binder[] $binder
     * @param array $parameter
     * @return Binder[]
     */
    protected function mergeBinder(array $binder, array $parameter)
    {
        foreach ($parameter as $key => $value) {
            if ($value instanceof Binder) {
                $binder[] = $value;
            } else {
                $binder[] = new Binder($key, $value);
            }
        }
        return $binder;
    }
}
