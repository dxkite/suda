<?php

namespace suda\application\template;

use Exception;
use ReflectionException;
use suda\application\Resource;
use suda\framework\runnable\Runnable;
use suda\framework\filesystem\FileSystem;
use suda\application\template\compiler\Compiler;
use suda\application\exception\NoTemplateFoundException;

/**
 * 可编译模板
 */
class CompilableTemplate extends RawTemplate
{
    /**
     * 模板源
     *
     * @var string
     */
    protected $source;

    /**
     * 模板配置
     *
     * @var array
     */
    protected $config;

    /**
     * 输出目录
     *
     * @var string
     */
    protected $output;

    /**
     * 父模版
     *
     * @var CompilableTemplate|null
     */
    protected $parent = null;


    /**
     * 模板钩子
     *
     * @var array
     */
    protected $hooks = [];

    /**
     * 继承的模板
     *
     * @var string|null
     */
    protected $extend = null;

    /**
     * 编译器
     *
     * @var Compiler
     */
    protected static $compiler;

    /**
     * 检测已经拷贝的目录
     *
     * @var array
     */
    protected static $copyedStaticPaths = [];

    /**
     * 静态目录
     *
     * @var string
     */
    protected $staticPath;

    /**
     * 模板名
     *
     * @var string
     */
    protected $name;

    /**
     * PHP模板
     * @var bool
     */
    protected $raw;

    /**
     * 构建模板
     *
     * @param string $source
     * @param array $config
     * @param bool $raw
     */
    public function __construct(string $source, array $config = [], bool $raw = false)
    {
        parent::__construct('', []);
        $this->source = $source;
        $this->name = pathinfo($source, PATHINFO_FILENAME);
        $this->config = $config;
        $this->raw = $raw;
    }

    protected function getStaticPath(?string $name = null)
    {
        $name = is_null($name) ? $this->config['static'] ?? 'static' : $name;
        return Resource::getPathByRelativePath($name, dirname($this->getSourcePath()));
    }

    protected function getStaticOutputPath(?string $name = null)
    {
        $public = defined('SUDA_PUBLIC') ? constant('SUDA_PUBLIC') : '.';
        $path = $this->config['assets-public'] ?? $public . '/assets/' . $this->getStaticName($name);
        FileSystem::make($path);
        return $path;
    }

    /**
     * 获取编译后的路径
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->isRaw()) {
            return $this->source;
        }
        $output = $this->config['output'] ?? constant('SUDA_DATA') . '/template';
        FileSystem::make($output);
        return $output . '/' . $this->name . '-' . substr(md5_file($this->getSourcePath()), 10, 8) . '.php';
    }

    /**
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * 获取源路径
     *
     * @return string|null
     */
    public function getSourcePath(): ?string
    {
        return $this->source ?? null;
    }

    /**
     * 输出
     * @ignore-dump
     * @return string
     * @throws Exception
     */
    public function getRenderedString()
    {
        $this->compile();
        return $this->render();
    }

    /**
     * 编译
     *
     * @return bool
     * @throws Exception
     */
    protected function compile()
    {
        if ($this->isCompiled() === false && ($sourcePath = $this->getSourcePath()) !== null) {
            $destPath = $this->getPath();
            $content = FileSystem::get($sourcePath);
            if ($content !== null) {
                $compiled = $this->compiler()->compileText($content, $this->config);
                FileSystem::make(dirname($destPath));
                FileSystem::put($destPath, $compiled);
            }
            return true;
        }
        return false;
    }

    /**
     * 检查是否编译过
     * @return bool
     */
    protected function isCompiled()
    {
        $sourcePath = $this->getSourcePath();
        if ($sourcePath === null) {
            throw new NoTemplateFoundException(
                'missing source ' . $this->name,
                E_USER_ERROR,
                $this->name,
                NoTemplateFoundException::T_SOURCE
            );
        }
        $source = FileSystem::exist($sourcePath);
        $dest = FileSystem::exist($this->getPath());
        $isDebug = defined('SUDA_DEBUG') ? constant('SUDA_DEBUG') : false;
        $notCompiled = $source === true && $dest === false;
        return ($notCompiled || $isDebug) === false;
    }

    /**
     * 获取渲染后的字符串
     * @ignore-dump
     * @throws Exception
     */
    public function render()
    {
        ob_start();
        echo parent::getRenderedString();
        if ($this->extend) {
            $this->include($this->extend);
        }
        $content = trim(ob_get_clean());
        return $content;
    }

    /**
     * 创建模板
     * @param $template
     * @return CompilableTemplate
     */
    public function parent($template)
    {
        $this->parent = $template;
        return $this;
    }


    public function extend(string $name)
    {
        $this->extend = $name;
    }

    /**
     * @param string $path
     * @throws Exception
     */
    public function include(string $path)
    {
        $subfix = $this->config['subfix'] ?? '';
        $included = new self(Resource::getPathByRelativePath($path . $subfix, dirname($this->source)), $this->config);
        $included->parent = $this;
        echo $included->getRenderedString();
    }

    /**
     * @param string $name
     * @param mixed ...$args
     * @return mixed
     * @throws ReflectionException
     */
    public function data(string $name, ...$args)
    {
        if (func_num_args() > 1) {
            return (new Runnable($name))->run($this, ...$args);
        }
        return (new Runnable($name))->apply([$this]);
    }

    protected function getStaticPrefix(?string $name = null)
    {
        $this->prepareStaticSource($name);
        if (array_key_exists('assets-prefix', $this->config)) {
            $prefix = $this->config['assets-prefix'];
        } elseif (defined('SUDA_ASSETS')) {
            $prefix = constant('SUDA_ASSETS');
        } else {
            $prefix = '/assets';
        }
        return '/' . ltrim($prefix, '/') . '/' . $this->getStaticName($name);
    }

    protected function prepareStaticSource(?string $name = null)
    {
        $isDebug = defined('SUDA_DEBUG') ? constant('SUDA_DEBUG') : false;
        $staticPath = $this->getStaticPath($name);
        if ($isDebug &&
            is_dir($staticPath) &&
            !in_array($staticPath, static::$copyedStaticPaths)) {
            FileSystem::copyDir($staticPath, $this->getStaticOutputPath($name));
            static::$copyedStaticPaths[] = $staticPath;
        }
    }

    protected function getStaticName(?string $name = null)
    {
        return $this->config['static-name'] ?? substr(md5($this->getStaticPath($name)), 0, 8);
    }

    public function insert(string $name, $callback)
    {
        // 存在父模板
        if ($this->parent) {
            $this->parent->insert($name, $callback);
        } else {
            // 添加回调钩子
            $this->hooks[$name][] = new Runnable($callback);
        }
    }

    public function exec(string $name)
    {
        try {
            // 存在父模板
            if ($this->parent) {
                $this->parent->exec($name);
            } elseif (isset($this->hooks[$name])) {
                foreach ($this->hooks[$name] as $hook) {
                    $hook->run();
                }
            }
        } catch (Exception $e) {
            echo '<div style="color:red">' . $e->getMessage() . '</div>';
            return;
        }
    }

    protected function compiler()
    {
        if (static::$compiler === null) {
            static::$compiler = $this->createCompiler();
        }
        return static::$compiler;
    }

    protected function createCompiler(): Compiler
    {
        $compiler = new Compiler;
        $compiler->init();
        return $compiler;
    }
}
