<?php
namespace suda\mail;

use suda\mail\send\Sender;
use suda\mail\send\StmpSender;
use suda\mail\send\MailSender;

class Factory
{
    const SENDMAIL=0;
    const SMTP=1;
    public function getSender(int $sender=SMTP):Sender
    {
        switch ($sender) {
            case self::SMTP:
                return new StmpSender(conf('smtp.server'), conf('smtp.port', 356), conf('smtp.timeout', 500), conf('smtp.email'), conf('smtp.passwd'), conf('smtp.ssl', true));
            case self::SENDMIAL:
                return new MailSender;
        }
    }
}
