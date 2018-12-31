<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\template;

use Iterator;
use suda\core\Hook;
use suda\core\Config;
use suda\core\Router;
use suda\core\Request;
use suda\core\Storage;
use suda\core\Autoloader;
use suda\core\Application;
use suda\template\Template;
use suda\exception\KernelException;
use suda\template\iterator\RecursiveTemplateIterator;

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
    public static $extCpl='.tpl.php';
    /**
     * 默认样式
     *
     * @var string
     */
    protected static $theme='default';
    /**
     * 模板编译器
     * @var Compiler|null
     */
    protected static $compiler=null;
    private static $staticPath='static';
    private static $dynamicPath='';
    /**
     * 外部资源目录
     *
     * @var string
     */
    private static $assetsPath= APP_PUBLIC.'/assets';
    protected static $baseUrl=null;

    /**
     * 模板搜索目录
     *
     * @var array
     */
    protected static $templateSource=[];

    /**
     * 编译器懒初始化状态
     *
     * @var boolean
     */
    private static $init = false;
    
    /**
     * 获取模板编译器
     */
    public static function getCompiler():Compiler
    {
        if (is_null(self::$compiler)) {
            Hook::exec('suda:template:load-compile::before');
            $class=class_name(conf('app.compiler', 'suda.template.compiler.suda.Compiler'));
            $instance=new $class;
            // 初始化编译器
            if ($instance instanceof Compiler) {
                self::$compiler = $instance;
            } else {
                throw new KernelException(__('app template compiler must be instance of suda\template\Compier'));
            }
            Hook::listen('suda:route:dispatch::extra', [__CLASS__,'assetsResponse']);
        }
        return self::$compiler;
    }

    /**
     * 获取/设置模板样式
     *
     * @param string $theme
     * @return string
     */
    public static function theme(string $theme=null):string
    {
        if (!is_null($theme)) {
            self::$theme = $theme;
            self::$init = false;
            Hook::exec('suda:template:change-theme', [$theme]);
            debug()->info('change themes:'.$theme);
        }
        return self::$theme;
    }

    /**
     * 编译文件
     *
     * @param string $name
     * @param string $ext
     * @param string $outpath
     * @return boolean
     */
    public static function compile(string $name, string $ext='html', string $outpath=null):bool
    {
        // 初始化一次
        if (!self::$init) {
            Hook::exec('suda:template:compile::init', [ self::getCompiler() ]);
            self::$init = true;
        }
        // 编译文件
        list($module, $basename) = Router::parseName($name);
        if ($data = self::getInputFile($module, $basename, true, $ext)) {
            list($root, $path) = $data;
            if (is_null($outpath)) {
                $outpath=self::getOutputFile($module, $basename);
            }
            Hook::exec('suda:template:compile::before', [ self::getCompiler() ]);
            self::getCompiler()->compile($module, $root, $basename, $path, $outpath);
            return true;
        }
        return false;
    }

    /**
     * 根据模板ID显示HTML模板
     *
     * @param string $name
     * @param string $viewpath
     * @return Template
     */
    public static function display(string $name, ?string $viewpath=null)
    {
        $template = self::displaySource($name, 'html', $viewpath??'');
        if (is_null($template)) {
            throw new KernelException(__('missing template $0 $1', self::$theme, $name));
        }
        return $template;
    }

    /**
     * 根据名称显示源
     *
     * @param string $name 模板名
     * @param string $ext 扩展名
     * @param string|null $viewpath 缓存模板路径
     * @return Template|null 模板元素
     */
    public static function displaySource(string $name, string $ext='html', ?string $viewpath=null)
    {
        if (empty($viewpath)) {
            list($module, $basename) = Router::parseName($name);
            $viewpath=self::getOutputFile($module, $basename);
        }
        if (Storage::exist($viewpath)) {
            if (Config::get('debug', true) || Config::get('exception', false)) {
                if (!self::compile($name, $ext)) {
                    return null;
                }
            }
        } else {
            $pathOutput=dirname($viewpath);
            storage()->mkdirs($pathOutput);
            if (!storage()->isWritable($pathOutput)) {
                // NOTICE: 这里可能会创建临时文件失败
                $viewpath=storage()->temp('tpl_');
            }
            if (!self::compile($name, $ext, $viewpath)) {
                return null;
            }
        }
        return self::displayFile($viewpath, $name);
    }

    /**
     * 根据路径显示模板
     *
     * @param string $file
     * @param string $name
     * @return Template
     */
    public static function displayFile(string $file, string $name = null)
    {
        return self::getCompiler()->render($file, $name??$file);
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
        Hook::exec('suda:template:resource-prepare::before', [$module]);
        // 非Debug不更新资源
        if (!conf('debug', false) && !$force) {
            return false;
        }
        // 向下兼容
        defined('APP_PUBLIC') or define('APP_PUBLIC', Storage::abspath('.'));
        $publicPath=self::getPublicModulePath($module);
        $sources=self::getTemplateSource($module);
        $return=false;
        if (!storage()->isDir($publicPath)) {
            storage()->mkdirs($publicPath);
        }
        if (storage()->isWritable($publicPath)) {
            foreach ($sources as $source) {
                if ($path=Storage::abspath($source.'/static')) {
                    self::copyStatic($path, $publicPath);
                    $return=true;
                }
            }
        } else {
            \debug()->warning(__('path not writeable: $0', $publicPath));
        }
        self::touchIndex(self::$assetsPath);
        return $return;
    }

    private static function touchIndex(string $dest, string $content = 'dxkite-suda@'.SUDA_VERSION)
    {
        $dest=Autoloader::parsePath($dest);
        $dest=storage()->path($dest);
        if ($dest) {
            $index = $dest.'/'.conf('default-index', 'index.html');
            if (!storage()->exist($index)) {
                file_put_contents($index, $content);
            }
            $dirs = storage()->readDirs($dest, true);
            foreach ($dirs as $path) {
                $index = $path.'/'.conf('default-index', 'index.html');
                if (!storage()->exist($index)) {
                    file_put_contents($index, $content);
                }
            }
            return true;
        }
        return false;
    }

    private static function getPublicModulePath(string $module)
    {
        return self::$assetsPath.'/'.self::$staticPath.'/'.self::moduleUniqueId($module);
    }

    /**
     * 获取模块唯一标识符
     *
     * @param string $module
     * @return string|null
     */
    public static function moduleUniqueId(string $module):?string
    {
        $moduleConfig=Application::getInstance()->getModuleConfig($module);
        if (array_key_exists('unique', $moduleConfig)) {
            return $moduleConfig['unique'];
        }
        if (array_key_exists('directory', $moduleConfig)) {
            return substr(md5($moduleConfig['directory']), 0, 8);
        }
        return null;
    }

    /**
     * 模块模板文件目录
     *
     * @param string $module
     * @return string
     */
    public static function getThemePath(string $module):string
    {
        return Application::getInstance()->getModulePath($module).'/resource/template/:theme:';
    }

    /**
     * 模块模板文件目录
     *
     * @param string $module
     * @return string
     */
    public static function getAppThemePath(string $module):string
    {
        $dirname=Application::getInstance()->getModuleDir($module);
        return RESOURCE_DIR.'/template/:theme:/'.$dirname;
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
        $moduleName=Application::getInstance()->getModuleFullName($module);
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
     * @return array
     */
    public static function getTemplateSource(string $module)
    {
        $moduleName=Application::getInstance()->getModuleFullName($module);
        $sources=[];
        if (isset(self::$templateSource[$moduleName])) {
            foreach (self::$templateSource[$moduleName] as $source) {
                if ($path=Storage::abspath(preg_replace('/\:theme\:/', static::$theme, $source))) {
                    if (!in_array($path, $sources)) {
                        $sources[]=$path;
                    }
                }
                if ($path=Storage::abspath(preg_replace('/\:theme\:/', 'default', $source))) {
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
        if (conf('template.refresh-all', false)) {
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
     * @param mixed $parent
     * @return string
     */
    public static function file(string $name, $parent)
    {
        list($module, $basename)=Router::parseName($name, $parent->getModule());
        list($root, $input)=self::getInputFile($module, $basename, false);

        if (!Storage::exist($input)) {
            throw new KernelException(__('missing file $0:$1', self::$theme, $input));
        }
        // 获取输出
        $output=self::getOutputFile($module, $basename);
        // 动态文件导出
        $outpath=self::$assetsPath.'/'.self::$dynamicPath.'/'.self::moduleUniqueId($module).'/'.$basename;
        Storage::path(dirname($outpath));
        // 编译检查
        if (Storage::exist($output)) {
            if (Config::get('debug', true) || Config::get('exception', false)) {
                if (!self::getCompiler()->compile($module, $root, $name, $input, $output)) {
                    throw new KernelException(__('missing file $0:$1', self::$theme, $input));
                    return;
                }
            }
        } else {
            $pathOutput=dirname($output);
            storage()->mkdirs($pathOutput);
            if (!storage()->isWritable($pathOutput)) {
                $output=storage()->temp('tpl_');
            }
            if (!self::getCompiler()->compile($name, $input, $output)) {
                throw new KernelException(__('missing file $0:$1', self::$theme, $input));
                return;
            }
        }
        // 输出内容
        $public=self::getCompiler()->render($output, $name)->parent($parent)->getRenderedString();
        Storage::put($outpath, $public);
        // 引用文件
        $static_url=Storage::cut($outpath, self::$assetsPath);
        $static_url=preg_replace('/[\\\\\/]+/', '/', $static_url);
        return  self::assetServer('/'.trim($static_url, '/'));
    }

    public static function include(string $name, $parent)
    {
        list($moduleName, $basename)=Router::parseName($name, $parent->getModule());
        if ($include=self::display($moduleName.':'.$basename)) {
            return $include->parent($parent)->assign($parent->getValue());
        } else {
            $class= new class implements Template {
                public $name;
                public $moduleName;
                public $basename;
                public function render()
                {
                    $this->echo();
                }
                public function echo()
                {
                    echo '<div style="color:red" title="'.__('can\'t include $0', $this->moduleName.':'.$this->basename).'">{include:{'.$this->name.'}}</div>';
                }
                public function response(\suda\core\Response $response)
                {
                }
                public function get(string $name, $default=null)
                {
                }
                public function set(string $name, $value)
                {
                }
                public function assign(array $values)
                {
                }
                public function getRenderedString()
                {
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
     * @param boolean $ext
     * @param string $extRaw
     * @return array 返回文件根目录和路径 array<root,input>
     */
    public static function getInputFile(string $module, string $basename, bool $ext=true, string $extRaw='html'):?array
    {
        $source=self::getTemplateSource($module);
        foreach ($source as $path) {
            $input=$path.'/'.trim($basename, '/').($ext?self::$extRaw.$extRaw:'');
            if (Storage::exist($input)) {
                return [$path, $input];
            }
        }
        return null;
    }

    /**
     * 模板编译后输出路径
     *
     * @param string $name
     * @return string
     */
    public static function getOutputFile(string $module, string $basename):string
    {
        $module_dir=Application::getInstance()->getModuleDir($module);
        $output=VIEWS_DIR.'/'. $module_dir .'/'.$basename.self::$extCpl;
        return $output;
    }

    public static function className(string $module, string $name)
    {
        $fullModuleName=Application::getInstance()->getModuleFullName($module);
        return 'Template_'.md5($fullModuleName.':'.$name);
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
                if ($success !== true) {
                    $init[$module]['template'][$name]=false;
                } else {
                    $init[$module]['template'][$name]=true;
                }
            }
        }
        debug()->timeEnd('init resource');
        return $init;
    }

    public static function findModuleTemplates(string $module):Iterator
    {
        if (!app()->checkModuleExist($module)) {
            return false;
        }
        if ($sources=Manager::getTemplateSource($module)) {
            foreach ($sources as $source) {
                $it = new RecursiveTemplateIterator($source);
                foreach ($it as $name => $path) {
                    yield $name;
                }
            }
        }
    }

    public static function getStaticAssetPath(string $module=null)
    {
        $module=$module??Application::getInstance()->getActiveModule();
        $path=Manager::getPublicModulePath($module);
        self::prepareResource($module);
        $staticUrl=Storage::cut($path, self::$assetsPath);
        $staticUrl=str_replace('\\', '/', $staticUrl);
        return  '/'.$staticUrl;
    }

    public static function getDynamicAssetPath(string $path, string $module=null)
    {
        $module=$module??Application::getInstance()->getActiveModule();
        $path=self::$assetsPath.'/'.self::$dynamicPath.'/'.self::moduleUniqueId($module).'/'.$path;
        $staticUrl=Storage::cut($path, self::$assetsPath);
        $staticUrl=str_replace('\\', '/', $staticUrl);
        return  '/'.$staticUrl;
    }

    public static function assetServer(string $url)
    {
        if (is_null(self::$baseUrl)) {
            if (is_dir(self::$assetsPath.DIRECTORY_SEPARATOR.self::$staticPath)) {
                $base    = Request::hostBase();
                $script  = $_SERVER['SCRIPT_NAME'];
                self::$baseUrl = $base.rtrim(str_replace('\\', '/', dirname($script)), '/').'/assets';
            } else {
                self::$baseUrl = rtrim(Request::baseUrl(), '/').'/assets';
            }
        }
        return conf('asset-server', self::$baseUrl).$url;
    }

    /**
     * 检查语法
     *
     * @param string $file
     * @return bool|\ParseError 语法错误报错
     */
    public static function checkSyntax(string $file, string $className='')
    {
        if (storage()->exist($file)) {
            if ($className && class_exists($className, false)) {
                return true;
            }
            $fileContent=storage()->get($file);
            if (conf('template.check-syntax', 'eval') == 'eval') {
                try {
                    eval('return true; ?>'.$fileContent);
                } catch (\ParseError $e) {
                    return $e;
                }
            } else {
                $fileTemp=RUNTIME_DIR.'/check-syntax-'.md5($fileContent);
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

    /**
     * 动态加载静态资源
     *
     * @param Request $request
     * @return bool
     */
    protected static function assetsResponse(Request $request)
    {
        $url=$request->url();
        if (preg_match('/^\/assets\/([^\/]+)\/(.+)$/', $url, $match)) {
            list($url, $name, $path) =$match;
            $assets=new class extends \suda\core\Response {
                public $assetName;
                public $assetPath;
                public function onRequest(Request $request)
                {
                    $name=$this->assetName;
                    $path=$this->assetPath;
                    $moduleHash = '';
                    if ($name == 'static') {
                        preg_match('/^([^\/]+)\/(.+)$/', $path, $match);
                        if (count($match) ==3) {
                            $moduleHash=$match[1];
                            $path='static/'.$match[2];
                        }
                    } else {
                        $moduleHash=$name;
                    }
                    $modules= app()->getLiveModules();
                    $module=null;
                    foreach ($modules as $temp) {
                        if (Manager::moduleUniqueId($temp) === $moduleHash) {
                            $module=$temp;
                            break;
                        }
                    }
                    if ($module) {
                        $res=Manager::getTemplateSource($module);
                        foreach ($res as $templateRoot) {
                            $assetPath=$templateRoot.'/'.$path;
                            if ($assetPath=storage()->abspath($assetPath)) {
                                if (preg_match('/^'.preg_quote($templateRoot, '/').'/', $assetPath)) {
                                    $this->getFile($assetPath);
                                    return;
                                }
                            }
                        }
                    }
                    $this->state(404);
                    if ($module) {
                        echo 'assets not find in '.$module.', path '.$path;
                    } else {
                        echo 'assets not find with path '.$path;
                    }
                }
                
                public function getFile(string $path)
                {
                    $content=file_get_contents($path);
                    $hash   = md5($content);
                    $size   = strlen($content);
                    if (!$this->_etag($hash)) {
                        $type   = pathinfo($path, PATHINFO_EXTENSION);
                        $this->type($type);
                        self::setHeader('Content-Length:'.$size);
                        echo $content;
                    }
                }
            };
            $assets->assetName=$name;
            $assets->assetPath=$path;
            $assets->onRequest($request);
            return true;
        }
        return false;
    }
}
