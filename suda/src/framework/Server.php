<?php
namespace suda\framework;

use suda\framework\Container;
use suda\framework\runnable\Runnable;


class Server
{
    /**
     * 容器
     *
     * @var \suda\framework\Container
     */
    public static $container;

    public function __construct() {
        
    }

    public function on(string $event, Runnable $callback) {
        Server::$container->get('event')->listen($event, $callback);
    }
}
