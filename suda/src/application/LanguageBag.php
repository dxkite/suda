<?php
namespace suda\application;

/**
 * I18N支持
 */
class LanguageBag
{
    /**
     * 语言内容
     *
     * @var array
     */
    private $locales=[];

    /**
     * 包含本地化语言数组
     * @param array $locales
     */
    public function assign(array $locales)
    {
        $this->locales = array_merge($this->locales, $locales);
    }

    /**
     * 翻译语言
     *
     * @param string|null $message
     * @param mixed ...$args
     * @return string
     */
    public  function interpolate(?string $message, ...$args)
    {
        $message = trim($message);
        if (array_key_exists($message, $this->locales)) {
            $message = $this->locales[$message];
        }
        if (count($args) > 0 && is_array($args[0])) {
            $args = $args[0];
        }
        return static::format($message, $args);
    }

    /**
     * 格式化输出
     *
     * @param string $string
     * @param array $param
     * @return string
     */
    public static function format(string $string, array $param)
    {
        return preg_replace_callback('/(?<!\$)\$(\{)?(\d+|\w+?\b)(?(1)\})/', function ($match) use ($param) {
            $key = $match[2];
            if (array_key_exists($key, $param)) {
                return strval($param[$key]);
            }
            return $match[0];
        }, $string);
    }
}
