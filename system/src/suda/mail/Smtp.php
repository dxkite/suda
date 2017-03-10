<?php

namespace suda\mail;

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

    public function __construct(string $host, int $port = 25, int $timeout=30, bool $auth = false, string $user, string $pass)
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
            if (!$this->smtpSockopen()) {
                $this->errstr.='Error: Cannot send email to '.$email."\n";
                $this->errno=11;
                $return=false;
                continue;
            }
            // 发送
            if (!$this->smtpSend($this->host, $this->from[0], $email, $header, $message)) {
                $this->errstr.='Error: Cannot send email to '.$email."\n";
                $this->errno=11;
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
        $header.='X-Mailer: Suda-App/'.conf("app.verison", 'dev')."\r\n";
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
        return $this->msg;
    }

    
    private function smtpSend($helo, $from, $to, $header, $body = "")
    {
        // 验证链接
        if (!$this->stmpCmd("HELO", $helo)) {
            $this->errstr.="sending HELO command"."\n";
            $this->errno=2;
            return false;
        }
        if ($this->auth) {
            if (!$this->stmpCmd("AUTH LOGIN", base64_encode($this->user))) {
                $this->errstr.="AUTH LOGIN command"."\n";
                $this->errno=3;
                return false;
            }
            if (!$this->stmpCmd(base64_encode($this->passwd))) {
                $this->errstr.="AUTH PASSWD command"."\n";
                $this->errno=4;
                return false;
            }
        }
        if (!$this->stmpCmd("MAIL", "FROM:<".$from.">")) {
            $this->errstr.="sending MAIL FROM command"."\n";
            $this->errno=5;
            return false;
        }
        if (!$this->stmpCmd("RCPT", "TO:<".$to.">")) {
            $this->errstr.="sending RCPT TO command"."\n";
            $this->errno=6;
            return false;
        }
        if (!$this->stmpCmd("DATA")) {
            $this->errstr.="sending DATA command"."\n";
            $this->errno=7;
            return false;
        }
        if (!$this->smtpMessage($header, $body)) {
            $this->errstr.="sending message"."\n";
            $this->errno=8;
            return false;
        }
        if (!$this->smtpEom()) {
            $this->errstr.="sending <CR><LF>.<CR><LF> [EOM]"."\n";
            $this->errno=9;
            return false;
        }
        if (!$this->stmpCmd("QUIT")) {
            $this->errstr.="sending QUIT command"."\n";
            $this->errno=10;
            return false;
        }
        return true;
    }

    private function smtpSockopen()
    {
        $this->sock = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!($this->sock && $this->smtpCheck())) {
            $this->errno=$errno;
            $this->errstr.=$errstr;
            return false;
        }
        return true;
    }
    // TODO: MX OPEN
    // private function stmpOpenMX($domain){}
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
    public function smtpCheck()
    {
        $response = str_replace("\r\n", "", fgets($this->sock, 512));
        if (!preg_match("/^[23]/", $response)) {
            fputs($this->sock, "QUIT\r\n");
            fgets($this->sock, 512);
            $this->errstr.= 'Remote host returned '.$response."\n";
            $this->erron=1;
            return false;
        }
        return true;
    }
}
