<?php
namespace suda\orm\struct;

use Countable;
use ArrayAccess;
use IteratorAggregate;
interface ArrayDataInterface extends ArrayAccess, IteratorAggregate, Countable
{
    public function getIterator();
    public function toArray():array;
    public function count();
}
