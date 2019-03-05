<?php


trait PrepareTrait {

    protected  function parepareWhere(array $where)
    {
        $and = [];
        $binders = [];
        foreach ($where as $name => $value) {
            $_name = Binder::index($name);
            // in cause
            if (is_array($value)) {
                list($sql, $in_binder) = static::prepareIn($name, $value);
                $and[] = $sql;
                $binders = array_merge($binders, $in_binder);
            } else {
                $and[] = "`{$name}`=:{$_name}";
                $binders[] = new Binder($_name, $value);
            }
        }
        return [\implode(' AND ', $and), $binders];
    }

    protected  function prepareIn(string $name, array $values)
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
}