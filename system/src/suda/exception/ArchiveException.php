<?php
namespace suda\exception;
class ArchiveException extends \ErrorException {
    const UNKOWN_METHOD=0;
    const UNKOWN_FIELDNAME=1;
    const MISS_ARGUMENT=2;
}