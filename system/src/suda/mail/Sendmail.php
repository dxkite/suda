<?php
namespace suda\mail;

class Sendmail implements Mailer
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
        set_error_handler(array($this, 'errorHander'));
        $return=mail($to, $this->subject, $message, $header);
        restore_error_handler();
        // var_dump($message);
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
        //$header.='To: '.self::parseTo()."\r\n";
        $header.='X-Mailer: DxSite/'.conf("app.verison",'dev')."\r\n";
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

    private function errorHander(int $errno, string $errstr, string $errfile, int $errline, array $errcontext)
    {
        $this->errno=$errno;
        $this->errstr=$errstr;
    }

    private function renderBody()
    {
        if ($this->use) {
            $file=Manager::viewPath('__mail__/'.$this->use);
            ob_start();
            $_Mail=new Core\Value($this->values);
            require $file;
            $this->message=ob_get_clean();
        }
        return $this->message;
    }
}
