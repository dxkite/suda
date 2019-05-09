<?php
namespace suda\application\template\compiler;

/**
 * Interface EchoValueInterface
 * @package suda\application\template\compiler
 */
interface EchoValueInterface
{
    /**
     * @param $var
     * @return string
     */
    public function parseEchoValue($var):string;
}
