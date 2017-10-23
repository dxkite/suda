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
use suda\template\Manager;

class HTMLMessage extends Message {
    public function __construct(string $subject,string $template,array $values){
        $message=Manager::display($template);
        $message->assign($values);     
        parent::__construct($subject,$message);
    }
}