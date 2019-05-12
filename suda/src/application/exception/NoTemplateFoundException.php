<?php
namespace suda\application\exception;

use RuntimeException;

/**
 * 模板找不到
 */
class NoTemplateFoundException extends RuntimeException
{
    const T_SOURCE = 1;
    const T_DEST = 2;
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $type;

    public function __construct(string $message, int $code, string $name, int $type = 0)
    {
        $this->name = $name;
        $this->type = $type;
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getTemplateType(): int
    {
        return $this->type;
    }
}
