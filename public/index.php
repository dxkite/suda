<?php

use suda\framework\Server;


define('SUDA_APP', __DIR__.'/../app');
define('SUDA_DATA', __DIR__.'/../app/data');
define('SUDA_SYSTEM', __DIR__.'/../suda');

require_once SUDA_SYSTEM.'/src/loader/web-run.php';


var_dump(Server::$container->get('request'));