<?php
namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

class ResourcePrepareResponse extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
    {
        return $this->json(['resource'=> init_resource() ]);
    }
}
