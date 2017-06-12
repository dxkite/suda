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

/**
 * 画类图还不熟练啊，，，，
 *
 */
interface Compiler
{
    /**
     * @param string $text
     */
    public function compileText(string $text);
    public function compile(string $name,string $input,string $output);
    public function render(string $name,string $viewfile);
}
