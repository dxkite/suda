<?php

namespace suda\framework\debug\attach;

use Throwable;

/**
 * 多行附加属性
 */
trait AttachTrait
{
    /**
     * 属性数组
     *
     * @var array
     */
    protected $attribute = [];

    public function addAttribute(string $name, $value)
    {
        $this->attribute[$name] = $value;
    }

    protected function isReplacedObj($val): bool
    {
        return !is_array($val) && (!is_object($val)
                || method_exists($val, '__toString')) && (!$val instanceof Throwable);
    }

    /**
     * @param string $message
     * @param array $context
     * @param array $attribute
     * @return string
     */
    public function interpolate(string $message, array $context, array $attribute)
    {
        $replace = [];
        $attribute = array_merge($this->attribute, $attribute);
        foreach ($attribute as $key => $val) {
            $replace['%' . $key . '%'] = $val;
        }
        $message = strtr($message, $replace);
        $attachInfo = '';
        foreach ($context as $name => $value) {
            $attachInfo .= $name . ' = ';
            if ($value instanceof AttachValueInterface) {
                $attachInfo .= $value->getLogAttach() . PHP_EOL;
            } else {
                $attachInfo .= DumpTrait::parameterToString($value) . PHP_EOL;
            }
        }
        if (strlen($attachInfo) > 0) {
            return $message . PHP_EOL . $attachInfo;
        }
        return $message;
    }

    /**
     * Get 属性数组
     *
     * @return  array
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
