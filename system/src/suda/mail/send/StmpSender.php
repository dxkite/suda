<?php
namespace suda\mail\sender;

use suda\mail\message\Message;

class StmpSender
{
    protected $username;
    protected $password;
    protected $server;
    protected $port;
    protected $isSecurity;
    protected $timeout;

    public function __construct(string $server, int $port, int $timeout, string $name, string $password, bool $isSecurity=true)
    {
        $this->username=$name;
        $this->password=$password;
        $this->isSecurity=$isSecurity;
        $this->server=$server;
        $this->port=$port;
        $this->timeout=$timeout;
    }

    public function send(Message $message):bool
    {
    }
}
