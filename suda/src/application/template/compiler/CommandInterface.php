<?php
namespace suda\application\template\compiler;

interface CommandInterface
{
    public function has(string $name):bool;
    public function parse(string $name, string $content):string;
    public function setConfig(array $config);
}
