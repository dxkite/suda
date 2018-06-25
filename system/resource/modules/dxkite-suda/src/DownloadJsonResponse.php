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

class DownloadJsonResponse extends \suda\core\Response
{
    public function onRequest(Request $resquest)
    {
        $hash =  $resquest->get('id');
        if (preg_match('/\w-\w/',$hash)) {
            $content = storage()->get(APP_LOG.'/dump/'.$hash.'.json');
            $this->json(json_decode($content));
        }
    }
}
