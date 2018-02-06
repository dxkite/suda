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

/**
 * 编译器接口
 */
interface Compiler
{
    /**
     * 编译文本
     * @param string $text
     */
    public function compileText(string $text);
    public function compile(string $name,string $input,string $output);
    public function render(string $name,string $viewfile);
}
