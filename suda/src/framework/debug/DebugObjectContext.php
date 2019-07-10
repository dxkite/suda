<?php


namespace suda\framework\debug;

class DebugObjectContext
{

    /**
     * @var array
     */
    protected $object = [];

    /**
     * @param string $objectHash
     * @return bool
     */
    public function isObjectExported(string $objectHash)
    {
        return in_array($objectHash, $this->object);
    }

    public function setObjectIsExported(string $objectHash)
    {
        $this->object [] = $objectHash;
    }
}
