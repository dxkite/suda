<?php


namespace suda\database\statement;

class StatementConfig
{
    const WRITE = 0;
    const READ = 1;

    const FETCH_ONE = 0;
    const FETCH_ALL = 1;

    const RET_ROWS = 1;
    const RET_LAST_INSERT_ID = 2;
    const RET_BOOL = 3;

    /**
     * 语句类型
     *
     * @var int|null
     */
    protected $type = null;

    /**
     * 获取类型
     *
     * @var int
     */
    protected $fetch = null;

    /**
     * 滚动
     *
     * @var boolean|null
     */
    protected $scroll = null;


    /**
     * 返回类型
     *
     * @var int
     */
    protected $returnType = self::RET_BOOL;



    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->type === self::READ;
    }

    /**
     * @return bool
     */
    public function isWrite(): bool
    {
        return $this->type === self::WRITE;
    }

    /**
     * 判断是否为一条
     *
     * @param bool|null $set
     * @return boolean
     */
    public function isFetchOne(bool $set = null): bool
    {
        if ($set !== null) {
            $this->fetch = self::FETCH_ONE;
        }
        return $this->fetch === self::FETCH_ONE;
    }

    /**
     * 判断是否为一条
     *
     * @return boolean
     */
    public function isFetch(): bool
    {
        return $this->fetch !== null;
    }

    /**
     * @param bool|null $scroll
     */
    public function setScroll(?bool $scroll): void
    {
        $this->scroll = $scroll;
    }

    /**
     * 判断是否获取多条
     *
     * @param bool|null $set
     * @return boolean
     */
    public function isFetchAll(bool $set = null): bool
    {
        if ($set !== null) {
            $this->fetch = self::FETCH_ALL;
        }
        return $this->fetch === self::FETCH_ALL;
    }


    /**
     * 是否滚动
     *
     * @param bool|null $set
     * @return boolean|null
     */
    public function isScroll(bool $set = null): ?bool
    {
        if ($set !== null) {
            $this->scroll = true;
        }
        return $this->scroll;
    }


    /**
     * Get 返回类型
     *
     * @return  int
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Set 返回类型
     *
     * @param int $returnType 返回类型
     * @return  $this
     */
    public function setReturnType(int $returnType)
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getFetch(): int
    {
        return $this->fetch;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $fetch
     */
    public function setFetch(int $fetch): void
    {
        $this->fetch = $fetch;
    }
}
