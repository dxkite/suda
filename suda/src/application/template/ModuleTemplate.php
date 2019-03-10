<?php
namespace suda\application\template;

use suda\application\Application;
use suda\application\template\CompilableTemplate;


/**
 * 模块模板
 */
class ModuleTemplate extends CompilableTemplate
{
    /**
     * 模板模块
     *
     * @var string|null
     */
    protected $module = null;

    /**
     * 模板名
     *
     * @var string
     */
    protected $name;

    public function __construct(string $name, Application $application)
    {
        if (strpos($name, ':') > 0) {
            list($this->module, $this->name) = \explode(':', $name);
        } else {
            $this->name = $name;
        }
    }
}
