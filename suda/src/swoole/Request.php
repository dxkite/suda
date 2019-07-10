<?php


namespace suda\swoole;


use suda\framework\http\UploadedFile;

class Request implements \suda\framework\http\Request
{

    /**
     * @var \Swoole\Http\Request
     */
    protected $request;

    /**
     * @var UploadedFile[]
     */
    protected $files;

    public function __construct(\Swoole\Http\Request $request)
    {
        $this->request = $request;
        $this->buildFilesFromEnv();
    }

    /**
     * 获取请求头
     *
     * @return array
     */
    public function header(): array
    {
        return $this->request->header ?: [];
    }

    /**
     * 获取服务环境
     *
     * @return array
     */
    public function server(): array
    {
        return $this->request->server ?: [];
    }

    /**
     * GET数据
     *
     * @return array
     */
    public function get(): array
    {
        return $this->request->get ?: [];
    }

    /**
     * POST数据
     *
     * @return array
     */
    public function post(): array
    {
        return $this->request->post ?: [];
    }

    /**
     * 获取Cookie
     *
     * @return array
     */
    public function cookies(): array
    {
        return $this->request->cookie ?: [];
    }

    /**
     * 获取原始输入
     *
     * @return string
     */
    public function input(): string
    {
        return $this->request->rawContent() ?: '';
    }

    /**
     * Get 输出的文件
     *
     * @return  UploadedFile[]
     */
    public function files(): array
    {
        return $this->files;
    }


    /**
     * 构建文件数据
     *
     * @return void
     */
    protected function buildFilesFromEnv()
    {
        if(is_array($this->request->files))  {
            foreach ($this->request->files as $name => $file) {
                $this->files[$name] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
            }
        }
    }
}