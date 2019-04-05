<?php
namespace suda\framework\http;

use SplFileObject;

/**
 * HTTP请求文件
 */
class UploadedFile extends SplFileObject
{
    /**
     * 上传的文件名
     *
     * @var string
     */
    protected $originalName;
    /**
     * 文件的Mimetype
     *
     * @var string
     */
    protected $mimeType;
    /**
     * 错误码
     *
     * @var int
     */
    protected $error;

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
        if ($this->error === UPLOAD_ERR_OK) {
            parent::__construct($path);
        }
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
    
    /**
     * Get 上传的文件名
     *
     * @return  string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Set 上传的文件名
     *
     * @param  string  $originalName  上传的文件名
     *
     * @return  self
     */
    public function setOriginalName(string $originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }
}
