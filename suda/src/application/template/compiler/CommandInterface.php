<?php
namespace suda\application\template\compiler;

/**
 * Interface CommandInterface
 * @package suda\application\template\compiler
 */
interface CommandInterface
{
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name):bool;

    /**
     * @param string $name
     * @param string $content
     * @return string
     */
    public function parse(string $name, string $content):string;

    /**
     * @param array $config
     * @return mixed
     */
    public function setConfig(array $config);
}
