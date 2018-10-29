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
 * @version    since 1.2.13
 */

namespace docme;

use suda\template\Manager;
use suda\core\Storage;

class ExportTemplate
{
    protected $value;
    protected $template;
    protected $source;
    protected $name;

    public function setSrc(string $src)
    {
        $this->source=$src;
        $this->template=$this->source.'.tpl.php';
        $this->name=md5($src);
    }

    public function setValues(array $values)
    {
        $this->value=$values;
    }

    public function export(string $dest)
    {
        $this->compile($this->name, $this->source, $this->template);
        $tpl=$this->template($this->name,$this->template);
        $content= $tpl->assign($this->value)->getRenderedString();
        Storage::path(dirname($dest));
        return Storage::put($dest, $content);
    }

    public function template(string $name, string $viewfile)
    {
        $classname='Class'.md5($name);
        require_once $viewfile;
        return $template=new $classname;
    }

    protected function compile(string $name, string $input, string $output)
    {
        $compiler=Manager::getCompiler();
        $content= $compiler->compileText(Storage::get($input));
        $classname='Class'.md5($name);
        $content='<?php  class '.$classname.' extends '. $compiler::Template.' { protected $name="'.$name.'";protected $module=""; protected function _render_template() {  ?>'.$content.'<?php }}';
        Storage::put($output, $content);
        return true;
    }
}
