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
 * @version    since 1.2.4
 */

namespace __class_namespace__;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url __create_url__ as __methods__ method to run this class.
* you call use u('__router_name__',Array) to create path.
* @template: __template_path__
* @name: __router_name__
* @url: __create_url__
* @param: __param_mark__
*/
class __class_name__ extends \__parent__
{
    public function onRequest(Request $request)
    {
        $page=$this->page('__module__:__template_name__');

        // params if had
        __params_str__;
        // param values array
        $value=__param_array__;
        // display template

        $page->set('title', 'Welcome to use Suda!')
        ->set('helloworld', 'Hello,World!')
        ->set('value', $value);

        return $page->render();
    }
}
