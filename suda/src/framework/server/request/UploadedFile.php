<?php
namespace suda\framework\server\request;

use suda\component\file\File;

/**
 * HTTP请求文件
 */
class UploadedFile extends File
{
    private $originalName;
    private $mimeType;
    private $error;

    /**
     * 创建文件
     *
     * @param string $path 路径
     * @param string $name 文件名
     * @param string $mimeType mime类型
     * @param integer $error 错误码
     */
    public function __construct(string $path, string $name, string $mimeType = null, int $error = null)
    {
        $this->mimeType = $mimeType ?: 'application/octet-stream';
        $this->error = $error ?: UPLOAD_ERR_OK;
        $this->originalName = pathinfo($name, PATHINFO_FILENAME);
        parent::__construct($path);
    }

    /**
     * Get the value of error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get the value of mimeType
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set the value of mimeType
     *
     * @return  self
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * 判断文件是否可用
     *
     * @return boolean
     */
    public function isValid()
    {
        return UPLOAD_ERR_OK === $this->error && is_uploaded_file($this->getPathname());
    }

    public function __destruct()
    {
        $path = $this->getPathname();
        if (file_exists($path) && is_writeable($path)) {
            unlink($path);
        }
    }
}
