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
 * @version    1.2.4
 */

namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url /self_check as GET method to run this class.
* you call use u('self_check',Array) to create path.
* @template: default:self_check.tpl.html
* @name: self_check
* @url: /self_check
* @param: 
*/
class SelfCheck extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        return $this->json(['self::check'=>SUDA_VERSION, 'self::ip'=>$request->ip()]);
    }
}
