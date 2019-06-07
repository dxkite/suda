<?php
namespace suda\database\exception;


use Exception;

class SQLException extends Exception
{
    const ERR_QUERY = 1;
    const ERR_PREPARE = 2;
    const ERR_NO_CONNECTION = 3;
    const ERR_TRANSACTION = 4;
    const ERR_CONFIGURATION = 5;
}
