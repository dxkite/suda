<?php
namespace suda\orm\struct;

trait SimpleJsonDataTrait  
{
    abstract public function getJsonData();

    /**
     * 获取序列化对象
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getJsonData();
    }
}
