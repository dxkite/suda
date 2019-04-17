<?php
namespace suda\orm\struct;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use suda\orm\struct\JsonDataTransferInterface;

interface ArrayDataInterface extends ArrayAccess, IteratorAggregate, Countable, JsonDataTransferInterface
{
    public function jsonSerialize();
    public function getIterator();
    public function toArray():array;
    public function count();
}
