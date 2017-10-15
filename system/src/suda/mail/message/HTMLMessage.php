<?php
namespace suda\mail\message;
use suda\template\Manager;

class HTMLMessage extends Message {
    public function __construct(string $subject,string $template,array $values){
        $message=Manager::display($template);
        $message->assign($values);     
        parent::__construct($subject,$message);
    }
}