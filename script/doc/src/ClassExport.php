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

namespace doc;

class ClassExport
{
    public function __construct(string $class)
    {
    }
    public function export(string $path)
    {
    }
    
    public static function getUserDefinedClasses()
    {
        $classes=get_declared_classes();
        $userClasses=[];
        foreach ($classes as $class) {
            if ((new \ReflectionClass($class))->isUserDefined()) {
                if (preg_match('/^class@anonymous/', $class)) {
                    continue;
                }
                $userClasses[]=$class;
            }
        }
        return $userClasses;
    }
}
