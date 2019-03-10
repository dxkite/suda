<?php
namespace suda\application\template\compiler;

interface EchoValueInterface
{
    public function parseEchoValue($var):string;
}
