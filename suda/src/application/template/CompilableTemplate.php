<?php

namespace suda\application\template;

use Exception;
use suda\application\Resource;
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
    protected static $copiedStaticPaths = [];

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

    /**
     * @param string|null $name
     * @return string
     */
    protected function getStaticPath(?string $name = null)
    {
        $name = is_null($name) ? $this->config['static'] ?? 'static' : $name;
        return Resource::getPathByRelativePath($name, dirname($this->getSourcePath()));
    }

    /**
     * @param string|null $name
     * @return mixed|string
     */
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
        return parent::getRenderedString();
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
                $compiler = $this->compiler();
                if (array_key_exists('tag', $this->config)) {
                    $tags = $compiler->getTags();
                    $compiled = $compiler->compileText($content, $this->config['tag']);
                    $compiler->setTags($tags);
                } else {
                    $compiled = $this->compiler()->compileText($content);
                }
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
     * @param string|null $name
     * @return string
     */
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

    /**
     * @param string|null $name
     */
    protected function prepareStaticSource(?string $name = null)
    {
        $isDebug = defined('SUDA_DEBUG') ? constant('SUDA_DEBUG') : false;
        $staticPath = $this->getStaticPath($name);
        if ($isDebug &&
            is_dir($staticPath) &&
            !in_array($staticPath, static::$copiedStaticPaths)) {
            FileSystem::copyDir($staticPath, $this->getStaticOutputPath($name));
            static::$copiedStaticPaths[] = $staticPath;
        }
    }

    /**
     * @param string|null $name
     * @return bool|mixed|string
     */
    protected function getStaticName(?string $name = null)
    {
        return $this->config['static-name'] ?? substr(md5($this->getStaticPath($name)), 0, 8);
    }


    /**
     * @return Compiler
     */
    protected function compiler()
    {
        if (static::$compiler === null) {
            static::$compiler = $this->createCompiler();
        }
        return static::$compiler;
    }

    /**
     * @return Compiler
     */
    protected function createCompiler(): Compiler
    {
        $compiler = new Compiler;
        $compiler->init();
        return $compiler;
    }
}
