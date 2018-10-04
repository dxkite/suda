<?php
namespace suda\core\storage\iterator;

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
