<?php
namespace suda\archive;

class Statement
{
    protected $statement;
    protected $bindValue;

    /**
     * Statement constructor.
     * @param $statement
     * @param $bindValue
     */
    public function __construct(string $statement, array $bindValue)
    {
        $this->statement = $statement;
        $this->bindValue = $bindValue;
    }

    /**
     * @return string
     */
    public function getStatement():string
    {
        return $this->statement;
    }

    /**
     * @param string $statement
     */
    public function setStatement(string $statement)
    {
        $this->statement = $statement;
    }

    /**
     * @return array
     */
    public function getBindValue()
    {
        return $this->bindValue;
    }

    /**
     * @param array $bindValue
     */
    public function setBindValue(array $bindValue)
    {
        $this->bindValue = $bindValue;
    }
}
