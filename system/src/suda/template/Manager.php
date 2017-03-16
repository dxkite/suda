<?php
namespace suda\template;

use Storage;
use suda\tool\Value;
use suda\core\Config;

class Manager
{
    /**
     * 模板编译器
     * @var null
     */
    private static $compiler=null;
    public static $lang='zh-CN';
    // 模板目录
    private static $path=[];
    // 样式
    protected static $theme='default';
    protected static $hooks=[];
    public static $extRaw='.tpl.html';
    public static $extCpl='.tpl';
    private static $values=[];
    protected static $errorInfo=[
        0=>'No Error',
        1=>'File %s Not Exist',
        2=>'Compile Include %s Error',
    ];
    protected static $error='';
    protected static $erron=0;
    protected static $current=null;

    /**
     * 载入模板编译器
     */
    public static function loadCompile()
    {
        if (is_null(self::$compiler)) {
            self::$compiler=new Compiler;
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
     * 获取编译后的模板路径
     * @param string $name
     * @return string
     */
    protected static function path(string $name, bool $ext=true):string
    {
        list($module, $name)=preg_split('/[:]/', $name, 2);
        $path=MODULES_DIR.'/'.$module.'/resource/template/'.self::$theme;
        if ($ext) {
            $tpl=$path.'/'.$name.self::$extRaw;
        } else {
            $tpl=$path.'/'.$name;
        }
        if (Storage::exist($tpl)) {
            self::$current=$path;
            return $tpl;
        }
        return false;
    }

    /**
     * 编译文件
     * @param $input
     * @return mixed
     */
    public static function compile(string $name)
    {
        list($module, $basename)=preg_split('/[:]/', $name, 2);
        $prefix=MODULES_DIR.'/'.$module.'/resource/template/'.self::$theme;
        $output=VIEWS_DIR.'/'.$module.'/'.$basename.self::$extCpl;

        $input=$prefix.'/'.$basename.self::$extRaw;
        
        if (!Storage::exist($input)) {
            return false;
        }
        $content= self::$compiler->compileText(Storage::get($input));
        if (!Storage::isDir($dir=dirname($output))) {
            Storage::mkdirs(dirname($output));
        }
        Storage::put($output, $content);
        return true;
    }

    public static function hook(string $name, $callback)
    {
        self::$hooks[$name][]=new  \base\Command($callback);
    }

    public static function exec(string $name)
    {
        if (isset(self::$hooks[$name])) {
            foreach (self::$hooks[$name] as $hook) {
                $hook->exec();
            }
        }
    }
    public static function display(string $name, array $values=[])
    {
        self::_display($name, VIEWS_DIR.'/'.preg_replace('/[:\\\\\/]/', DIRECTORY_SEPARATOR, $name).self::$extCpl, $values);
    }

    public static function include(string $tplname, array $values)
    {
        Manager::display($tplname, $values);
    }
    
    /**
    *  $name 模板名称
    *  $path 编译后路径
    */
    protected static function _display(string $name, string $viewpath, array $values)
    {
        if (Config::get('debug', true)) {
            if (!self::compile($name)) {
                echo '<b>compile error: '.$name.':missing raw template </b>';
                return;
            }
        } elseif (!Storage::exist($viewpath)) {
            if (!self::compile($name)) {
                echo '<b>missing '.$name.'</b>';
                return;
            }
        }
        
        self::displayFile($viewpath, $values);
    }

    public static function displayFile(string $file, array $values=[])
    {
        $v=new Value($values);
        require $file;
    }

    public static function prepareResource()
    {
        // 向下兼容
        defined('APP_PUBLIC') or define('APP_PUBLIC', '.');
        $static_path=MODULES_DIR.'/'.\suda\core\Application::getActiveModule().'/resource/template/'.self::$theme.'/static';
        $path=Storage::path(APP_PUBLIC.'/static/');
        defined('APP_STATIC') or define('APP_STATIC',$path);
        if (self::hasChanged($static_path,$path)) {
            self::copyStatic($static_path,$path);
        }
    }

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
    protected static function copyStatic(string $static_path ,string $path)
    {
        Storage::rmdirs($path);
        // 复制静态资源
        $non_static=trim(str_replace(',', '|', Config::get('non-static', 'php')), '|');
        $non_static_preg='/(?<!(\.tpl\.html)|(\.('.$non_static.')))$/';
        if (Storage::isDir($static_path)){
            Storage::copydir($static_path, $path, $non_static_preg);
        }
    }
}
