<?php

namespace suda\template;

interface Template
{
    public function render();
    public function echo();
    public function response(\suda\core\Response $response);
    public function get(string $name=null, $default=null);
    public function set(string $name, $value);
    public function has(string $name);
    public function assign(array $values);
    public function getRenderedString();
}
