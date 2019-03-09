<?php
namespace suda\application\exception;

use RuntimeException;

/**
 * 模板找不到
 */
class MissingTemplateException extends RuntimeException
{
    protected $path;

    public function __construct($path) {
        parent::__construct(sprintf('missing template %s', $this->path));
    }


    /**
     * Get the value of path
     */ 
    public function getPath()
    {
        return $this->path;
    }

}
