<?php

namespace suda\welcome\response;

use suda\core\Request;
use suda\core\Response;

class SimpleResponse extends Response
{
    public function onRequest(Request $request)
    {
        $view = $this->page('simple');
        $view->set('ip', $request->ip());
        $view->render();
    }
}
