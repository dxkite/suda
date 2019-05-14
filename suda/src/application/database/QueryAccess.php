<?php


namespace suda\application\database;


use suda\orm\middleware\Middleware;

class QueryAccess extends \suda\orm\statement\QueryAccess
{
    public function __construct(Middleware $middleware = null)
    {
        parent::__construct(Database::application()->getDataSource(), $middleware);
    }
}