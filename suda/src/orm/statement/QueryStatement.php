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
     * 设置取一条记录
     *
     * @param string|null $class
     * @param array $args
     * @return self
     */
    public function wantOne(?string $class = null, array $args = [])
    {
        $this->fetch = self::FETCH_ONE;
        if ($class !== null) {
            $this->setFetchType($class, $args);
        }
        return $this;
    }

    /**
     * 设置取全部记录
     *
     * @param string|null $class
     * @param array $args
     * @return self
     */
    public function wantAll(?string $class = null, array $args = [])
    {
        $this->fetch = self::FETCH_ALL;
        if ($class !== null) {
            $this->setFetchType($class, $args);
        }
        return $this;
    }

    /**
     * 设置取值类
     *
     * @param string|null $class
     * @param array $args
     * @return self
     */
    public function wantType(?string $class = null, array $args = [])
    {
        $this->setFetchType($class, $args);
        return $this;
    }
    
    /**
     * 设置使用某个字段做Key
     *
     * @param string $key
     * @return self
     */
    public function withKey(string $key)
    {
        $this->withKey = $key;
        $this->wantAll();
        return $this;
    }

    /**
     * Get the value of withKey
     */
    public function getWithKey()
    {
        return $this->withKey;
    }
    
    /**
     * 滚动获取
     *
     * @return self
     */
    public function scroll()
    {
        $this->scroll = true;
        return $this;
    }
}
