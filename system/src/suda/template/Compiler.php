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
    public function compileFile(string $name,string $input,string $output);
}
