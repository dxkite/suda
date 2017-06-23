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
 * @version    since 1.2.5
 */

namespace cn\atd3\exception;

class AjaxException extends \Exception {
    protected $name;
    protected $data;

    public function setName(string $name){
        $this->name=$name;
        return $this;
    }
    public function setData($data){
        $this->data=$data;
        return $this;
    }
        
    public function getData(){
        return $this->data;
    }
    
    public function getName() {
        return $name??__CLASS__;
    }   
}