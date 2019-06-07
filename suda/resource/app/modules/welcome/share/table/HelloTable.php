<?php

namespace suda\welcome\table;

use suda\database\struct\TableStruct;
use suda\application\database\Table;

class HelloTable extends Table
{
    public function __construct()
    {
        parent::__construct('hello');
    }

    public function onCreateStruct(TableStruct $struct): TableStruct
    {
        return $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80),
        ]);
    }
}
