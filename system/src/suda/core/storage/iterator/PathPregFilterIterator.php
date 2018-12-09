<?php
namespace suda\core\storage\iterator;

/**
 * 路径正则迭代器
 * 用于查找路径中符合正则规则的路径
 */
class PathPregFilterIterator extends \FilterIterator
{
    protected $preg;
    
    public function __construct(\Iterator $it, ?string $preg =null)
    {
        parent::__construct($it);
        $this->preg =$preg;
    }

    public function accept():bool
    {
        $item = $this->getInnerIterator();
        if ($item->getFilename() === '.' || $item->getFilename() === '..') {
            return false;
        }
        if ($this->preg && !preg_match($this->preg, $item->getFilename())) {
            return false;
        }
        return true;
    }
}
