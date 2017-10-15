<?php
namespace suda\mail\message;

class Message
{
    protected $form;
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

    public function setFrom(string $fromEmail, string $name=null)
    {
        $this->from = [$fromEmail,$name];
        return $this;
    }

    public function setTo(string $toEmail, string $name=null)
    {
        if (!isset($this->to[$toEmail])) {
            $this->to[$toEmail] =[$toEmail,$name];
        }
        return $this;
    }

    public function setCc(string $cc)
    {
        if (!in_array($cc, $this->cc)) {
            $this->cc[] = $cc;
        }
        return $this;
    }

    public function setBcc(string $bcc)
    {
        if (!in_array($bcc, $this->bcc)) {
            $this->bcc[] = $bcc;
        }
        return $this;
    }

    public function setSubject(string $subject)
    {
        $this->subject="=?UTF-8?B?".base64_encode($subject)."?=";
        return $this;
    }

    public function setBody(string $body)
    {
        $this->body=base64_encode($body);
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
        if (!in_array($bcc, $this->bcc)) {
            $this->bcc[] = $bcc;
        }
        return $this->bcc;
    }


    public function getAttachment()
    {
        return $this->attachment;
    }
}
