<?php
namespace suda\application\template;

use suda\framework\Config;
use suda\framework\Request;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\filesystem\FileSystem;
use suda\application\template\compiler\Compiler;
use suda\application\template\CompilableTemplate;
use suda\application\template\ModuleTemplateBase;
use suda\application\template\ModuleTemplateCompiler;

/**
 * 模块模板
 */
class ModuleTemplate extends ModuleTemplateBase
{
    public function __construct(string $name, Application $application, Request $request, ?string $defaultModule = '')
    {
        parent::__construct($name, $application, $request, $defaultModule);
    }

    protected function getSourcePath()
    {
        $subfix = $this->config['subfix'] ?? '.tpl.html';
        return $this->getResource($this->module)->getResourcePath($this->getTemplatePath().'/'.$this->name.$subfix);
    }

    protected function getPath()
    {
        $output = $this->config['output'] ?? \constant('SUDA_DATA').'/template/'. $this->uriName;
        FileSystem::make($output);
        return $output .'/'. $this->name.'.php';
    }

    public function include(string $name)
    {
        $included = new self($name, $this->application, $this->request, $this->module);
        $included->parent = $this;
        $included->value = $this->value;
        echo $included->getRenderedString();
    }

    public function getUrl($name = null, $values = null)
    {
        $defaultName = $this->request->getAttribute('route');
        if (is_string($name)) {
            if (!is_array($values)) {
                $args = func_get_args();
                array_shift($args);
                $values = $args;
            }
            return $this->application->getUrl($this->request, $name, $values ?? [], true, $this->module, $this->group);
        } elseif (is_array($name) && \is_string($defaultName)) {
            return $this->application->getUrl($this->request, $defaultName, array_merge($name, $this->request->get() ?? []) , true, $this->module, $this->group);
        } elseif (is_string($defaultName)) {
            return $this->application->getUrl($this->request, $defaultName, $this->request->get() ?? [], true, $this->module, $this->group);
        }
        return '#'.$defaultName;
    }
}
