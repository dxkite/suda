<?php
namespace suda\application\exception;

use RuntimeException;

/**
 * 应用异常
 */
class ApplicationException extends RuntimeException
{
    const ERR_MANIFAST_IS_EMPTY = 1;
}
