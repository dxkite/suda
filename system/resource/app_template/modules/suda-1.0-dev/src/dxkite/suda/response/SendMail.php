<?php
namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};
use suda\mail\Mailer;

/**
* visit url /sendmail as all method to run this class.
* you call use u('send_mail',Array) to create path.
* @template: default:send_mail.tpl.html
* @name: send_mail
* @url: /sendmail
* @param: 
*/
class SendMail extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        return $this->page('suda:send_mail')
        ->set('title',Mailer::instance())
        ->set('helloworld','Hello,World!')->set('header_select','system_admin')
        ->render();
    }
}
