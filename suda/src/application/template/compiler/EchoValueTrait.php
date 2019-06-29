<?php
namespace suda\application\template\compiler;

use Exception;
use http\Exception\RuntimeException;

trait EchoValueTrait
{
    /**
     * @param $var
     * @return string
     */
    public function parseEchoValue($var):string
    {
        // 任意变量名: 中文点下划线英文数字
        $code = preg_replace_callback(
            '/\B[$](\?)?[:]([.\w\x{4e00}-\x{9aff}]+)(\s*)(\( ( (?>[^()]+) | (?4) )* \) )?/ux',
            [$this,'echoValueCallback'],
            $var
        );
        $error = preg_last_error();
        if ($error !== PREG_NO_ERROR) {
            throw new RuntimeException($error);
        }
        return $code;
    }
    
    protected function echoValueCallback($matchs)
    {
        $name=$matchs[2];
        if ($matchs[1]==='?') {
            return '$this->has("'.$name.'")';
        }
        if (isset($matchs[4])) {
            if (preg_match('/\((.+)\)/', $matchs[4], $v)) {
                $args = trim($v[1]);
                $args= strlen($args) ?','.$args:'';
                return '$this->get("'.$name.'"'.$args.')';
            }
        }
        return '$this->get("'.$name.'")';
    }
}
