<?php
namespace suda\orm\statement;

use suda\orm\Binder;

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

    public function __construct(string $query, array  $binder = [])
    {
        $this->query = trim($query);
        $this->binder = $binder;
    }

    /**
     * Get 绑定
     *
     * @return  array
     */
    public function getBinder()
    {
        return $this->binder;
    }

    /**
     * Set 绑定
     *
     * @param  array  $binder  绑定
     *
     * @return  self
     */
    public function setBinder(array $binder)
    {
        $this->binder = $binder;

        return $this;
    }

    /**
     * Get sQL语句
     *
     * @return  string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set sQL语句
     *
     * @param  string  $query  SQL语句
     *
     * @return  self
     */
    public function setQuery(string $query)
    {
        $this->query = $query;

        return $this;
    }
}
