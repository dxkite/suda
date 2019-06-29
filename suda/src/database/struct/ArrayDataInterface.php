<?php
namespace suda\database\struct;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use Traversable;

/**
 * Interface ArrayDataInterface
 * @package suda\database\struct
 */
interface ArrayDataInterface extends ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @return Traversable
     */
    public function getIterator();

    /**
     * @return array
     */
    public function toArray():array;

    /**
     * @return int
     */
    public function count();
}
