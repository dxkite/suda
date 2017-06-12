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
use suda\mail\Mailer;

/**
* visit url /sendmail as all method to run this class.
* you call use u('send_mail',Array) to create path.
* @template: default:send_mail.tpl.html
* @name: send_mail
* @url: /sendmail
* @param: 
*/
class SendMail extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
    {
        $page=$this->page('suda:send_mail');
        if ($request->isPost()){
            $post=$request->post();
            $mailer=Mailer::instance(strtolower($post->type)==='sendmail'?Mailer::SENDMAIL:Mailer::SMTP);
            $result=$mailer
            ->from(conf('sendmail.email'),conf('sendmail.name'))
            ->to($post->email)
            ->subject($post->subject('无主题'))
            ->message($post->content)->send();
            $page->set('result',true);
            $page->set('success',$result);
            $page->set('result_message',$result?'发送成功':'发送失败 '.$mailer->error());
        }
        
        return 
        $page->set('title','发送邮件')
        ->set('helloworld','Hello,World!')->set('header_select','system_admin')
        ->render();
    }
}
