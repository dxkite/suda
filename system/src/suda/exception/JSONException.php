<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 * 
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.9
 */

namespace suda\exception;

class JSONException extends \RuntimeException
{
    protected static $error=[
        JSON_ERROR_NONE=>'no errors',
        JSON_ERROR_DEPTH=>'maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH=>'underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR=>'unexpected control character found',
        JSON_ERROR_SYNTAX=>'syntax error, malformed JSON',
        JSON_ERROR_UTF8=>'malformed UTF-8 characters, possibly incorrectly encoded',
     ];

    public function __construct(int $error)
    {
        parent::__construct(__(self::$error[$error] ?? 'unknown error'));
    }
}
