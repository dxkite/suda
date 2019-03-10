<?php
namespace suda;

use suda\framework\Event;
use suda\framework\Route;
use suda\framework\Config;
use suda\framework\Context;
use suda\framework\Request;
use suda\framework\Service;
use suda\framework\Debugger;
use suda\framework\Response;
use suda\framework\loader\Path;
use suda\framework\loader\Loader;
use suda\application\loader\ApplicationLoader;
use suda\framework\http\Request as HTTPRequest;
use suda\application\builder\ApplicationBuilder;

class Framework
{
    /**
     * 框架环境
     *
     * @var Context
     */
    protected static $context;

    public static function bootstrap(Loader $loader)
    {
        $context = new Context;
        $context->setSingle('loader', $loader);
        $context->setSingle('config', Config::class);
        $context->setSingle('event', Event::class);
        $context->setSingle('route', Route::class);
        $context->setSingle('request', function () {
            return new Request(HTTPRequest::create());
        });
        $context->setSingle('response', function () {
            return new Response;
        });
        $context->setSingle('debug', function () use ($context) {
            return Debugger::create($context)->register();
        });
        static::$context = $context;
    }

    /**
     * 获取框架组件
     *
     * @param string $name
     * @return mixed
     */
    public static function get(string $name)
    {
        return static::$context->get($name);
    }

    /**
     * 获取环境
     *
     * @return \suda\framework\Context
     */
    public static function context():Context
    {
        return static::$context;
    }

    public static function run()
    {
        $context = static::$context;
        static::get('debug')->notice('system booting');
        $service = new Service($context);

        static::get('debug')->time('ApplicationBuilder::build');
        $appLoader = new ApplicationLoader(ApplicationBuilder::build($context, SUDA_APP));
        static::get('debug')->timeEnd('ApplicationBuilder::build');
        
        $service->on('service:load-config', function ($config) use ($appLoader, $context) {
            Framework::get('debug')->time('ApplicationLoader->load');
            $appLoader->load();
            Framework::get('debug')->timeEnd('ApplicationLoader->load');
        });
        
        $service->on('service:load-environment', function ($config) use ($appLoader, $context) {
            Framework::get('debug')->time('ApplicationLoader->loadDataSource');
            $appLoader->loadDataSource();
            Framework::get('debug')->timeEnd('ApplicationLoader->loadDataSource');
        });
        
        $service->on('service:load-route', function ($route) use ($appLoader, $context) {
            Framework::get('debug')->time('ApplicationLoader->loadRoute');
            $appLoader->loadRoute();
            Framework::get('debug')->timeEnd('ApplicationLoader->loadRoute');
        });
        
        static::get('debug')->time('service->run');
        
        $service->run();

        static::get('debug')->timeEnd('service->run');
        static::get('debug')->notice('system shutdown');
    }
}
