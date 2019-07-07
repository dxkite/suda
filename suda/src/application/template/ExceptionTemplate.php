<?php
namespace suda\application\template;

use function strrpos;
use function substr;
use Throwable;
use suda\application\exception\NoTemplateFoundException;

/**
 * 异常显示模板
 */
class ExceptionTemplate extends RawTemplate
{
    /**
     * ExceptionTemplate constructor.
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        parent::__construct('', []);
        $this->path = SUDA_RESOURCE. '/error.php';
        $type = get_class($exception);
        $this->value = [
            'error_type' => $type ,
            'error_sort_type' => strpos($type, '\\') === false ? $type : substr($type, strrpos($type, '\\') + 1),
            'error_code' => $exception->getCode(),
            'error_message' => $exception->getMessage(),
        ];
    }

    /**
     * @return string
     */
    public function getRenderedString()
    {
        try {
            return parent::getRenderedString();
        } catch (NoTemplateFoundException $e) {
            return 'NoTemplateFoundException:'.$e->getTemplateName();
        } catch (Throwable $e) {
            return 'Template Render Exception:'.$e->getMessage();
        }
    }
}
