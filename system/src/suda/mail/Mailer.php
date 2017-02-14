<?php
namespace suda\mail;
interface Mailer
{
    public function to(string $email, string $name='');
    public function subject(string $subject);
    public function from(string $email, string $name='');
    public function message(string $msg);
    public function use(string $tpl);
    public function assign(string $name, $value);
    // 发送邮件
    public function send(array $value_map=[]);
    public function errno();
    public function error();
}
