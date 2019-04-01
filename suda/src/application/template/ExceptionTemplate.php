<?php
namespace suda\application\template;

use Throwable;
use suda\application\template\RawTemplate;
use suda\application\exception\MissingTemplateException;

/**
 * 异常显示模板
 */
class ExceptionTemplate extends RawTemplate
{
    public function __construct(Throwable $exception)
    {
        $this->path = SUDA_RESOURCE. '/error.php';
        $type = get_class($exception);
        $this->value = [
            'error_type' => $type ,
            'error_sort_type' => strpos($type, '\\') === false ? $type : \substr($type, \strrpos($type, '\\') + 1),
            'error_code' => $exception->getCode(),
            'error_message' => $exception->getMessage(),
        ];
    }

    public function getRenderedString()
    {
        try {
            return parent::getRenderedString();
        } catch (MissingTemplateException $e) {
            return 'MissingTemplateException:'.$e->getPath();
        } catch (Throwable $e) {
            return 'Template Render Exception:'.$e->getMessage();
        }
    }
}
