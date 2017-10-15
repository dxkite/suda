<?php
namespace suda\mail\sender;

use suda\mail\message\Message;

interface Sender
{
    public function send(Message $message):bool;
    public function getError():string;
}
