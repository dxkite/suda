<?php
namespace cn\atd3\table;
class TestTable extends \suda\archive\Table {
    public function  __construct(){
        // table name 
        parent::__construct('test');
    }

    protected function onBuildCreator($table){
        $table->fields(
            $table->field('id','bigint')->primary()->auto(),
            $table->field('name','varchar',30),
            $table->field('value','text')
        );
        return $table;
    }
}