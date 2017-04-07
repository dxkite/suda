<?php
namespace suda\mail;

use suda\template\Manager;
use suda\core\Response;

class Smtp implements Mailer
{
    // 发送至
    private $to=[];
    // 来至
    private $from=[];
    // 邮件类型
    private $type='html';
    // 使用的邮件模板
    private $use='';
    // 直接发txt
    private $msg='';
    // 模板值
    private $values=[];

    private $subject='';
    private $errno=0;
    private $errstr='';

    private $host;
    private $port;
    private $auth;
    private $user;
    private $passwd;
    private $sock=null;
    private $timeout;
    private $log;
    public function __construct(string $host=null, int $port = 25, int $timeout=30, bool $auth = false, string $user, string $pass)
    {
        $this->port = $port;
        $this->host = $host;
        $this->timeout = $timeout;
        $this->auth = $auth;
        $this->user = $user;
        $this->passwd = $pass;
    }


    // Mail To
    public function to(string $email, string $name='')
    {
        if ($name) {
            $this->to[$name]=$email;
        } else {
            $this->to[]=$email;
        }
        return $this;
    }
    public function subject(string $subject)
    {
        $this->subject=$subject;
        return $this;
    }
    public function from(string $email, string $name='')
    {
        $this->from=[$email,$name];
        return $this;
    }
    // raw message
    public function message(string $msg)
    {
        $this->msg=$msg;
        $this->type='txt';
        $this->use=null;
    }
    // 使用模板
    public function use(string $tpl)
    {
        $this->use=$tpl;
        return $this;
    }
    // 模板压入值
    public function assign(string $name, $value)
    {
        $this->values[$name]=$value;
        return $this;
    }
    public function errno()
    {
        return $this->errno;
    }
    public function error()
    {
        return $this->errstr;
    }
    public function log(){
        return $this->log;
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

    
    private function parseFrom()
    {
        if ($this->from[1]) {
            return "From: {$this->from[1]}<{$this->from[0]}>\r\n";
        } else {
            return 'From: '.$this->from[0] . "\r\n" ;
        }
    }

    private function parseHeader()
    {
        $header='MIME-Version: 1.0' . "\r\n";
        $header.='Content-Type:'.mime($this->type)."\r\n";
        $header.=self::parseFrom();
        if ($this->subject) {
            $header.= 'Subject: '.$this->subject."\r\n";
        }
        $header.='X-Mailer: Suda-App/'.conf("app.name", 'suda').'-'.conf("app.verison", 'dev')."\r\n";
        list($msec, $sec) = explode(" ", microtime());
        $header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".".$this->from[0].">\r\n";
        return $header;
    }

    private function parseTo()
    {
        $to='';
        foreach ($this->to as $name => $email) {
            if (is_string($name)) {
                $to.="$name <$email>,";
            } else {
                $to.=$email.',';
            }
        }
        return rtrim($to, ',');
    }

    private function renderBody()
    {
        if ($this->use) {
            $this->type='html';
            ob_start();
            Response::space('email');
            Response::assign($this->values);
            Manager::display($this->use);
            Response::reset();
            $this->msg=ob_get_clean();
        }
        return $this->msg;
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
            $this->sock = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
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
        if (!@getmxrr($domain, $MXhosts)) {
            $this->setError(13, 'cannot resolve MX '.$domain);
            return false;
        }
        
        foreach ($MXhosts as $host) {
            $this->sock = @fsockopen($host, $this->port, $errno, $errstr, $this->timeout);
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
    private function setError(int $errno, string $errstr)
    {
        if (!$this->errno) {
            $this->errno=$errno;
            $this->errstr=$errstr;
        }
    }
    private function _log(string $message){
        $this->log[]=$message;
    }
}
