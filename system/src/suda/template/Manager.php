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
    public static $extRaw='.tpl.html';
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
    protected static $templateSource=[];
    /**
     * 载入模板编译器
     */
    public static function loadCompile()
    {
        if (is_null(self::$compiler)) {
            Hook::exec('Manager:loadCompile::before');
            // 调用工厂方法
            self::$compiler=Factory::compiler(conf('app.compiler', 'SudaCompiler'));
        }
    }

    // 设置模板基类
    public static function setTemplate(string $template=null)
    {
        if (is_null($template)) {
            return self::$compiler->setBase();
        }
        return self::$compiler->setBase($template);
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
            debug()->info('change themes:'.$theme);
        }
        return self::$theme;
    }

    /**
     * 编译文件
     * @param $input
     * @return mixed
     */
    public static function compile(string $name)
    {
        if ($path=self::getInputFile($name)) {
            return self::$compiler->compile($name, $path, self::getOutputFile($name));
        }
        return false;
    }

    /**
     * 根据模板ID显示模板
     *
     * @param string $name
     * @param string $viewpath
     * @return void
     */
    public static function display(string $name, string $viewpath=null)
    {
        if (is_null($viewpath)) {
            $viewpath=self::getOutputFile($name);
        }

        if (Config::get('debug', true)) {
            if (!self::compile($name)) {
                echo '<b>compile theme</b> &lt;<span style="color:red;">'.self::$theme.'</span>&gt; error: '.$name.' location '.$viewpath. ' missing raw template file</br>';
                return;
            }
        } elseif (!Storage::exist($viewpath)) {
            echo '<b>missing theme</b> &lt;<span style="color:red;">'.self::$theme.'</span>&gt; template file '.$name.'  location '. Storage::cut($viewpath, DATA_DIR). '</br>';
            return;
        }
        return self::displayFile($viewpath, $name);
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
        return self::$compiler->render($name, $file);
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
        foreach ($sources as $source) {
            if ($path=Storage::abspath($source.'/static')) {
                self::copyStatic($path, $public_path);
                $return=true;
            }
        }
        return $return;
    }

    private static function getPublicModulePath(string $module)
    {
        $module_dir=Application::getModuleDir($module);
        return Storage::path(APP_PUBLIC.'/'.self::$staticPath.'/'.self::shadowName($module_dir));
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
        $theme=Application::getModulePath($module).'/resource/template/'.self::$theme;
        return Storage::abspath($theme);
    }

    /**
     * 模块模板文件目录
     *
     * @param string $module
     * @return string
     */
    public static function getAppThemePath(string $module):string
    {
        $module_name=Application::getModuleName($module);
        $theme=RESOURCE_DIR.'/template/'.self::$theme.'/'.$module_name;
        return Storage::abspath($theme);
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
        $module_name=Application::getModuleName($module);
        if(!isset(self::$templateSource[$module_name])){
            self::$templateSource[$module_name]=[];
        }
        if (!in_array($path, self::$templateSource[$module_name]) && $abspath= Storage::abspath($path)) {
            self::$templateSource[$module_name][]=$abspath;
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
        $module_name=Application::getModuleName($module);
        // 未初始化
        if(!isset(self::$templateSource[$module_name])){
            self::addTemplateSource($module,self::getAppThemePath($module));
            self::addTemplateSource($module,self::getThemePath($module));
        }
        return self::$templateSource[$module_name] ;
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
        $input=self::getInputFile($name,false);
        if (!Storage::exist($input)) {
            echo '<b>compile theme &lt;<span style="color:red;">'.self::$theme.'</span>&gt; file '.$input. ' missing file</b>';
            return; //文件目录不存在
        }
        // 获取文件夹
        $module_dir=Application::getModuleDir($module);
        // 获取输出
        $output=self::getOutputFile($name);
        // 动态文件导出
        $outpath=APP_PUBLIC.'/'.self::$dynamicPath.'/'.self::shadowName($module_dir).'/'.$basename;
        $path=Storage::path(dirname($outpath));
        // 编译检查
        if (Config::get('debug', true)) {
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
        list($module_name, $basename)=Router::parseName($name, $parent->getModule());
        return self::display($module_name.':'.$basename)->parent($parent)->assign($parent->getValue());
    }

    /**
     * 模板输入路径
     *
     * @param string $name
     * @return string
     */
    public static function getInputFile(string & $name, bool $ext=true)
    {
        list($module, $basename)=Router::parseName($name);
        $name=$module.':'.$basename;
        $source=self::getTemplateSource($module);
        foreach ($source as $path) {
            $input=$path.'/'.trim($basename, '/').($ext?self::$extRaw:'');
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
        $module_dir=Application::getModuleDir($module);
        $output=VIEWS_DIR.'/'. $module_dir .'/'.$basename.self::$extCpl;
        return $output;
    }

    public static function className(string $name)
    {
        list($module, $basename)=Router::parseName($name);
        return 'Template_'.md5($basename);
    }

    public static function initResource(array $modules=null)
    {
        debug()->time('init resource');
        $init=[];
        $modules=$modules??Application::getLiveModules();
        foreach ($modules as $module) {
            if (!Application::checkModuleExist($module)) {
                continue;
            }
            $sources=self::getTemplateSource($module);
            foreach ($sources as $path) {
                self::compileModulleFile($module, $path, $path);
            }
            $init[$module]=self::prepareResource($module, true);
        }
        debug()->timeEnd('init resource');
        return $init;
    }

    private static function compileModulleFile(string $module, string $root, string $dirs)
    {
        $hd=opendir($dirs);
        while ($read=readdir($hd)) {
            if (strcmp($read, '.') !== 0 && strcmp($read, '..') !==0) {
                $path=$dirs.'/'.$read;
                if (preg_match('/'.preg_quote($root.'/static', '/').'/', $path)) {
                    continue;
                }
                if (is_file($path) && preg_match('/\.tpl\..+$/', $path)) {
                    self::compileFile($path, $module, $root);
                } elseif (is_dir($path)) {
                    self::compileModulleFile($module, $root, $path);
                }
            }
        }
    }

    private static function compileFile(string $path, string $module, string $root)
    {
        $name=preg_replace('/^('.preg_quote($root, '/').')?(.+)'.preg_quote(self::$extRaw, '/').'/', '$2', $path);
        $name=$module.':'.trim($name, '/');
        $success=self::compile($name);
        // debug()->debug(__('[%d] compiling ==> %s', $success, $name));
        return $success;
    }
    
    public static function getStaticAssetPath(string $module=null)
    {
        $module=$module??Application::getActiveModule();
        $path=Manager::getPublicModulePath($module);
        self::prepareResource($module);
        $static_url=Storage::cut($path, APP_PUBLIC);
        $static_url=preg_replace('/[\\\\\/]+/', '/', $static_url);
        return  '/'.$static_url;
    }

    public static function getDynamicAssetPath(string $path, string $module=null)
    {
        $module=$module??Application::getActiveModule();
        $path=APP_PUBLIC.'/'.self::$dynamicPath.'/'.self::shadowName(Application::getModuleDir($module)).'/'.$path;
        $static_url=Storage::cut($path, APP_PUBLIC);
        $static_url=preg_replace('/[\\\\\/]+/', '/', $static_url);
        return  '/'.$static_url;
    }

    public static function assetServer(string $url)
    {
        return conf('asset-server', Request::hostBase()).$url;
    }
}

Manager::loadCompile();
