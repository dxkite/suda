<?php
namespace suda\component\debug\log;

use suda\component\loader\Loader;

trait LoaderAwareTrait
{
     /**
     * 加载器
     *
     * @var Loader
     */
    protected $loader;

    
    /**
     * Get 加载器
     *
     * @return  Loader
     */ 
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Set 加载器
     *
     * @param  Loader  $loader  加载器
     *
     * @return  self
     */ 
    public function setLoader(Loader $loader)
    {
        $this->loader = $loader;

        return $this;
    }
}