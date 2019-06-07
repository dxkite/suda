<?php


namespace suda\application\database;


use suda\database\middleware\Middleware;

class QueryAccess extends \suda\database\statement\QueryAccess
{
    public function __construct(Middleware $middleware = null)
    {
        parent::__construct(Database::application()->getDataSource(), $middleware);
    }
}