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

namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url /database as all method to run this class.
* you call use u('database',Array) to create path.
* @template: default:database_list.tpl.html
* @name: database
* @url: /database
* @param: 
*/
class DatabaseList extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
    {
        return $this->page('suda:database_list', ['header_select'=>'database_list','title'=>'数据库管理'])->render();
    }
}
