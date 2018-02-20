<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\template;

use suda\core\Config;
use suda\core\Application;
use suda\core\Storage;
use suda\core\Hook;
use suda\core\Router;
use suda\core\Request;
use suda\exception\KernelException;

/**
 * 模板管理类
 */
class Manager
{
    /**
     * 模板输入扩展
     *
     * @var string
     */
    public static $extRaw='.tpl.';
    /**
     * 模板输出扩展
     *
     * @var string
     */
    public static $extCpl='.tpl';
    /**
     * 默认样式
     *
     * @var string
     */
    protected static $theme='default';
    /**
     * 模板编译器
     * @var null
     */
    private static $compiler=null;
    private static $staticPath='assets/static';
    private static $dynamicPath='assets';

    /**
     * 模板搜索目录
     *
     * @var array
     */
    protected static $templateSource=[];

    /**
     * 载入模板编译器
     */
    public static function loadCompile()
    {
        if (is_null(self::$compiler)) {
            Hook::exec('Manager:loadCompile::before');
            $class=class_name(conf('app.compiler', 'suda.template.compiler.suda.Compiler'));
            $instance=new $class;
            // 初始化编译器
            if ($instance instanceof Compiler) {
                self::$compiler = $instance;
            } else {
                throw new KernelException(__('app template compiler must be instance of suda\template\Compier '));
            }
        }
    }

    /**
     * 设置模板基类
     *
     * @param string $template
     * @return void
     */
    public static function setTemplate(string $template=null)
    {
        if (is_null($template)) {
            return self::$compiler->setBase();
        }
        return self::$compiler->setBase($template);
    }

    /**
     * 获取编译器
     *
     * @return void
     */
    public static function getCompiler()
    {
        return static::$compiler;
    }

    /**
     * 获取/设置模板样式
     * @param string|null $theme
     * @return mixed
     */
    public static function theme(string $theme=null)
    {
        if (!is_null($theme)) {
            self::$theme=$theme;
            Hook::exec('template:theme::change', [$theme]);
            debug()->info('change themes:'.$theme);
        }
        return self::$theme;
    }

    /**
     * 编译文件
     * @param $input
     * @return mixed
     */
    public static function compile(string $name, string $ext='html', string $outpath=null)
    {
        if ($path=self::getInputFile($name, true, $ext)) {
            if (is_null($outpath)) {
                $outpath=self::getOutputFile($name);
            }
            return self::$compiler->compile($name, $path, $outpath);
        }
        return false;
    }

    /**
     * 根据模板ID显示HTML模板
     *
     * @param string $name
     * @param string $viewpath
     * @return void
     */
    public static function display(string $name, string $viewpath=null)
    {
        return static::displayExt($name, 'html', $viewpath??'');
    }

    /**
     * 根据模板ID显示模板
     *
     * @param string $name
     * @param string $viewpath
     * @return void
     */
    public static function displayExt(string $name, string $ext='html', string $viewpath=null)
    {
        if (empty($viewpath)) {
            $viewpath=static::getOutputFile($name);
        }
        if (Storage::exist($viewpath)){
            if (Config::get('debug', true) || Config::get('exception', false)) {
                if (!static::compile($name, $ext)) {
                    echo '<b>compile theme</b> &lt;<span style="color:red;">'.static::$theme.'</span>&gt; error: '.$name.' location '.$viewpath. ' missing raw template file</br>';
                    return;
                }
            } 
        }else{
            if (!storage()->isWritable($viewpath)) {
                $viewpath=storage()->temp('tpl_');
            }
            if (!static::compile($name, $ext, $viewpath)) {
                echo '<b>missing theme</b> &lt;<span style="color:red;">'.static::$theme.'</span>&gt; template  '.$name.' file not exist '.$viewpath.'</br>';
            }
        }
        return static::displayFile($viewpath, $name);
    }

    /**
     * 根据路径显示模板
     *
     * @param string $file
     * @param string $name
     * @return void
     */
    public static function displayFile(string $file, string $name)
    {
        return static::$compiler->render($name, $file);
    }

    /**
     * 准备静态资源
     *
     * @param string $module
     * @param bool $force
     * @return bool
     */
    public static function prepareResource(string $module, bool $force=false):bool
    {
        Hook::exec('Manager:prepareResource::before', [$module]);
        // 非Debug不更新资源
        if (!conf('debug', false) && !$force) {
            return false;
        }
        // 向下兼容
        defined('APP_PUBLIC') or define('APP_PUBLIC', Storage::abspath('.'));
        $public_path=self::getPublicModulePath($module);
        $sources=self::getTemplateSource($module);
        $return=false;
        if (storage()->isWritable($public_path)) {
            foreach ($sources as $source) {
                if ($path=Storage::abspath($source.'/static')) {
                    self::copyStatic($path, $public_path);
                    $return=true;
                }
            }
        }
        return $return;
    }

    private static function getPublicModulePath(string $module)
    {
        $module_dir=Application::getInstance()->getModuleDir($module);
        return APP_PUBLIC.'/'.self::$staticPath.'/'.self::shadowName($module_dir);
    }

    public static function shadowName(string $name)
    {
        return substr(md5($name), 0, 8);
    }

    /**
     * 模块模板文件目录
     *
     * @param string $module
     * @return string
     */
    public static function getThemePath(string $module):string
    {
        return Application::getInstance()->getModulePath($module).'/resource/template/{theme}';
    }

    /**
     * 模块模板文件目录
     *
     * @param string $module
     * @return string
     */
    public static function getAppThemePath(string $module):string
    {
        $moduleName=Application::getInstance()->getModuleName($module);
        return RESOURCE_DIR.'/template/{theme}/'.$moduleName;
    }

    /**
     * 设置模板源
     *
     * @param string $module
     * @param string $path
     * @return void
     */
    public static function addTemplateSource(string $module, string $path)
    {
        if (empty($path) ||  !Application::getInstance()->checkModuleExist($module)) {
            return;
        }
        $moduleName=Application::getInstance()->getModuleName($module);
        if (!isset(self::$templateSource[$moduleName])) {
            self::$templateSource[$moduleName]=[];
        }
        if (!in_array($path, self::$templateSource[$moduleName])) {
            array_unshift(self::$templateSource[$moduleName], $path);
        }
    }

    /**
     * 获取模板源
     *
     * @param string $module
     * @return void
     */
    public static function getTemplateSource(string $module)
    {
        $moduleName=Application::getInstance()->getModuleName($module);
        $sources=[];
        if (isset(self::$templateSource[$moduleName])) {
            foreach (self::$templateSource[$moduleName] as $source) {
                if ($path=Storage::abspath(preg_replace('/\{theme\}/', static::$theme, $source))) {
                    if (!in_array($path, $sources)) {
                        $sources[]=$path;
                    }
                }
                if ($path=Storage::abspath(preg_replace('/\{theme\}/', 'default', $source))) {
                    if (!in_array($path, $sources)) {
                        $sources[]=$path;
                    }
                }
            }
        }
        return $sources;
    }


    /**
     * 注册模块模板资源目录
     *
     * @param string $module 模块名
     * @return void
     */
    public static function registerTemplateSource(string $module)
    {
        // 初始化
        if ($path=self::getThemePath($module)) {
            self::addTemplateSource($module, $path);
        }
        if ($path=self::getAppThemePath($module)) {
            self::addTemplateSource($module, $path);
        }
    }

    /**
     * 复制模板目录下静态文件
     *
     * @param string $static_path
     * @param string $path
     * @return void
     */
    protected static function copyStatic(string $static_path, string $path)
    {
        // 默认不删除模板更新
        if (conf('template.refreshAll', false)) {
            Storage::rmdirs($path);
        }
        // 复制静态资源
        $non_static=trim(str_replace(',', '|', Config::get('non-static', 'php')), '|');
        $non_static_preg='/(?<!(\.tpl\.html)|(\.('.$non_static.')))$/';
        if (Storage::isDir($static_path)) {
            debug()->trace('copy '.$static_path.' => '.$path);
            Storage::copydir($static_path, $path, $non_static_preg);
        }
    }

    /**
     * 编译动态文件
     *
     * @param string $name
     * @param [type] $parent
     * @return void
     */
    public static function file(string $name, $parent)
    {
        list($module, $basename)=Router::parseName($name, $parent->getModule());
        $name=$module.':'.$basename;
        $input=self::getInputFile($name, false);
        if (!Storage::exist($input)) {
            echo '<b>compile theme &lt;<span style="color:red;">'.self::$theme.'</span>&gt; file '.$input. ' missing file</b>';
            return;
        }
        // 获取文件夹
        $module_dir=Application::getInstance()->getModuleDir($module);
        // 获取输出
        $output=self::getOutputFile($name);
        // 动态文件导出
        $outpath=APP_PUBLIC.'/'.self::$dynamicPath.'/'.self::shadowName($module_dir).'/'.$basename;
        $path=Storage::path(dirname($outpath));
        // 编译检查
        if (Config::get('debug', true) || Config::get('exception', false)) {
            if (!self::$compiler->compile($name, $input, $output)) {
                echo '<b>compile theme &lt;<span style="color:red;">'.self::$theme.'</span>&gt; file '.$input. ' missing file</b>';
                return;
            }
        } elseif (!Storage::exist($output)) {
            if (!self::$compiler->compile($name, $input, $output)) {
                echo '<b>missing theme &lt;<span style="color:red;">'.self::$theme.'</span>&gt; file '.$input. ' missing file</b>';
                return;
            }
        }
        // 输出内容
        $public=self::$compiler->render($name, $output)->parent($parent)->getRenderedString();
        Storage::put($outpath, $public);
        // 引用文件
        $static_url=Storage::cut($outpath, APP_PUBLIC);
        $static_url=preg_replace('/[\\\\\/]+/', '/', $static_url);
        return  Request::hostBase().'/'.trim($static_url, '/');
    }

    public static function include(string $name, $parent)
    {
        list($moduleName, $basename)=Router::parseName($name, $parent->getModule());
        if ($include=self::display($moduleName.':'.$basename)) {
            return $include->parent($parent)->assign($parent->getValue());
        } else {
            $class= new class {
                public function render()
                {
                    echo '<div style="color:red" title="'.__('can\'t include %s', $this->moduleName.':'.$this->basename).'">{include:{'.$this->name.'}}</div>';
                    return;
                }
            };
            $class->moduleName=$moduleName;
            $class->basename=$basename;
            $class->name=$name;
            return $class;
        }
    }

    /**
     * 模板输入路径
     *
     * @param string $name
     * @return string
     */
    public static function getInputFile(string & $name, bool $ext=true, string $extRaw='html')
    {
        list($module, $basename)=Router::parseName($name);
        $name=$module.':'.$basename;
        $source=self::getTemplateSource($module);
        foreach ($source as $path) {
            $input=$path.'/'.trim($basename, '/').($ext?self::$extRaw.$extRaw:'');
            if (Storage::exist($input)) {
                return $input;
            }
        }
        return false;
    }

    /**
     * 模板编译后输出路径
     *
     * @param string $name
     * @return string
     */
    public static function getOutputFile(string & $name):string
    {
        list($module, $basename)=Router::parseName($name);
        $name=$module.':'.$basename;
        $module_dir=Application::getInstance()->getModuleDir($module);
        $output=VIEWS_DIR.'/'. $module_dir .'/'.$basename.self::$extCpl;
        return $output;
    }

    public static function className(string $name)
    {
        list($module, $basename)=Router::parseName($name);
        $fullName=Application::getInstance()->getModuleFullName($module);
        return 'Template_'.md5($fullName.':'.$basename);
    }

    public static function initResource(array $modules=null)
    {
        debug()->time('init resource');
        $init=[];
        $modules=$modules??Application::getInstance()->getLiveModules();
        foreach ($modules as $module) {
            if (!Application::getInstance()->checkModuleExist($module)) {
                continue;
            }
            $init[$module]['static']=self::prepareResource($module, true);
            $tempaltes=self::findModuleTemplates($module);
            foreach ($tempaltes as $name) {
                $success=self::compile($module.':'.$name);
                if ($success != true) {
                    $init[$module]['template'][$name]=false;
                } else {
                    $init[$module]['template'][$name]=true;
                }
            }
        }
        debug()->timeEnd('init resource');
        return $init;
    }

    public static function findModuleTemplates(string $module)
    {
        $templates=[];
        if (!app()->checkModuleExist($module)) {
            return false;
        }
        if ($sources=Manager::getTemplateSource($module)) {
            foreach ($sources as $source) {
                $templates=array_merge($templates, self::_findModuleTemplate($module, $source, $source));
            }
        }
        return $templates;
    }

    protected static function _findModuleTemplate(string $module, string $root, string $dirs)
    {
        $templates=[];
        $hd=opendir($dirs);
        while ($read=readdir($hd)) {
            if (strcmp($read, '.') !== 0 && strcmp($read, '..') !==0) {
                $path=$dirs.'/'.$read;
                if (preg_match('/'.preg_quote($root.'/static', '/').'/', $path)) {
                    continue;
                }
                if (is_file($path) && preg_match('/\.tpl\..+$/', $path)) {
                    $name=preg_replace('/^'.preg_quote($root, '/').'\/(.+)\.tpl\..+$/', '$1', $path);
                    $templates[]=$name;
                } elseif (is_dir($path)) {
                    $templates=array_merge($templates, self::_findModuleTemplate($module, $root, $path));
                }
            }
        }
        return $templates;
    }
    
    public static function getStaticAssetPath(string $module=null)
    {
        $module=$module??Application::getInstance()->getActiveModule();
        $path=Manager::getPublicModulePath($module);
        self::prepareResource($module);
        $static_url=Storage::cut($path, APP_PUBLIC);
        $static_url=preg_replace('/[\\\\\/]+/', '/', $static_url);
        return  '/'.$static_url;
    }

    public static function getDynamicAssetPath(string $path, string $module=null)
    {
        $module=$module??Application::getInstance()->getActiveModule();
        $path=APP_PUBLIC.'/'.self::$dynamicPath.'/'.self::shadowName(Application::getInstance()->getModuleDir($module)).'/'.$path;
        $static_url=Storage::cut($path, APP_PUBLIC);
        $static_url=preg_replace('/[\\\\\/]+/', '/', $static_url);
        return  '/'.$static_url;
    }

    public static function assetServer(string $url)
    {
        return conf('asset-server', Request::hostBase()).$url;
    }

    /**
     * 检查语法
     *
     * @param string $file
     * @return void
     */
    public static function checkSyntax(string $file)
    {
        if (storage()->exist($file)) {
            $fileContent=storage()->get($file);
            if (conf('template.checkSyntax', 'eval') == 'eval') {
                try {
                    eval('return true; ?>'.$fileContent);
                } catch (\ParseError $e) {
                    return $e;
                }
            } else {
                $fileTemp=RUNTIME_DIR.'/checkSyntax-'.md5($fileContent);
                storage()->put($fileTemp, '<?php return true ?>'."\n" .$fileContent);
                try {
                    include $fileTemp;
                } catch (\ParseError $e) {
                    return $e;
                }
            }
            return true;
        }
        return false;
    }
}

// 加载编译器
Manager::loadCompile();
