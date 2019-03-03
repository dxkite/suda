<?php
namespace suda\framework\filesystem;

use suda\framework\loader\Path;
use suda\framework\filesystem\FileHelper;
use suda\framework\filesystem\DirectoryHelper;

/**
 * 文件辅助函数
 */
class FileSystem implements FileSystemInterface
{
    use DirectoryHelper {
        make as protected;
        rm as protected;
        rmdirs as protected;
        move as moveDir;
        copy as copyDir;
    }

    use FileHelper  {
       delete as protected deleteFile;
       FileHelper::copy insteadof DirectoryHelper;
       FileHelper::move insteadof DirectoryHelper;
    }



    /**
     * 删除文件或者目录
     *
     * @param string $path
     * @return boolean
     */
    public static function delete(string $path):bool
    {
        if (($path=Path::format($path)) !== null) {
            if (is_file($path)) {
                return static::deleteFile($path);
            }

            if (is_dir($path)) {
                return static::rmDirs($path);
            }
        }
        return false;
    }
}
