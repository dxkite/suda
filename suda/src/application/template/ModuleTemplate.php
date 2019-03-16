<?php
namespace suda\application\template;

use suda\application\Application;
use suda\application\template\compiler\Compiler;
use suda\application\template\CompilableTemplate;
use suda\application\template\ModuleTemplateCommand;

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

    /**
     * 应用环境
     *
     * @var Application
     */
    protected $application;

    public function __construct(string $name, Application $application)
    {
        if (strpos($name, ':') > 0) {
            list($this->module, $this->name) = \explode(':', $name);
        } else {
            $this->name = $name;
        }
    }

    protected function getCompiler():Compiler
    {
        $compiler = new Compiler;
        $compiler->registerCommand(new ModuleTemplateCommand);
        return $compiler;
    }

    public function getUrl($name = null, $values = null)
    {
        if (is_string($name)) {
            if (!is_array($values)) {
                $args = func_get_args();
                array_shift($args);
                $values = $args;
            }
            return $this->application->getUrl($name, $values ?? [], true, $this->module);
        } elseif (is_array($name)) {
            return $this->application->getUrl($this->application->request()->getAttribute('route'), $name, true, $this->module);
        } else {
            return $this->application->getUrl($this->application->request()->getAttribute('route'), $this->application->request()->get() ?? [], true, $this->module);
        }
    }
}
