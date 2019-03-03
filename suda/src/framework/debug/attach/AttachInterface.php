<?php
namespace suda\framework\debug\attach;

/**
 * 多行附加属性
 */
interface AttachInterface 
{
    public function addAttribute(string $name, $value);
    public function interpolate(string $message, array $context, array $attribute);
}
