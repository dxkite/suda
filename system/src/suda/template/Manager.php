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

    /**
     * 获取/设置模板样式
     * @param string|null $theme
     * @return mixed
     */
    public static function theme(string $theme=null)
    {
        if (!is_null($theme)) {
            self::$theme=$theme;
            _D()->info('change themes:'.$theme);
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
        self::loadCompile();
        return self::$compiler->compile($name, self::getInputPath($name), self::getOutputPath($name));
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
            $viewpath=self::getOutputPath($name);
        }

        if (Config::get('debug', true)) {
            if (!self::compile($name)) {
                echo '<b>compile theme &lt;<span style="color:red;">'.self::$theme.'</span>&gt; error: '.$name.' location '.$viewpath. ' missing raw template file</b>';
                return;
            }
        } elseif (!Storage::exist($viewpath)) {
            // _D()->debug('perpare viewpath',$viewpath);
            if (!self::compile($name)) {
                echo '<b>missing theme &lt;<span style="color:red;">'.self::$theme.'</span>&gt; template file '.$name.'  location '.$viewpath. '</b>';
                return;
            }
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
        self::loadCompile();
        return self::$compiler->render($name, $file);
    }

    /**
    * 准备静态资源
    */
    public static function prepareResource(string $module)
    {
        Hook::exec('Manager:prepareResource::before', [$module]);
        $module_dir=Application::getModuleDir($module);
        // 向下兼容
        defined('APP_PUBLIC') or define('APP_PUBLIC', Storage::path('.'));
        $static_path=Storage::path(self::getThemePath($module).'/static');
        $app_static_path=Storage::path(self::getAppThemePath($module).'/static');
        $path=Storage::path(APP_PUBLIC.'/static/'.self::shadowName($module_dir));
        if (self::hasChanged($static_path, $path)) {
            self::copyStatic($static_path, $path);
        }
        if (self::hasChanged($app_static_path, $path)) {
            self::copyStatic($app_static_path, $path);
        }
        return $path;
    }

    public static function shadowName(string $name)
    {
        return base64_encode(md5($name, true));
    }

    /**
     * 模块模板文件目录
     *
     * @param string $module
     * @return string
     */
    public static function getThemePath(string $module):string
    {
        $module_dir=Application::getModuleDir($module);
        $theme=MODULES_DIR.'/'.  $module_dir.'/resource/template/'.self::$theme;
        return Storage::path($theme);
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
        $theme=RESOURCE_DIR.'/template/'.self::$theme.'/'.  $module_name;
        return Storage::path($theme);
    }

    /**
     * 模板输入路径
     *
     * @param string $name
     * @return string
     */
    public static function getInputPath(string $name):string
    {
        return self::getInputFile($name.self::$extRaw);
    }

    /**
     * 模板编译后输出路径
     *
     * @param string $name
     * @return string
     */
    public static function getOutputPath(string $name):string
    {
        return self::getOutputFile($name.self::$extCpl);
    }

   
    /**
     * 检测模板是否被修改
     *
     * @param string $static
     * @param string $tpl
     * @return boolean
     */
    protected static function hasChanged(string $static, string $tpl)
    {
        if (conf('debug', false)) {
            return true;
        } else {
            // 模板内容比静态文件夹内容新
            if (filemtime($tpl)>filemtime($static)) {
                return true;
            }
        }
        return false;
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
            _D()->trace('copy '.$static_path.' => '.$path);
            Storage::copydir($static_path, $path, $non_static_preg);
        }
    }

    public static function file(string $name, $parent)
    {
        list($module, $basename)=Router::parseName($name);
        $input=self::getAppThemePath($module).'/'.$basename;
        if (!Storage::exist($input)) {
            $input=self::getThemePath($module).'/'.$basename;
        }
        $module_dir=Application::getModuleDir($module);
        // 获取输出
        $output=VIEWS_DIR.'/'. $module_dir .'/'.$basename;
        // 动态文件导出
        $outpath=APP_PUBLIC.'/dtf/'.self::shadowName($module_dir).'/'.$basename;
        $path=Storage::path(dirname($outpath));
        // 编译文件
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

    /**
     * 模板输入路径
     *
     * @param string $name
     * @return string
     */
    public static function getInputFile(string $name):string
    {
        list($module, $basename)=Router::parseName($name);
        $input=self::getAppThemePath($module).'/'.$basename;
        // _D()->info($input);
        if (Storage::exist($input)) {
            return $input;
        }
        return self::getThemePath($module).'/'.$basename;
    }

    /**
     * 模板编译后输出路径
     *
     * @param string $name
     * @return string
     */
    public static function getOutputFile(string $name):string
    {
        list($module, $basename)=Router::parseName($name);
        $module_dir=Application::getModuleDir($module);
        $output=VIEWS_DIR.'/'. $module_dir .'/'.$basename;
        return $output;
    }
}
