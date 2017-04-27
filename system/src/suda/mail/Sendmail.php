<?php
namespace suda\mail;

use suda\template\Manager;
use suda\core\Response;

class Sendmail extends Mailer
{
    // 发送邮件
    public function send(array $value_map=[])
    {
        // 合并属性值
        $this->values=array_merge($this->values, $value_map);
        $to=self::parseTo();
        $message=self::renderBody();
        $header=self::parseHeader();
        set_error_handler(array($this, 'errorHander'));
        $return=mail($to, $this->subject, $message, $header);
        restore_error_handler();
        return $return;
    }
}
