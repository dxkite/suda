<?php
namespace suda\application\template;

use Exception;
use suda\application\template\RawTemplate;
use suda\application\exception\MissingTemplateException;

/**
 * 异常显示模板
 */
class ExceptionTemplate extends RawTemplate
{
    public function __construct(Exception $exception)
    {
        $this->path = SUDA_RESOURCE. '/error.php';
        $type = get_class($exception);
        $this->value = [
            'error_type' => $type ,
            'error_sort_type' => \substr($type, \strrpos($type, '\\') + 1),
            'error_code' => $exception->getCode(),
            'error_message' => $exception->getMessage(),
        ];
    }

    public function __toString()
    {
        try {
            return parent::__toString();
        } catch (MissingTemplateException $e) {
            return 'MissingTemplateException:'.$e->getPath();
        } catch (Exception $e) {
            return 'Template Render Exception:'.$e->getMessage();
        }
    }
}
