<?php
namespace suda\framework\debug\attach;

/**
 * 多行附加属性
 */
interface AttachInterface
{
    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public function addAttribute(string $name, $value);

    /**
     * @param string $message
     * @param array $context
     * @param array $attribute
     * @return mixed
     */
    public function interpolate(string $message, array $context, array $attribute);
}
