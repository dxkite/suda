<?php
namespace suda\application\exception;

use RuntimeException;

/**
 * 应用异常
 */
class ApplicationException extends RuntimeException
{
    const ERR_MANIFAST_IS_EMPTY = 1;
    const ERR_FRAMEWORK_VERSION = 2;
    const ERR_MODULE_REQUIREMENTS = 3;
    const ERR_MODULE_NAME = 4;
    const ERR_CONFLICT_MODULE_NAME = 5;
    const ERR_PATH_NOT_EXISTS_IN_MODULE = 6;
}
