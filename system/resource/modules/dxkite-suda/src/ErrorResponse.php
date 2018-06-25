<?php
namespace dxkite\suda\response;

use suda\core\Request;
use suda\core\Response;

class ErrorResponse extends \suda\core\Response
{
    public function onRequest(Request $resquest)
    {
        $code = $resquest->get('code');
        $this->state($code);
        $view=$this->view('suda:error/'.$code);
        if (!$view) {
            $view=$this->page('suda:http_error');
        }
        $view->assign(['error_type'=>'Error','error_code'=>$code,'error_message'=> Response::statusMessage($code)]);
        if ($code == 404) {
            $view->set('path', $request->url());
        }
        $view->render();
    }
}
