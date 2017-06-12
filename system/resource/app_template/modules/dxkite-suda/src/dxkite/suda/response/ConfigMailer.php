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

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use suda\tool\ArrayHelper;
use suda\tool\Json;

/**
* visit url /system/config/Mailer as all method to run this class.
* you call use u('config_mailer',Array) to create path.
* @template: default:config_mailer.tpl.html
* @name: config_mailer
* @url: /system/config/Mailer
* @param:
*/
class ConfigMailer extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
    {
        $config=[];
        if ($request->isPost()) {
            $config['smtp']=$request->post()->smtp;
            $config['smtp']['auth']=$config['smtp']['auth']??false;
            $config['sendmail']=$request->post()->sendmail;
            ArrayHelper::export(RUNTIME_DIR.'/mailer.config.php', '_mailer_runtime_config', $config);
        } else {
            if (file_exists($path=RUNTIME_DIR.'/mailer.config.php')) {
                $config=include $path;
            }
            
        }
        _D()->trace('mailer config',json_encode($config));
        $smtp=$config['smtp']??[];
        $sendmail=$config['sendmail']??[];
        return $this->page('suda:config_mailer')
        ->set('title', '邮箱发送设置')
        ->set('smtp',$smtp)
        ->set('sendmail',$sendmail)
        ->set('header_select', 'system_admin')
        ->render();
    }
}
