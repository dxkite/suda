<?php

namespace suda\application\template;

use Exception;
use suda\framework\Config;
use suda\framework\filesystem\FileSystem;

/**
 * 模块模板
 */
class ModuleTemplate extends ModuleTemplateBase
{
    /**
     * 获取模板源路径
     *
     * @return string|null
     */
    public function getSourcePath(): ?string
    {
        $subfix = $this->config['subfix'] ?? '.tpl.html';
        $path = $this
            ->getResource($this->module)
            ->getResourcePath($this->getTemplatePath() . '/' . $this->name . $subfix);
        $this->loadDynamicTemplateConfig($path.'.ini');
        return $path;
    }

    /**
     * @param string|null $path
     */
    protected function loadDynamicTemplateConfig(?string $path) {
        if ($path !== null) {
            $config = Config::loadConfig($path);
            // 自定义编译标签
            if ($config !== null) {
                $this->config['tag'] = $config;
            }
        }
    }

    /**
     * 获取模板编译后的路径
     *
     * @return string
     */
    public function getPath()
    {
        $output = $this->config['output'] ?? $this->application->getDataPath() . '/template/' . $this->uriName;
        FileSystem::make($output);
        return $output . '/' . $this->name . '.php';
    }

    /**
     * 包含模板
     *
     * @param string $name
     * @return void
     * @throws Exception
     */
    public function include(string $name)
    {
        $included = new self($name, $this->application, $this->request, $this->module);
        $included->parent = $this;
        $included->value = $this->value;
        echo $included->getRenderedString();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getRenderedString()
    {
        $this->application->debug()->time('render ' . $this->name);
        $code = parent::getRenderedString();
        $time = $this->application->debug()->timeEnd('render ' . $this->name);
        $this->application->debug()->recordTiming('render', $time);
        return $code;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function compile()
    {
        if ($this->isCompiled() === false) {
            $this->application->debug()->time('compile ' . $this->name);
            $result = parent::compile();
            $this->application->debug()->timeEnd('compile ' . $this->name);
            return $result;
        }
        return true;
    }


    /**
     * @param string|array|null $name
     * @param mixed $values
     * @return string
     */
    public function getUrl($name = null, $values = null)
    {
        $defaultName = $this->request->getAttribute('route');
        $query = $this->request->get() ?? [];
        if (is_string($name)) {
            if (!is_array($values)) {
                $args = func_get_args();
                array_shift($args);
                $values = $args;
            }
            return $this->application->getUrl($this->request, $name, $values ?? [], true, $this->module, $this->group);
        } elseif (is_string($defaultName)) {
            $parameter = is_array($name)?array_merge($query, $name):$query;
            return $this->application->getUrl(
                $this->request,
                $defaultName,
                $parameter,
                true,
                $this->module,
                $this->group
            );
        }
        return '#' . $defaultName;
    }


    /**
     * 判断是否是某路由
     *
     * @param string $name
     * @param array $parameter
     * @return boolean
     */
    public function is(string $name, array $parameter = null)
    {
        $full = $this->application->getRouteName($name, $this->module, $this->group);
        if ($this->request->getAttribute('route') === $full) {
            if (is_array($parameter)) {
                foreach ($parameter as $key => $value) {
                    if ($this->request->getQuery($key) != $value) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }
}
