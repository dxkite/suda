<?php

namespace suda\database\statement;

use suda\database\Binder;

class Query
{

    /**
     * SQL语句
     *
     * @var string
     */
    protected $query;

    /**
     * 绑定
     *
     * @var array
     */
    protected $binder;

    /**
     * Query constructor.
     * @param string $query
     * @param array $binder
     */
    public function __construct(string $query, array $binder = [])
    {
        $this->query = trim($query);
        $this->binder = $binder;
    }

    /**
     * Get 绑定
     *
     * @return  Binder[]
     */
    public function getBinder()
    {
        return $this->binder;
    }

    /**
     * Set 绑定
     *
     * @param Binder[] $binder 绑定
     *
     * @return  $this
     */
    public function setBinder(array $binder)
    {
        $this->binder = $binder;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->query;
    }
}
