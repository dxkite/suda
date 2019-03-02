<?php
namespace suda\component\debug\log;

use suda\component\loader\Loader;

interface LoaderAwareInterface
{
    /**
     * Get 加载器
     *
     * @return  Loader
     */
    public function getLoader();

    /**
     * Set 加载器
     *
     * @param  Loader  $loader  加载器
     *
     * @return  self
     */
    public function setLoader(Loader $loader);
}
