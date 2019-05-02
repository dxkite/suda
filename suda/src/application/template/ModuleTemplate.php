<?php
namespace suda\application\template;

use function constant;
use function is_array;
use function is_string;
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

    /**
     * 获取模板源路径
     *
     * @return string|null
     */
    public function getSourcePath():?string
    {
        $subfix = $this->config['subfix'] ?? '.tpl.html';
        return $this->getResource($this->module)->getResourcePath($this->getTemplatePath().'/'.$this->name.$subfix);
    }

    /**
     * 获取模板编译后的路径
     *
     * @return string
     */
    public function getPath()
    {
        $output = $this->config['output'] ?? constant('SUDA_DATA').'/template/'. $this->uriName;
        FileSystem::make($output);
        return $output .'/'. $this->name.'.php';
    }

    /**
     * 包含模板
     *
     * @param string $name
     * @return void
     */
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
        } elseif (is_array($name) && is_string($defaultName)) {
            return $this->application->getUrl($this->request, $defaultName, array_merge($this->request->get() ?? [], $name) , true, $this->module, $this->group);
        } elseif (is_string($defaultName)) {
            return $this->application->getUrl($this->request, $defaultName, $this->request->get() ?? [], true, $this->module, $this->group);
        }
        return '#'.$defaultName;
    }

    /**
     * 判断是否是某路由
     *
     * @param string $name
     * @param array $parameter
     * @return boolean
     */
    public function is(string $name, array $parameter = null) {
        $full = $this->application->getRouteName($name, $this->module, $this->group);
        if ($this->request->getAttribute('route') === $full) {
            if (is_array($parameter)) {
                foreach ($parameter as $key => $value) {
                    if ($this->request->getQuery($key) != $value){
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    } 
}
