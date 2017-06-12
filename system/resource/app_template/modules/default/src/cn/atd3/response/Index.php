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

namespace cn\atd3\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url / as all method to run this class.
* you call use u('index',Array) to create path.
* @template: default:index.tpl.html
* @name: index
* @url: /
* @param: 
*/
class Index extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $page=$this->page('demo/default:index');

        // params if had
        ;
        // param values array
        $value=array();
        // display template

        $page->set('title', 'Welcome to use Suda!')
        ->set('helloworld', 'Hello,World!')
        ->set('value', $value);

        return $page->render();
    }
    public static function showTplName($tpl){
        echo '<div>'.__('当前页面模板名:%s',$tpl->getName()).'</div>';
    }
}
