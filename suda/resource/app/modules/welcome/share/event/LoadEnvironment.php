<?php
namespace suda\welcome\event;

use suda\application\Application;
use suda\framework\Config;

class LoadEnvironment
{
    public static function handle(Config $config, Application $app)
    {
        $config->set('role', 'admin');
        $app->debug()->info('load environment');
    }
}
