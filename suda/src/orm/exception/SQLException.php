<?php
namespace suda\orm\exception;

class SQLException extends \ErrorException
{
    protected $sql;
    protected $binds;
    public function setSql(string $sql)
    {
        $this->sql=$sql;
        return $this;
    }
    public function getSql()
    {
        return $this->sql;
    }
    public function getBinds()
    {
        return $this->binds;
    }
    public function setBinds(array $binds)
    {
        $this->binds=$binds;
        return $this;
    }
}
