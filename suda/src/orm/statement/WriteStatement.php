<?php
namespace suda\orm\statement;


use suda\orm\statement\Statement;

class WriteStatement extends Statement
{
    public function __construct(string $sql, ...$args)
    {
        parent::__construct($sql, ...$args);
        $this->type =  Statement::WRITE;
    }


    
}
