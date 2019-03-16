<?php
namespace suda\application\template;

use suda\application\Resource;
use suda\application\Application;
use suda\framework\runnable\Runnable;
use suda\framework\filesystem\FileSystem;
use suda\application\template\RawTemplate;
use suda\application\template\compiler\Compiler;

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
     * 渲染堆栈
     *
     * @var array
     */
    protected static $render = [];
    
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
     * 构建模板
     *
     * @param string $source
     * @param array $config
     */
    public function __construct(string $source, array $config = [])
    {
        $this->source = $source;
        $this->name = \pathinfo($source, PATHINFO_FILENAME);
        $this->config = $config;
        $this->value = [];
    }

    protected function getStaticPath()
    {
        return Resource::getPathByRelativedPath($this->config['static'] ?? 'static', dirname($this->getSourcePath()));
    }

    protected function getStaticOutpath()
    {
        $path = $this->config['assets-public'] ?? \constant('SUDA_PUBLIC').'/assets/'. $this->getStaticName();
        FileSystem::makes($path);
        return $path;
    }

    protected function getPath()
    {
        $output = $this->config['output'] ?? \constant('SUDA_DATA').'/template';
        FileSystem::makes($output);
        return $output .'/'. $this->name.'-'.substr(md5_file($this->source), 10, 8).'.php';
    }

    protected function getSourcePath()
    {
        return $this->source;
    }

    public function __toString()
    {
        $content = FileSystem::get($this->getSourcePath());
        if (FileSystem::exist($this->getPath()) === false && $content !== null) {
            $compiled = $this->compiler()->compileText($content, $this->config);
            FileSystem::put($this->getPath(), $compiled);
        }
        return $this->getRenderedString();
    }

    /**
    * 创建模板
    */
    public function parent(CompilableTemplate $template)
    {
        $this->parent = $template;
        return $this;
    }


    /**
     * 获取渲染后的字符串
     */
    public function getRenderedString()
    {
        $this->_render_start();
        echo parent::__toString();
        if ($this->extend) {
            $this->include($this->extend);
        }
        $content = $this->_render_end();
        return $content;
    }

    public function extend(string $name)
    {
        $this->extend = $name;
    }

    public function include(string $path)
    {
        $subfix = $this->config['subfix'] ?? '';
        $included = new self(Resource::getPathByRelativedPath($path. $subfix, dirname($this->source)), $this->config);
        $included->parent = $this;
        echo $included->__toString();
    }

    protected function _render_start()
    {
        array_push(self::$render, $this->name);
        ob_start();
    }

    protected function _render_end()
    {
        array_pop(self::$render);
        $content = trim(ob_get_clean());
        return $content;
    }

    public function data(string $name, ...$args)
    {
        if (func_num_args() > 1) {
            return (new Runnable($name))->run($this, ...$args);
        }
        return (new Runnable($name))->apply([$this]);
    }

    protected function getStaticPrefix()
    {
        $this->prepareStaticSource();
        $prefix = $this->config['assets-prefix'] ?? defined('SUDA_ASSETS')?\constant('SUDA_ASSETS'):'/assets';
        return $prefix .'/'.$this->getStaticName();
    }

    protected function prepareStaticSource()
    {
        if (is_dir($this->getStaticPath()) && !\in_array($this->getStaticPath(), static::$copyedStaticPaths)) {
            FileSystem::copyDir($this->getStaticPath(), $this->getStaticOutpath());
            static::$copyedStaticPaths[] = $this->getStaticPath();
        }
    }

    protected function getStaticName()
    {
        return $this->config['static-name'] ?? substr(md5($this->staticPath), 0, 8);
    }

    public function parseData($exp)
    {
        return "<?php \$this->data{$exp}; ?>";
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
        } catch (\Exception $e) {
            echo '<div style="color:red">'.$e->getMessage().'</div>';
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

    protected function createCompiler():Compiler
    {
        return new Compiler;
    }
}
