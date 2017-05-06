<?php
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
class ConfigMailer extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $config=[];
        if ($request->isPost()) {
            $config['smtp']=$request->post()->smtp;
            $config['smtp']['auth']=$config['smtp']['auth']??false;
            $config['sendmail']=$request->post()->sendmail;
            ArrayHelper::export(DATA_DIR.'/mailer.runtime.config.php', '_mailer_runtime_config', $config);
        } else {
            if (file_exists($path=DATA_DIR.'/mailer.runtime.config.php')) {
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
