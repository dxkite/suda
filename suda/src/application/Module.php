<?php
namespace suda\application;

use suda\application\Resource;



/**
 * 模块名
 */
class Module
{
    const LOADED = 1;
    const REACHABLE = 2;
    const RUNNING =3;
    
    /**
     * 模块名
     *
     * @var string
     */
    protected $name;

    /**
     * 版本
     *
     * @var string
     */
    protected $version;

    /**
     * 资源路径
     *
     * @var Resource
     */
    protected $resource;

    /**
     * 状态
     *
     * @var int
     */
    protected $status;

    /**
     * 创建模块
     *
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name, string $version = '1.0.0') {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Get 版本
     *
     * @return  string
     */ 
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get 模块名
     *
     * @return  string
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get 资源路径
     *
     * @return  Resource
     */ 
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set 资源路径
     *
     * @param  Resource  $resource  资源路径
     *
     * @return  self
     */ 
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * 设置状态
     *
     * @param integer $status
     * @return void
     */
    public function setStatus(int $status) {
        $this->status = $status;
    }


}
