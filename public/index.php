<?php

use suda\framework\Server;

define('SUDA_APP', __DIR__.'/../app');
define('SUDA_DATA', __DIR__.'/../app/data');
define('SUDA_SYSTEM', __DIR__.'/../suda');

require_once SUDA_SYSTEM.'/src/loader/web-run.php';


$route = Server::$container->get('route');

$route->get('index', '/', function ($request, $response) {
    $response->sendContent('hello world');
});

$match = $route->match(Server::$container->get('request'));

if ($match) {
    $match->run(Server::$container->get('request'), Server::$container->get('response'));
} else {
    echo '404';
}
