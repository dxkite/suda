<?php

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
