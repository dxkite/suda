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

namespace suda\mail\message;

class Message
{
    protected $from;
    protected $to=[];
    protected $cc=[];
    protected $bcc=[];
    protected $subject;
    protected $body;
    protected $attachment=[];
    
    public function __construct(string $subject, string $body)
    {
        $this->setSubject($subject);
        $this->setBody($body);
    }

    public function setFrom(string $fromEmail, string $name='')
    {
        $name=self::utf8($name);
        $this->from = [$fromEmail,$name];
        return $this;
    }

    public function setTo(string $toEmail, string $name='')
    {
        $name=self::utf8($name);
        $this->to[] =[$toEmail,$name];
        return $this;
    }

    public function setCc(string $ccEmail, string $name='')
    {
        $name=self::utf8($name);
        $this->cc[] =[$ccEmail,$name];
        return $this;
    }

    public function setBcc(string $bccEmail, string $name='')
    {
        $name=self::utf8($name);
        $this->bcc[] =[$bccEmail,$name];
        return $this;
    }

    public function setSubject(string $subject)
    {
        $this->subject=$subject;
        return $this;
    }
    
    public function setBody(string $body)
    {
        $this->body=$body;
        return $this;
    }

    public function addAttachment(string $file)
    {
        if (file_exists($file) && !in_array($file, $this->attachment)) {
            $this->attachment[] = $file;
        }
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getCc()
    {
        return $this->cc;
    }

    public function getBcc()
    {
        return $this->bcc;
    }
    
    public function getHeader() {
        $header ="MIME-Version: 1.0\r\n";
        $header.='X-Mailer: Suda-App/'.conf("app.name", 'suda').'-'.conf("app.verison", 'dev')." (https://github.com/DXkite/suda)\r\n";
        $header.= 'FROM: '.$this->utf8($this->from[1]).'<' . $this->from[0] . ">\r\n";
        
        // to
        if (!empty($this->to)) {
            $count = count($this->to);
            if ($count == 1) {
                $header .= 'TO: '.$this->utf8($this->to[0][1]).'<' . $this->to[0][0] .">\r\n";
            } else {
                for ($i=0; $i<$count; $i++) {
                    if ($i == 0) {
                        $header .= 'TO: '.$this->utf8($this->to[$i][1]).'<' . $this->to[$i][0] .'>';
                    } elseif ($i + 1 == $count) {
                        $header .=','.$this->utf8($this->to[$i][1]).'<' . $this->to[$i][0] .">\r\n";
                    } else {
                        $header .=','.$this->utf8($this->to[$i][1]).'<' . $this->to[$i][0] .'>';
                    }
                }
            }
        }
        // CC
        if (!empty($this->cc)) {
            $count = count($this->cc);
            if ($count == 1) {
                $header .= 'CC: '.$this->utf8($this->cc[0][1]).'<' . $this->cc[0][0] .">\r\n";
            } else {
                for ($i=0; $i<$count; $i++) {
                    if ($i == 0) {
                        $header .= 'CC: '.$this->utf8($this->cc[$i][1]).'<' . $this->cc[$i][0] .'>';
                    } elseif ($i + 1 == $count) {
                        $header .=','.$this->utf8($this->cc[$i][1]).'<' . $this->cc[$i][0] .">\r\n";
                    } else {
                        $header .=','.$this->utf8($this->cc[$i][1]).'<' . $this->cc[$i][0] .'>';
                    }
                }
            }
        }
        // BCC
        if (!empty($this->bcc)) {
            $count = count($this->bcc);
            if ($count == 1) {
                $header .= 'BCC: '.$this->utf8($this->bcc[0][1]).'<' . $this->bcc[0][0] .">\r\n";
            } else {
                for ($i=0; $i<$count; $i++) {
                    if ($i == 0) {
                        $header .= 'BCC: '.$this->utf8($this->bcc[$i][1]).'<' . $this->bcc[$i][0] .'>';
                    } elseif ($i + 1 == $count) {
                        $header .=','.$this->utf8($this->bcc[$i][1]).'<' . $this->bcc[$i][0] .">\r\n";
                    } else {
                        $header .=','.$this->utf8($this->bcc[$i][1]).'<' . $this->bcc[$i][0] .'>';
                    }
                }
            }
        }
        return $header;
    }

    public function getMessage()
    {
        $separator = '----=_Separator_'. md5($this->from[0] . time()) . uniqid(); //分隔符
        $header='';
        if (empty($this->attachment)) {
            $header .= "Content-Type: multipart/related;\r\n";
        } else {
            $header .= "Content-Type: multipart/mixed;\r\n";
        }

        //邮件头分隔符
        $header .= "\t" . 'boundary="' . $separator . '"'."\r\n";
        //这里开始是邮件的body部分，body部分分成几段发送
        $header .= "\r\n--" . $separator . "\r\n";
        $header .= 'Content-Type: text/html; charset="utf-8"'."\r\n";
        $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $header .= base64_encode($this->body) . "\r\n";
        $header .= "--" . $separator . "\r\n";
        //加入附件
        if (!empty($this->attachment)) {
            $count = count($this->attachment);
            for ($i=0; $i<$count; $i++) {
                $header .= "\r\n--" . $separator . "\r\n";
                $header .= 'Content-Type: ' . $this->getMIMEType($this->attachment[$i]) . '; name="=?UTF-8?B?' . base64_encode(basename($this->attachment[$i])) . '?="' . "\r\n";
                $header .= "Content-Transfer-Encoding: base64\r\n";
                $header .= 'Content-Disposition: attachment; filename="=?UTF-8?B?' . base64_encode(basename($this->attachment[$i])) . '?="' . "\r\n";
                $header .= "\r\n";
                $header .= $this->readFile($this->attachment[$i]);
                $header .= "\r\n--" . $separator . "\r\n";
            }
        }
        //结束邮件数据发送
        $header .= "\r\n.\r\n";
        return $header;
    }

    protected static function utf8(string $text)
    {
        if (!empty($text)) {
            return '=?UTF-8?B?'.base64_encode($text).'?=';
        }
        return '';
    }

    protected function getMIMEType(string $path)
    {
        $ext=pathinfo($path, PATHINFO_EXTENSION);
        return mime($ext);
    }
    
    protected function readFile(string $file)
    {
        if (file_exists($file)) {
            return base64_encode(file_get_contents($file));
        } else {
            throw new \suda\exception\KernelException('file ' . $file . ' dose not exist');
        }
    }

    public function getAttachment()
    {
        return $this->attachment;
    }
}
