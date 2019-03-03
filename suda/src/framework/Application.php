<?php
namespace suda\framework;

use suda\framework\Context;
use suda\framework\runnable\Runnable;

class Application
{
    /**
     * 环境
     *
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function on(string $event, Runnable $callback)
    {
        $this->context->get('event')->listen($event, $callback);
    }

    
    public function run()
    {
    }
}
