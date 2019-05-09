<?php


namespace suda\application\exception;

use RuntimeException;

class ConfigurationException extends RuntimeException
{
    const ERR_MISSING_CONFIG = 1;
}
