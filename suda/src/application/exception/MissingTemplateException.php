<?php
namespace suda\application\exception;

use RuntimeException;

/**
 * 模板找不到
 */
class MissingTemplateException extends RuntimeException
{
    protected $name;

    public function __construct(string $name, int $type = 0)
    {
        $this->name = $name;
        parent::__construct(sprintf('missing %s template %s', $type == 0 ? 'source':'dest', $name));
    }


    /**
     * Get the value of path
     */
    public function getTemplateName()
    {
        return $this->name;
    }
}
