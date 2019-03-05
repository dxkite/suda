<?php


trait PrepareTrait {
    
    /**
     * 准备选择列
     *
     * @param string|array $wants
     * @return string
     */
    public  function prepareWants($wants):string {
        if (is_string($wants)) {
            $fields=$wants;
        } else {
            $field=[];
            foreach ($wants as $want) {
                $field[]="`$want`";
            }
            $fields=implode(',', $field);
        }
        return $fields;
    }

    protected  function parepareWhere(array $where)
    {
        $and = [];
        $binders = [];
        foreach ($where as $name => $value) {
            $_name = Binder::index($name);
            // in cause
            if (is_array($value)) {
                list($sql, $in_binder) = $this->prepareIn($name, $value);
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