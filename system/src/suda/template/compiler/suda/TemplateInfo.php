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
 * @version    since 1.2.10
 */

namespace suda\template\compiler\suda;

use suda\template\Manager;
use suda\template\iterator\RecursiveTemplateIterator;

/**
 * 获取模板信息类
 * @var 获取模板的包含库
 * @var 获取模板需要的值
 */
class TemplateInfo extends Compiler
{
    protected $values=[];
    protected $includes=[];
    protected $includes_info=[];
    protected $name;
    protected $module=null;
    protected $path=null;
    protected static $templates;

    public function __construct(string $name, string $parent=null)
    {
        list($module_name, $basename)=router()->parseName($name, $parent);
        $this->name=$module_name.':'.$basename;
        $this->module=$module_name;
        if ($path=Manager::getInputFile($this->name)) {
            $this->path=$path;
            $this->compileText(file_get_contents($path));
        }
    }

    protected function echoValueCallback($matchs)
    {
        $name=$matchs[1];
        $args=isset($matchs[4])?','.$matchs[4]:'';
        $this->values[$name]=$matchs[4]??null;
    }

    // include
    protected function parseInclude($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        ($tpl=new self($name, $this->module));
        $this->includes[]=$tpl->name;
        $this->includes_info[$name]=$tpl;
        $this->values=array_merge($this->values, $tpl->values);
        $this->includes=array_merge($this->includes, $tpl->includes);
    }

    // extend
    protected function parseExtend($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        ($tpl=new self($name, $this->module));
        $this->includes[]=$tpl->name;
        $this->includes_info[$name]=$tpl;
        $this->values=array_merge($this->values, $tpl->values);
        $this->includes=array_merge($this->includes, $tpl->includes);
    }

    public function getValuesName()
    {
        return array_keys($this->values);
    }

    public static function getTemplates(string $module=null, ?string $extension= null)
    {
        $modules=empty($module) || is_null($module) ? app()->getLiveModules():[$module];
        foreach ($modules as $module) {
            if (!app()->checkModuleExist($module)) {
                continue;
            }
            $sources=Manager::getTemplateSource($module);
            // 覆盖顺序：栈顶优先级高，覆盖栈底元素
            while ($path=array_pop($sources)) {
                self::getModuleTemplate($module, $extension, $path);
            }
        }
        return self::$templates;
    }

    protected static function getModuleTemplate(string $module, ?string $extension=null, string $root)
    {
        $it = new RecursiveTemplateIterator($root, $extension);
        foreach ($it as $name => $path) {
            self::$templates[$module][$name]=$path;
        }
    }
}
