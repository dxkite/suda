<?php


namespace suda\database\statement;

use Countable;
use IteratorAggregate;
use suda\database\Binder;
use suda\database\exception\SQLException;

trait WherePrepareTrait
{

    /**
     * 处理？模板
     *
     * @param string $sql
     * @param array $parameter
     * @return array
     */
    public function prepareQueryMark(string $sql, array $parameter)
    {
        $binders = [];
        $query = preg_replace_callback('/\?/', function ($match) use (&$binders, $parameter) {
            $index = count($binders);
            if (array_key_exists($index, $parameter)) {
                $name = Binder::index($index);
                if (is_array($parameter[$index]) || $parameter[$index] instanceof IteratorAggregate) {
                    [$inSQL, $inBinders] = $this->prepareInParameter($parameter[$index], $index);
                    $binders = array_merge($binders, $inBinders);
                    return $inSQL;
                } else {
                    $binder = new Binder($name, $parameter[$index]);
                    $binders[] = $binder;
                    return ':' . $binder->getName();
                }
            }
            return $match[0];
        }, $sql);
        return [$query, $binders];
    }

    /**
     * 处理In语句
     * @param IteratorAggregate|array $values
     * @param string $name
     * @return array
     * @throws SQLException
     */
    public function prepareInParameter($values, string $name)
    {
        if ($this->countObject($values) <= 0) {
            throw new SQLException('on field ' . $name . ' value can\'t be empty array');
        }
        $names = [];
        $binders = [];
        foreach ($values as $value) {
            $_name = Binder::index($name);
            $binders[] = new Binder($_name, $value);
            $names[] = ':' . $_name;
        }
        return [implode(',', $names), $binders];
    }

    /**
     * @param array|Countable|IteratorAggregate $value
     * @return int
     */
    protected function countObject($value)
    {
        if (is_array($value) || $value instanceof Countable) {
            return count($value);
        }
        return iterator_count($value);
    }

    /**
     * @param string $name
     * @param string $operator
     * @param mixed $value
     * @param string $indexName
     * @return Query
     * @throws SQLException
     */
    public function createQueryOperation(string $name, string $operator, $value, string $indexName = '')
    {
        if ($value instanceof Query) {
            return new Query("`{$name}` {$operator} " . $value, $value->getBinder());
        }
        if ($value instanceof Statement) {
            return new Query("`{$name}` {$operator} (" . $value->getQuery() . ')', $value->getBinder());
        }
        if ($value instanceof IteratorAggregate || is_array($value)) {
            return $this->prepareIn($name, $operator, $value);
        }
        if (strlen($indexName) === 0) {
            $indexName = Binder::index($name);
        }
        return new Query("`{$name}` {$operator} :{$indexName}", [new Binder($indexName, $value)]);
    }

    /**
     * @param string $name
     * @param string $operator
     * @param $value
     * @return Query
     * @throws SQLException
     */
    public function getQueryForArray(string $name, string $operator, $value)
    {
        if ($value instanceof IteratorAggregate || is_array($value)) {
            return $this->prepareIn($name, $operator, $value);
        } else {
            return $this->createQueryOperation($name, $operator, $value);
        }
    }

    /**
     * @param string $where
     * @param array $whereBinder
     * @return array
     * @throws SQLException
     */
    public function prepareWhereString(string $where, array $whereBinder)
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
     * 准备条件列
     *
     * @param array $where
     * @return array
     * @throws SQLException
     */
    public function prepareWhere(array $where)
    {
        $whereArray = $this->normalizeWhereArray($where);
        $and = [];
        $binders = [];
        foreach ($whereArray as $item) {
            [$name, $option, $value] = $this->fixWhereArray($item);
            $query = $this->getQueryForArray($name, $option, $value);
            $and[] = $query->getQuery();
            $binders = array_merge($binders, $query->getBinder());
        }
        return [implode(' AND ', $and), $binders];
    }

    /**
     * @param array $item
     * @return array
     */
    protected function fixWhereArray(array $item) {
        if (count($item) === 2) {
            [$name, $value] = $item;
            return [$name, '=', $value];
        }
        return $item;
    }

    /**
     * @param array $where
     * @return array
     */
    protected function normalizeWhereArray(array $where)
    {
        if ($this->isNumberArray($where)) {
            return $where;
        }
        $newWhere = [];
        foreach ($where as $name => $value) {
            if (is_array($value) && $this->countObject($value) === 2) {
                $newWhere[] = [$name, $value[0], $value[1]];
            } else {
                $newWhere[] = [$name, '=', $value];
            }
        }
        return $newWhere;
    }

    /**
     * @param array $where
     * @return bool
     */
    protected function isNumberArray(array $where)
    {
        return is_numeric(key($where)) && array_keys($where) === range(0, count($where) - 1);
    }

    /**
     * @param string $where
     * @param string $name
     * @param $value
     * @return Query
     * @throws SQLException
     */
    public function getQueryForString(string $where, string $name, $value)
    {
        if (is_array($value) || $value instanceof IteratorAggregate) {
            [$inSQL, $binders] = $this->prepareInParameter($value, $name);
            $where = $this->replaceQuote($name, $inSQL, $where);
            return new Query($where, $binders);
        } elseif ($value instanceof Binder) {
            return new Query($where, [$value]);
        } else {
            return new Query($where, [new Binder($name, $value)]);
        }
    }

    /**
     * 准备In
     *
     * @param string $name
     * @param string $operation
     * @param IteratorAggregate|array|Query|Statement $values
     * @return Query
     * @throws SQLException
     */
    public function prepareIn(string $name, string $operation, $values)
    {
        if ($values instanceof Query || $values instanceof Statement) {
            return $this->createQueryOperation($name, 'in', $values);
        }
        [$inSQL, $binders] = $this->prepareInParameter($values, $name);
        $sql = '`' . $name . '` ' . strtoupper($operation) . ' (' . $inSQL . ')';
        return new Query($sql, $binders);
    }

    /**
     * 替换占位符
     *
     * @param string $name
     * @param string $replace
     * @param string $target
     * @return string
     */
    public function replaceQuote(string $name, string $replace, string $target)
    {
        $name = ltrim($name, ':');
        return preg_replace('/(?<!_):' . preg_quote($name) . '/', $replace, $target);
    }
}
