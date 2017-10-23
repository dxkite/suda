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
 * @version    since 1.2.10
 */

namespace suda\mail\sender;

use suda\mail\message\Message;

class MailSender implements Sender
{
    protected $error;
    protected $message;

    public function send(Message $message):bool
    {
        $this->message=$message;
        set_error_handler(array($this, 'errorHander'));
        $return=mail('','=?UTF-8?B?'.base64_encode($message->getSubject()).'?=', $message->getMessage(), $message->getHeader());
        restore_error_handler();
        return $return;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function errorHander(int $errno, string $errstr, string $errfile, int $errline, array $errcontext)
    {
        $this->errno=$errno;
        $this->errstr=$errstr;
        self::setError($errno.':'.$errstr);
    }

    protected function setError(string $error)
    {
        $this->error=$error;
        debug()->error($error);
    }

    protected function log(string $message)
    {
        debug()->debug($message);
    }
}
