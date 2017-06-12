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
 * @version    1.2.4
 */
namespace suda\template;

use suda\tool\EchoValue;
use suda\core\{Config,Application,Storage,Hook,Router};

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
                echo '<b>compile error: '.$name.': '.$viewpath. 'missing raw template </b>';
                return;
            }
        } elseif (!Storage::exist($viewpath)) {
            if (!self::compile($name)) {
                echo '<b>missing '.$name.': '.$viewpath. '</b>';
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
        return self::$compiler->render($name,$file);
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
        $path=Storage::path(APP_PUBLIC.'/static/'. $module_dir);
        if (self::hasChanged($static_path, $path)) {
            self::copyStatic($static_path, $path);
        }
        return $path;
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
        return $theme;
    }

    /**
     * 模板输入路径
     *
     * @param string $name
     * @return string
     */
    public static function getInputPath(string $name):string
    {
        list($module, $basename)=Router::parseName($name);
        $input=self::getThemePath($module).'/'.$basename.self::$extRaw;
        return $input;
    }

    /**
     * 模板编译后输出路径
     *
     * @param string $name
     * @return string
     */
    public static function getOutputPath(string $name):string
    {
        list($module, $basename)=Router::parseName($name);
        $module_dir=Application::getModuleDir($module);
        $output=VIEWS_DIR.'/'. $module_dir .'/'.$basename.self::$extCpl;
        return $output;
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
            Storage::copydir($static_path, $path, $non_static_preg);
        }
    }
}
