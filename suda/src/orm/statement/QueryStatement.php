<?php
namespace suda\orm\statement;

use suda\orm\TableStruct;
use suda\orm\statement\Statement;
use suda\orm\exception\SQLException;
use suda\orm\statement\PrepareTrait;

class QueryStatement extends Statement
{
    use PrepareTrait;

    protected $withKey = null;

    /**
     * 创建写
     *
     * @param string $rawTableName
     * @param TableStruct $struct
     */
    public function __construct(string $query, ...$args)
    {
        parent::__construct($query, ...$args);
        $this->type = self::READ;
        $this->fetch = self::FETCH_ONE;
    }

    /**
     * 取1
     *
     * @return self
     */
    public function fetch()
    {
        $this->fetch = self::FETCH_ONE;
        return $this;
    }

    /**
     * 取全部
     *
     * @return self
     */
    public function fetchAll()
    {
        $this->fetch = self::FETCH_ALL;
        return $this;
    }

    /**
     * 用某段做Key
     *
     * @param string $key
     * @return self
     */
    public function withKey(string $key)
    {
        $this->withKey = $key;
        $this->fetchAll();
        return $this;
    }

    /**
     * 获取字符串
     *
     * @return void
     */
    public function prepare()
    {
        // noop
    }

    /**
     * Get the value of withKey
     */
    public function getWithKey()
    {
        return $this->withKey;
    }
}
