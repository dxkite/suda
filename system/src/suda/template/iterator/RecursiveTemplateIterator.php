<?php
namespace suda\template\iterator;

class RecursiveTemplateIterator extends \FilterIterator
{
    protected $extension;
    protected $directory;
    protected $staticDirectory;

    public function __construct(string $directory, ?string $extension = 'html')
    {
        parent::__construct(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::CURRENT_AS_PATHNAME)));
        $this->extension = $extension;
        $this->directory = $directory;
        $this->staticDirectory = $directory.DIRECTORY_SEPARATOR.'static';
    }

    public function accept():bool
    {
        $item = $this->getInnerIterator();
        if (!$item->isFile()) {
            return false;
        }
        if (strpos($item->getPath(), $this->staticDirectory) === 0) {
            return false;
        }
        if ($this->extension && $item->getExtension() !== $this->extension) {
            return false;
        }
        if (pathinfo($item->getBasename('.'.$item->getExtension()), PATHINFO_EXTENSION) !== 'tpl') {
            return false;
        }
        return true;
    }
    
    public function key()
    {
        $key = parent::key();
        $basename = substr($key, strlen($this->directory) + 1);
        $basename = substr($basename, 0, strpos($basename, '.tpl'));
        $basename = str_replace('\\', '/', $basename);
        return $basename;
    }
}
