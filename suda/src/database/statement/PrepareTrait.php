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
    use WherePrepareTrait;
    
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
