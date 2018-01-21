<?php
namespace suda\archive\creator;
use PDO;

class InputValue
{
    private $name;
    private $value;
    private $bindType;

    public function __construct(string $name, $value, int $bindType=PDO::PARAM_STR)
    {
        $this->bindType=static::bindParam($value);
        $this->name=$name;
        $this->value=$value;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function getBindType():int
    {
        return $this->bindType;
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function bindParam($value){
        if (is_null($value)) {
            $type=PDO::PARAM_NULL;
        } elseif (is_bool($value)) {
            $type=PDO::PARAM_BOOL;
        } elseif (is_numeric($value)) {
            $type=PDO::PARAM_INT;
        } else {
            $type=PDO::PARAM_STR;
        }
        return $type;
    }
}
