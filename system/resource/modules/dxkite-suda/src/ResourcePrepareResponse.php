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
 * @version    since 1.2.9
 */

namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

class ResourcePrepareResponse extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
    {
        return $this->json(['resource'=> init_resource() ]);
    }
}
