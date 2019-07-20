<?php
namespace suda\database\statement;

use Countable;
use IteratorAggregate;
use suda\database\Binder;
use suda\database\exception\SQLException;

/**
 * Trait PrepareTrait
 * @package suda\database\statement
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
    protected function prepareWhere(array $where)
    {
        $and = [];
        $binders = [];
        foreach ($where as $name => $value) {
            $query = $this->getQueryForArray($name, $value);
            $and[] = $query->getQuery();
            $binders  = array_merge($binders, $query->getBinder());
        }
        return [implode(' AND ', $and), $binders];
    }

    /**
     * @param string $name
     * @param $value
     * @return Query
     * @throws SQLException
     */
    private function getQueryForArray(string $name, $value)
    {
        if ($value instanceof IteratorAggregate) {
            return $this->prepareIn($name, 'IN', $value);
        } elseif (is_array($value)) {
            list($op, $val) = $value;
            $op = trim($op);
            return $this->createQueryOperation($name, $op, $val);
        } else {
            return $this->createQueryOperation($name, '=', $value);
        }
    }

    /**
     * @param string $name
     * @param string $operation
     * @param string $indexName
     * @param mixed $value
     * @return Query
     * @throws SQLException
     */
    private function createQueryOperation(string $name, string $operation, $value, string $indexName = '')
    {
        if ($value instanceof Query) {
            return new Query("`{$name}` {$operation} ".$value, $value->getBinder());
        }
        if ($value instanceof Statement) {
            return new Query("`{$name}` {$operation} (".$value->getQuery().')', $value->getBinder());
        }
        if ($value instanceof IteratorAggregate || is_array($value)) {
            return $this->prepareIn($name, $operation, $value);
        }
        if (strlen($indexName) === 0) {
            $indexName = Binder::index($name);
        }
        return new Query("`{$name}` {$operation} :{$indexName}", [new Binder($indexName, $value)]);
    }

    /**
     * @param string $where
     * @param string $name
     * @param $value
     * @return Query
     * @throws SQLException
     */
    private function getQueryForString(string $where, string $name, $value)
    {
        if (is_array($value) || $value instanceof IteratorAggregate) {
            list($inSQL, $binders) = $this->prepareInParameter($value, $name);
            $where = $this->replaceQuote($name, $inSQL, $where);
            return new Query($where, $binders);
        } elseif ($value instanceof  Binder) {
            return new Query($where, [$value]);
        } else {
            return new Query($where, [new Binder($name, $value)]);
        }
    }

    /**
     * @param string $name
     * @param string $replace
     * @param string $target
     * @return string
     */
    protected function replaceQuote(string $name, string $replace, string $target) {
        $name = ltrim($name, ':');
        return preg_replace('/(?<!_):'.preg_quote($name).'/', $replace, $target);
    }

    /**
     * @param string $where
     * @param array $whereBinder
     * @return array
     * @throws SQLException
     */
    protected function prepareWhereString(string $where, array $whereBinder)
    {
        $newWhereBinder = [];
        foreach ($whereBinder as $name => $value) {
            $query = $this->getQueryForString($where, $name, $value);
            $where = $query->getQuery();
            $newWhereBinder = array_merge($newWhereBinder, $query->getBinder());
        }
        return [$where, $newWhereBinder];
    }


    /**
     * 准备In
     *
     * @param string $name
     * @param IteratorAggregate|array|Query|Statement $values
     * @return Query
     * @throws SQLException
     */
    protected function prepareIn(string $name, string $operation, $values)
    {
        if ($values instanceof Query || $values instanceof Statement) {
            return $this->createQueryOperation($name, 'in', $values);
        }
        list($inSQL, $binders) = $this->prepareInParameter($values, $name);
        $sql = '`'.$name.'` '.strtoupper($operation).' ('.$inSQL.')';
        return new Query($sql, $binders);
    }

    /**
     * @param IteratorAggregate|array $values
     * @param string $name
     * @return array
     * @throws SQLException
     */
    protected function prepareInParameter($values, string $name)
    {
        if ($this->count($values) <= 0) {
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
     * @param array|Countable|IteratorAggregate $value
     * @return int
     */
    private function count($value)
    {
        if (is_array($value) || $value instanceof Countable) {
            return count($value);
        }
        return iterator_count($value);
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
                if (is_array($parameter[$index]) || $parameter[$index] instanceof IteratorAggregate) {
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
     * @param Binder[] $binderArray
     * @param array $parameter
     * @return Binder[]
     */
    protected function mergeBinder(array $binderArray, array $parameter)
    {
        foreach ($parameter as $key => $value) {
            if (! ($value instanceof Binder)) {
                $value = new Binder($key, $value);
            }
            if (!in_array($value, $binderArray)) {
                $binderArray[] = $value;
            }
        }
        return $binderArray;
    }
}
