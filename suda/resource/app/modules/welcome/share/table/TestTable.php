<?php
namespace suda\welcome\table;

use suda\orm\DataSource;
use suda\orm\TableStruct;
use suda\application\database\Table;

class TestTable extends Table
{
    public function __construct(DataSource $datasource)
    {
        parent::__construct('hello', $datasource);
        $this->getSource()->write()->createTable($this->getStruct()->getFields());
    }

    public function onCreateStruct(TableStruct $struct):TableStruct
    {
        return $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80),
        ]);
    }
}
