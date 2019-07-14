<?php
namespace suda\framework\http;

/**
 * HTTP请求文件
 */
class UploadedFile
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
     * 临时文件名
     *
     * @var string
     */
    protected $tempname;

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
        $this->originalName = pathinfo($name, PATHINFO_BASENAME);
        $this->tempname = $path;
    }

    /**
     * Get the value of error
     */
    public function getError()
    {
        return $this->error;
    }


    /**
     * Get the value of mimeType
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * 判断文件是否可用
     *
     * @return boolean
     */
    public function isValid()
    {
        return UPLOAD_ERR_OK === $this->error && is_uploaded_file($this->getTempname());
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
     * 获取临时文件名
     *
     * @return string
     */
    public function getTempname()
    {
        return $this->tempname;
    }
}
