<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2017 DXkite
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
        JSON_ERROR_NONE=>'No errors',
        JSON_ERROR_DEPTH=>'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH=>'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR=>'Unexpected control character found',
        JSON_ERROR_SYNTAX=>'Syntax error, malformed JSON',
        JSON_ERROR_UTF8=>'Malformed UTF-8 characters, possibly incorrectly encoded',
     ];

    public function __construct(int $error)
    {
        parent::__construct(self::$error[$error] ?? 'Unknown error');
    }
}
