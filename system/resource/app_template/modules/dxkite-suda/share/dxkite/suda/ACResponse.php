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
 * @version    1.2.3
 */

namespace dxkite\suda;

use suda\core\Session;
use suda\core\Request;

/**
 * 权限控制类
 */
abstract class ACResponse extends \suda\core\Response
{
    public function __construct()
    {
        parent::__construct();
        Session::getInstance();
        // 只允许本地调试
        if (conf('debug-local',true) && !self::selfCheck()){
            $this->state(403);
            _D()->warning('recieve track form address> '.Request::ip());
            die('<span style="color:red">YOU ARE NOT THE DEVELOPER!</span>');
        }
    }
    public function onRequest(Request $resquest)
    {
        $set=conf('debug-passwd', false);
        if ($set) {
            if (Session::get('signin', false)) {
                $this->onAction($resquest);
            } else {
                $page=$this->page('suda:signin');
                if ($resquest->isPost() && isset($resquest->post()->passwd)) {
                    $passwd=$resquest->post()->passwd;
                    if ($passwd===$set) {
                        Session::set('signin', true);
                        $this->refresh();
                    } else {
                        $page->set('msgerror', true);
                    }
                }
                $page->render();
            }
        } else {
            $this->onAction($resquest);
        }
    }
    public function selfCheck()
    {
        if (Request::ip()!=='::1'||Request::ip()!=='127.0.0.1') {
            try {
                $content=file_get_contents(u('self_check'));
                if ($check=json_decode($content,true)){
                    if($check['self::ip']===Request::ip() &&$check['self::check']==SUDA_VERSION){
                        return true;
                    }
                }
            } catch (Exception $e) {
                return false;
            }
            return false;
        }
        return true;
    }
    abstract public function onAction(Request $resquest);
}
