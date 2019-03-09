<?php
namespace suda\application;

use suda\orm\DataSource;
use suda\framework\Context;
use suda\framework\Request;
use suda\application\Module;
use suda\framework\Response;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\framework\runnable\Runnable;
use suda\framework\arrayobject\ArrayDotAccess;
use suda\application\processor\RequestProcessor;
use suda\application\processor\FileRequestProcessor;
use suda\application\processor\TemplateRequestProcessor;

/**
 * 应用程序
 */
class Application implements RequestProcessor
{
    /**
     * 应用路径
     *
     * @var string
     */
    protected $path;

    /**
     * 模块集合
     *
     * @var ModuleBag
     */
    protected $module;

    /**
     * 语言
     *
     * @var string
     */
    protected $locate = 'zh-cn';

    /**
     * 使用的样式
     *
     * @var string
     */
    protected $style = 'default';
    
    /**
     * 路由组
     *
     * @var array
     */
    protected $routeGroup;

    /**
     * 配置数组
     *
     * @var array
     */
    protected $manifast;

    /**
     * 模块路径
     *
     * @var string[]
     */
    protected $modulePaths;

    /**
     * 运行环境
     *
     * @var Context $context
     */
    protected $context;

    /**
     * 资源目录
     *
     * @var Resource
     */
    protected $resource;


    /**
     * 数据源
     *
     * @var DataSource
     */
    protected $dataSource;
    public function __construct(string $path, array $manifast)
    {
        $this->path = $path;
        $this->module = new ModuleBag;
        $this->manifast = $manifast;
        $this->routeGroup = $manifast['route-group'] ?? ['default'];
        $this->initProperty($manifast);
        $this->resource = new Resource([Resource::getPathByRelativedPath($manifast['resource'] ?? './resource', $path)]);
    }

    /**
     * 添加模块
     *
     * @param \suda\application\Module $module
     * @return void
     */
    public function add(Module $module)
    {
        $this->module->add($module);
    }

    /**
     * 查找模块
     *
     * @param string $name
     * @return \suda\application\Module|null
     */
    public function find(string $name):?Module
    {
        return $this->module->get($name);
    }

    /**
     * Get 使用的样式
     *
     * @return  string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set 使用的样式
     *
     * @param  string  $style  使用的样式
     *
     * @return  self
     */
    public function setStyle(string $style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get 语言
     *
     * @return  string
     */
    public function getLocate()
    {
        return $this->locate;
    }

    /**
     * Set 语言
     *
     * @param  string  $locate  语言
     *
     * @return  self
     */
    public function setLocate(string $locate)
    {
        $this->locate = $locate;

        return $this;
    }
    
    /**
     * Get 配置数组
     *
     * @return  mixed
     */
    public function getManifast(string $name = null, $default = null)
    {
        if ($name !== null) {
            return ArrayDotAccess::get($this->manifast, $name, $default);
        }
        return $this->manifast;
    }

    protected function initProperty(array $manifast)
    {
        if (\array_key_exists('style', $manifast)) {
            $this->style = \strtolower($manifast['style']);
        }
        if (\array_key_exists('locate', $manifast)) {
            $this->locate = \strtolower($manifast['locate']);
        }
        if (\array_key_exists('module-paths', $manifast)) {
            $this->modulePaths = $manifast['module-paths'];
        } else {
            $this->modulePaths = [ Resource::getPathByRelativedPath('modules', $this->path) ];
        }
    }

    /**
     * Get 模块路径
     *
     * @return  string[]
     */
    public function getModulePaths()
    {
        return $this->modulePaths;
    }

    /**
     * Get $context
     *
     * @return  Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set $context
     *
     * @param  Context  $context  $context
     *
     * @return  self
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get 路由组
     *
     * @return  array
     */
    public function getRouteGroup()
    {
        return $this->routeGroup;
    }

    /**
     * 请求处理
     *
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function onRequest(Request $request, Response $response)
    {
        $route = $request->getAttribute('route', []);
        $runnable = null;
        if (\array_key_exists('class', $route)) {
            $runnable = $this->className($route['class']).'->onRequest';
        } elseif (\array_key_exists('source', $route)) {
            $request->setAttribute('source', $route['source']);
            $runnable = FileRequestProcessor::class.'->onRequest';
        } elseif (\array_key_exists('template', $route)) {
            $request->setAttribute('template', $route['template']);
            $runnable = TemplateRequestProcessor::class.'->onRequest';
        } else {
            throw new \Exception('request failed');
        }
        return (new Runnable($runnable))($request, $response);
    }

    protected function className(string $name)
    {
        return str_replace(['.','/'], '\\', $name);
    }

    /**
     * Get 数据源
     *
     * @return  Resource
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * Set 数据源
     *
     * @param  Resource  $resource  数据源
     *
     * @return  self
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get 数据源
     *
     * @return  DataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Set 数据源
     *
     * @param  DataSource  $dataSource  数据源
     *
     * @return  self
     */
    public function setDataSource(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;

        return $this;
    }
}
