<?php

namespace suda\template;


interface Template
{
    public function render();
    public function echo();
    public function response(\suda\core\Response $response);
    public function get(string $name, $default=null);
    public function set(string $name, $value);
    public function assign(array $values);
    public function getRenderedString();
}
