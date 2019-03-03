<?php
namespace suda\framework\request;

/**
 * HTTP 入口解析查找
 */
class IndexFinder
{
    /**
     * 入口文件
     *
     * @var string
     */
    protected $indexFile;

    /**
     * 文本根目录
     *
     * @var string
     */
    protected $documentRoot;

    /**
     * 入口文件
     *
     * @var string
     */
    protected $entranceFile;

    public function __construct(?string $entranceFile = null, ?string $documentRoot = null)
    {
        $this->entranceFile = $entranceFile ?? \get_included_files()[0];
        $this->documentRoot = $documentRoot;
    }

    /**
     * 入口文件
     *
     * @return string
     */
    private function getEntranceFile():string
    {
        return $this->entranceFile;
    }

    /**
     * 引导文件
     *
     * @return string
     */
    public function getIndexFile():string
    {
        if (isset($this->indexFile)) {
            return $this->indexFile;
        }
        if (strpos(__DIR__, 'phar://') === 0) {
            $indexFile = substr($this->getEntranceFile(), strlen('phar://'.$this->getDocumentRoot()));
        } else {
            $indexFile = substr($this->getEntranceFile(), strlen($this->getDocumentRoot()));
        }
        return $this->indexFile = str_replace('\\', '/', $indexFile);
    }

    /**
     * Get 文本根目录
     *
     * @return  string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }
}
