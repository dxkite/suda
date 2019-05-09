<?php
namespace suda\framework\arrayobject;

use function is_array;

/**
 * 递归数组点获取类
 */
class RecursiveArrayDocAccess extends ArrayDotAccess
{
    public function offsetGet($offset)
    {
        $value = parent::offsetGet($offset);
        if (is_array($value)) {
            return new self($value);
        }
        return $value;
    }
}
