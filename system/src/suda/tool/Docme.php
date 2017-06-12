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
namespace suda\tool;

/**
* Doc Me
* create a doc for class or function
* use markdown style to doc this.
*/
class Docme
{
    protected $me=null;
    protected $info=null;

    public function __construct($that)
    {
        $this->me=$that;
    }
    public function setTemplate(string $template)
    {
    }
    public function info()
    {
    }
    public function export(string $path=null)
    {
    }
    protected function parseDoc(string $docs)
    {
        return preg_replace('/^(\b+)?\/?\*\/?/m','',$docs);
    }

    protected function docClass($class)
    {
        $class=new ReflectionClass($class);
        $methods=[];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name=$method->getShortName();
            if (preg_match('/^action(.+?)$/', $name, $match)) {
                $name=lcfirst($match[1]??$name);
                $methods[$name]['doc']=$this->parseDoc($method->getDocComment());
                foreach ($method->getParameters() as $param) {
                    $methods[$name]['parameters'][$param->getName()]['pos']=$param->getPosition();
                    if ($param->hasType()) {
                        $methods[$name]['parameters'][$param->getName()]['type']=$param->getType()->__toString();
                    }
                    if ($param->isDefaultValueAvailable()) {
                        $methods[$name]['parameters'][$param->getName()]['default']=$param->getDefaultValue();
                    }
                }
            }
        }
        return ['doc'=>$this->parseDoc($class->getDocComment()),'methods'=>$methods];
    }
    public function __toString()
    {
        return $this->export();
    }
}
