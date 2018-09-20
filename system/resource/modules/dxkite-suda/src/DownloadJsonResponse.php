<?php
namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;

class DownloadJsonResponse extends \suda\core\Response
{
    public function onRequest(Request $resquest)
    {
        $hash =  $resquest->get('id');
        if (preg_match('/\w-\w/', $hash)) {
            $content = storage()->get(APP_LOG.'/dump/'.$hash.'.json');
            $this->json(json_decode($content));
        }
    }
}
