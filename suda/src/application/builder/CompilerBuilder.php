<?php
namespace suda\application\builder;

use suda\application\template\compiler\Compiler;

/**
 * 应用程序
 */
class CompilerBuilder
{
    /**
     * 构建编译器
     *
     * @return \suda\application\template\compiler\Compiler
     */
    public static function build():Compiler
    {
        return new Compiler;
    }
    
}
