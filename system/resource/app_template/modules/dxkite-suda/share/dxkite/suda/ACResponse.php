<?php
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
    }
    public function onRequest(Request $resquest)
    {
        $set=conf('debug-passwd', false);
        if ($set) {
            if (Session::get('signin', false)) {
                $this->onAction($resquest);
            } else {
                $page=$this->page('suda:signin');
                if ($resquest->isPost()) {
                    $passwd=$resquest->post()->passwd('');
                
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
    abstract public function onAction(Request $resquest);
}
