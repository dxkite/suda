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

/**
 * SMTP邮件发送器
 *
 * @example
 * ```php
 * $sender=new StmpSender('smtp.163.com', 465, 500, 'dxkite@163.com', 'password', true);
 * $this->json($sender->send(
 *      (new Message('我的邮件', '测试发送邮件'))
 *      ->setFrom('dxkite@163.com')
 *      ->setTo('dxkite@qq.com')));
 * ```
 */
class StmpSender implements Sender
{
    protected $userName;
    protected $password;
    protected $server;
    protected $port;
    protected $isSecurity;
    protected $timeout;

    protected $socket;
    protected $error;
    protected $message;

    /**
     * 创建一个SMTP发送
     *
     * @param string $server SMTP邮件服务器
     * @param integer $port 端口号
     * @param integer $timeout 设置发送超时
     * @param string $name 邮箱用户名
     * @param string $password 邮箱密码
     * @param boolean $isSecurity 是否使用SSL，需要开启 OpenSSL 模块
     */
    public function __construct(string $server, int $port, int $timeout, string $name, string $password, bool $isSecurity=true)
    {
        $this->userName=$name;
        $this->password=$password;
        $this->isSecurity=$isSecurity;
        $this->server=$server;
        $this->port=$port;
        $this->timeout=$timeout;
    }


    /**
     * 发送信息
     *
     * @param Message $message 信息体
     * @return boolean
     */
    public function send(Message $message):bool
    {
        $this->message=$message;
        if ($this->message->getFrom() == null) {
            $this->message->setFrom($this->userName);
        }
        $commands=$this->getCommand();
        if ($this->isSecurity) {
            if ($this->openSocketSecurity()) {
                foreach ($commands as $command) {
                    $result =  $this->sendCommandSecurity($command[0], $command[1]);
                    if ($result) {
                        continue;
                    } else {
                        return false;
                    }
                }
                $this->closeSecutity();
            }
        } else {
            if ($this->openSocket()) {
                foreach ($commands as $command) {
                    $result =  $this->sendCommand($command[0], $command[1]);
                    if ($result) {
                        continue;
                    } else {
                        return false;
                    }
                }
                $this->close();
            }
        }
        return true;
    }

    protected function getCommand()
    {
        $command = [
            ["HELO sendmail\r\n", 250]
        ];
        if (!empty($this->userName)) {
            $command[] = ["AUTH LOGIN\r\n", 334];
            $command[] = [base64_encode($this->userName) . "\r\n", 334];
            $command[] = [base64_encode($this->password) . "\r\n", 235];
        }
        
        $command[] = ['MAIL FROM: <' . ($this->message->getFrom()[0]??$this->userName) . ">\r\n", 250];
        if (!empty($emails=$this->message->getTo())) {
            foreach ($emails as $email) {
                $command[] = array("RCPT TO: <" . $email[0] . ">\r\n", 250);
            }
        }
        if (!empty($emails=$this->message->getCc())) {
            foreach ($emails as $email) {
                $command[] = array("RCPT TO: <" . $email[0] . ">\r\n", 250);
            }
        }
        if (!empty($emails=$this->message->getBcc())) {
            foreach ($emails as $email) {
                $command[] = array("RCPT TO: <" . $email[0] . ">\r\n", 250);
            }
        }
        $command[] = ["DATA\r\n", 354];
        $command[] = [$this->getData(), 250];
        $command[] = ["QUIT\r\n", 221];
        return $command;
    }

    protected function getData()
    {
        $data=$this->message->getHeader();
        $data.='Subject: =?UTF-8?B?'.base64_encode($this->message->getSubject()).'?='."\r\n";
        $data.=$this->message->getMessage();
        return $data;
    }

    protected function openSocket()
    {
        //创建socket资源
        $this->socket = fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            $this->setError($errstr);
            return false;
        }
        $str = fread($this->socket, 1024);
        if (!preg_match("/220+?/", $str)) {
            $this->setError($str);
            return false;
        }
        return true;
    }


    protected function openSocketSecurity()
    {
        $remoteAddr = 'tcp://' . $this->server . ':' . $this->port;
        $this->socket = stream_socket_client($remoteAddr, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            $this->setError($errstr);
            return false;
        }
        stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
        stream_set_blocking($this->socket, 1); //设置阻塞模式
        $str = fread($this->socket, 1024);
        if (!preg_match("/220+?/", $str)) {
            $this->setError($str);
            return false;
        }
        return true;
    }


    protected function close()
    {
        if (!is_null($this->socket) && is_object($this->socket)) {
            fclose($this->socket);
            return true;
        }
        return false;
    }

    protected function closeSecutity()
    {
        if (!is_null($this->socket) && is_object($this->socket)) {
            stream_socket_shutdown($this->socket, STREAM_SHUT_WR);
            return true;
        }
        return false;
    }

    protected function sendCommand(string $command, int $stateReturn=null)
    {
        debug()->debug('send '.trim($command));
        try {
            if (fwrite($this->socket, $command, strlen($command))) {
                if (is_null($stateReturn)) {
                    return true;
                }
                $data = trim(fread($this->socket, 1024));
                if ($data) {
                    if (preg_match('/^'.$stateReturn.'+?/', $data)) {
                        return true;
                    } else {
                        $this->setError($data);
                        return false;
                    }
                } else {
                    $this->setError($command . ' read failed');
                    return false;
                }
            } else {
                $this->setError($command . ' send failed');
                return false;
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
    
    protected function sendCommandSecurity(string $command, int $stateReturn=null)
    {
        debug()->debug('send '.trim($command));
        try {
            if (fwrite($this->socket, $command)) {
                if (is_null($stateReturn)) {
                    return true;
                }
                $data = trim(fread($this->socket, 1024));
                if ($data) {
                    if (preg_match('/^'.$stateReturn.'+?/', $data)) {
                        return true;
                    } else {
                        $this->setError($data);
                        return false;
                    }
                } else {
                    $this->setError($command . ' read failed');
                    return false;
                }
            } else {
                $this->setError($command . ' send failed');
                return false;
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

    public function getError()
    {
        return $this->error;
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
