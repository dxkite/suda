<?php
namespace suda\orm\struct;

use JsonSerializable;

interface JsonDataTransferInterface extends JsonSerializable
{
    public function getJsonData();
    public function jsonSerialize();
}
