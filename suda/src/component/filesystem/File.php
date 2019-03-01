<?php
namespace suda\component\file;

/**
 * 文件
 */
class File extends \SplFileInfo
{
    /**
     * 创建文件
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct($path);
    }
}
