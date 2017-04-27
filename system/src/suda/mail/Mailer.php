<?php
namespace suda\mail;

use suda\template\Manager;
use suda\core\{Response,Config};

abstract class Mailer
{
    // 发送至
    protected $to=[];
    // 来至
    protected $from=[];
    // 邮件类型
    protected $type='html';
    // 使用的邮件模板
    protected $use='';
    // 直接发txt
    protected $msg='';
    // 模板值
    protected $values=[];

    protected $subject='';
    private $errno=0;
    private $errstr='';
    private $log;
    protected static $instance;
    const SENDMAIL=0;
    const SMTP=1;
    
    public static function instance(int $type)
    {
        if (!isset(self::$instance[$type])) {
            if ($type==Mailer::SMTP) {
                // 不存在配置则加载
                if (!conf('smtp', false) && file_exists($path=DATA_DIR.'/mailer.php')) {
                    $config=include $path;
                    Config::assign($config);
                    // var_dump($config);
                }
                self::$instance[Mailer::SMTP]=new Smtp(conf('smtp.server'), conf('smtp.port'), conf('smtp.timeout'), conf('smtp.auth'), conf('smtp.email'), conf('smtp.password'),conf('smtp.name'));
            } else {
                self::$instance[Mailer::SENDMAIL]=new Sendmail();
            }
        }
        return self::$instance[$type];
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
        return $this;
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

    // 发送邮件
    abstract public function send(array $value_map=[]);
    public function errno()
    {
        return $this->errno;
    }
    public function error()
    {
        return $this->errstr;
    }
    public function log()
    {
        return $this->log;
    }

    protected function checkmail(string $email)
    {
        $domain = preg_replace('/^.+@([^@]+)$/', '\1', $email);
        // PHP IP 乱入 使用　gethostbyname
        if (!dns_check_record($domain)) {
            _D()->error('dns '.$email.' no found '.$domain);
            throw new DNSnoFound('no such dns in has mail mx '.$email);
        }
        _D()->trace('dns '.$email.' found '.$domain.' '.gethostbyname($domain));
    }

    protected function parseFrom()
    {
        if (isset($this->from[1])) {
            $name=self::encode($this->from[1]);
            return "From: {$name}<{$this->from[0]}>\r\n";
        } else {
            return 'From: '.$this->from[0] . "\r\n" ;
        }
    }

    protected function parseHeader()
    {
        $header='MIME-Version: 1.0' . "\r\n";
        $header.='Content-Type:'.mime($this->type)."\r\n";
        $header.=self::parseFrom();
        $header.='X-Mailer: Suda-App/'.conf("app.name", 'suda').'-'.conf("app.verison", 'dev')."\r\n";
        return $header;
    }

    protected function parseTo()
    {
        $to='';
        foreach ($this->to as $name => $email) {
            self::checkmail($email);
            if (is_string($name)) {
                $to.="$name <$email>,";
            } else {
                $to.=$email.',';
            }
        }
        return rtrim($to, ',');
    }
    protected function renderBody()
    {
        if ($this->use) {
            $this->type='html';
            $this->msg=Manager::display($this->use)->assign($this->values)->getRenderedString();
        }
        return $this->msg;
    }

    protected function errorHander(int $errno, string $errstr, string $errfile, int $errline, array $errcontext)
    {
        $this->errno=$errno;
        $this->errstr=$errstr;
        self::_log($errno.':'.$errstr);
    }

     
    protected function _log(string $message)
    {
        $this->log[]=$message;
        _D()->trace($message);
    }
    protected function encode(string $text){
        return  '=?UTF-8?B?'. base64_encode($text) .'?=';
    }

    protected function setError(int $errno, string $errstr)
    {
        if (!$this->errno) {
            $this->errno=$errno;
            $this->errstr=$errstr;
            _D()->error($errno,$errstr);
        }
    }
}
