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

namespace suda\mail;

use suda\mail\sender\Sender;
use suda\mail\sender\StmpSender;
use suda\mail\sender\MailSender;

class Factory
{
    const SENDMAIL=0;
    const SMTP=1;
    public static function sender(int $sender=self::SMTP):Sender
    {
        switch ($sender) {
            case self::SMTP:
                return new StmpSender(conf('smtp.server'), conf('smtp.port', 465), conf('smtp.timeout', 500), conf('smtp.email'), conf('smtp.passwd'), conf('smtp.ssl', false));
            case self::SENDMAIL:
                return new MailSender;
        }
    }
}
