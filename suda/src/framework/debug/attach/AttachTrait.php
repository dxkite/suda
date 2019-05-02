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

    protected function analyse(array $context)
    {
        $replace = [];
        $attach = [] ;
        foreach ($context as $key => $val) {
            if ($this->isReplacedObj($val)) {
                $replace['{' . $key . '}'] = $val;
            } else {
                $attach[$key] = $val;
            }
        }
        return [$attach, $replace];
    }

    protected function isReplacedObj($val) : bool
    {
        return !is_array($val) && (!is_object($val) || method_exists($val, '__toString')) && ! $val instanceof Throwable;
    }

    public function interpolate(string $message, array $context, array $attribute)
    {
        list($attach, $replace) = $this->analyse($context);
        $attribute = array_merge($this->attribute, $attribute);
        foreach ($attribute as $key => $val) {
            $replace['%' . $key . '%'] = $val;
        }
        $message = strtr($message, $replace);
        $attachInfo = '';
        foreach ($attach as $name => $value) {
            $attachInfo = $name.' = ';
            if ($value instanceof AttachValueInterface) {
                $attachInfo .= $value->getLogAttach().PHP_EOL;
            } else {
                $attachInfo .= DumpTrait::parameterToString($value).PHP_EOL;
            }
        }
        if (strlen($attachInfo) > 0) {
            return $message.PHP_EOL.$attachInfo;
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
