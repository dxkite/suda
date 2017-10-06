<?php
namespace suda\archive;
use suda\archive\creator\Table as TableCreator;

abstract class Table  extends DAO {
    abstract protected function onCreateTable($table);

    public function createTable(){
        return self::initFromTable($this->onCreateTable(new TableCreator($this->tableName, 'utf8')));
    }

    protected function initFromDatabase()
    {
         if(!parent::initFromDatabase()){
            return $this->createTable();
         }
         return true;
    }

    protected function initFromTable(TableCreator $table)
    {
        (new SQLQuery($table->getSQL()))->exec();
        $this->primaryKeys=$table->getPrimaryKeysName();
        $this->fields=$table->getFieldsName();
        return true;
    }
}