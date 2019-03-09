<?php
namespace suda\orm\exception;

class SQLException extends \ErrorException
{
    const ERROR_QUERY = 1;
    const ERROR_PREPARE = 2;
    const ERROR_NO_CONNECTION = 3;
    const ERROR_TRANSACTION = 4;
    const ERROR_CONFIGURATION = 5;
}
