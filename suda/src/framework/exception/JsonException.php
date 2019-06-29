<?php
namespace suda\framework\exception;

use RuntimeException;
use Throwable;

/**
 * json置类
 */
class JsonException extends RuntimeException
{
    public static $error=[
        JSON_ERROR_NONE=>'no errors',
        JSON_ERROR_DEPTH=>'maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH=>'underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR=>'unexpected control character found',
        JSON_ERROR_SYNTAX=>'syntax error, malformed JSON',
        JSON_ERROR_UTF8=>'malformed UTF-8 characters, possibly incorrectly encoded',
    ];

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (array_key_exists($code, static::$error)) {
            $message = static::$error[$code].' : '.$message;
        }
        parent::__construct($message, $code, $previous);
    }
}
