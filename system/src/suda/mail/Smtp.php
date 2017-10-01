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
 * @version    since 1.2.4
 */
namespace suda\mail;

use suda\template\Manager;
use suda\core\Response;

class Smtp extends Mailer
{
    private $host;
    private $port;
    private $auth;
    private $user;
    private $passwd;
    private $sock=null;
    private $timeout;
    private $ssl=true;

    public function __construct(string $host=null, int $port = 25, int $timeout=30, bool $auth = false, string $user, string $pass, string $name=null,bool $ssl=false)
    {
        $this->port = $port;
        $this->host = $host;
        $this->timeout = $timeout;
        $this->auth = $auth;
        $this->user = $user;
        $this->passwd = $pass;
        $this->from[0]=$user;
        $this->ssh=$ssl;
        if ($name) {
            $this->from[1]=$name;
        }
    }

    public function from(string $email, string $name='')
    {
        if ($name) {
            $this->from[1]=$name;
        }
        return $this;
    }

    protected function parseHeader()
    {
        $header=parent::parseHeader();
        $header.='Subject:'.self::encode($this->subject)."\r\n";
        return $header;
    }
    // 发送邮件
    public function send(array $value_map=[])
    {
        // 合并属性值
        $this->values=array_merge($this->values, $value_map);
        $to=self::parseTo();
        $message=self::renderBody();
        $header=self::parseHeader();
        $return=true;
        // 对每个邮箱
        foreach ($this->to as $email) {
            if (!$this->smtpSockopen($email)) {
                $this->setError(11, 'Cannot send email to '.$email);
                $return=false;
                continue;
            }
            // 发送
            if (!$this->smtpSend($this->host, $this->from[0], $email, $header, $message)) {
                $this->setError(11, 'Cannot send email to '.$email);
                $return=false;
            }

            fclose($this->sock);
        }
        return $return;
    }

    private function smtpSend($helo, $from, $to, $header, $body = "")
    {
        // 验证链接
        if (!$this->stmpCmd("HELO", $helo)) {
            $this->setError(2, 'sending HELO command error');
            return false;
        }
        if ($this->auth) {
            if (!$this->stmpCmd("AUTH LOGIN", base64_encode($this->user))) {
                $this->setError(3, 'AUTH LOGIN command error');
                return false;
            }
            if (!$this->stmpCmd(base64_encode($this->passwd))) {
                $this->setError(4, 'AUTH PASSWD command error');
                return false;
            }
        }
        if (!$this->stmpCmd("MAIL", "FROM:<".$from.">")) {
            $this->setError(5, 'sending MAIL FROM command error');
            return false;
        }
        if (!$this->stmpCmd("RCPT", "TO:<".$to.">")) {
            $this->setError(6, 'sending RCPT TO command error');
            return false;
        }
        if (!$this->stmpCmd("DATA")) {
            $this->setError(7, 'sending DATA command error');
            return false;
        }
        if (!$this->smtpMessage($header, $body)) {
            $this->setError(8, 'sending message error');
            return false;
        }
        if (!$this->smtpEom()) {
            $this->setError(9, 'sending <CR><LF>.<CR><LF> [EOM] error');
            return false;
        }
        if (!$this->stmpCmd("QUIT")) {
            $this->setError(10, 'sending QUIT command error');
            return false;
        }
        return true;
    }

    private function smtpSockopen(string $email)
    {
        if ($this->host) {
            $this->sock = self::fsockopen($this->host, $this->port, $this->timeout, $errno, $errstr);
            if (!($this->sock && $this->smtpCheck())) {
                $this->setError($errno, $errstr);
            }
            return true;
        } elseif ($sock=self::smtpSockopenMX($email)) {
            $this->sock =$sock;
            return true;
        }
        return  false;
    }

    private function smtpSockopenMX($email)
    {
        $domain = preg_replace('/^.+@([^@]+)$/', '\1', $email);
        try {
            getmxrr($domain, $MXhosts);
        } catch (\Exception $e) {
            $this->setError(13, 'cannot resolve MX '.$domain);
            return false;
        }
        
        
        foreach ($MXhosts as $host) {
            $this->sock = self::fsockopen($host, $this->port, $this->timeout, $errno, $errstr);
            if (!($this->sock && $this->smtpCheck())) {
                $this->log($errno.'>'.$host.':'.$errstr);
                continue;
            }
            $this->host=$host;
            return $this->sock;
        }
        return false;
    }

    private function smtpMessage($header, $body)
    {
        fputs($this->sock, $header."\r\n".$body);
        return true;
    }


    private function smtpEom()
    {
        fputs($this->sock, "\r\n.\r\n");
        return $this->smtpCheck();
    }

    // 发送命令到服务器
    private function stmpCmd(string $cmd, string $arg=null)
    {
        if (!is_null($arg)) {
            $cmd = $cmd." ".$arg;
            $this->_log('send command ' .$cmd);
        }
        fputs($this->sock, $cmd."\r\n");
        return $this->smtpCheck();
    }

    // 检测命令
    private function smtpCheck()
    {
        $response = str_replace("\r\n", "", fgets($this->sock, 512));
        if (!preg_match("/^[23]/", $response)) {
            fputs($this->sock, "QUIT\r\n");
            fgets($this->sock, 512);
            $this->_log('remote host returned '.$response);
            return false;
        }
        return true;
    }

    private function fsockopen(string $host, int $port, int $timeout, & $errno, & $errstr)
    {
        try {
            if($this->ssl){
                $sock = fsockopen('ssl://'.$host, $port, $errno, $errstr, $timeout);
            }else{
                $sock = fsockopen($host, $port, $errno, $errstr, $timeout);
            }
            return $sock;
        } catch (\Exception $e) {
            $this->setError($e->getCode(), $e->getMessage());
            return false;
        }
    }
}
