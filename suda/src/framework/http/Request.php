<?php
namespace suda\framework\http;

use suda\framework\http\Stream;
use suda\framework\server\request\UploadedFile;

class Request
{
    /**
     * 请求头数据
     *
     * @var array
     */
    public $header;

    /**
     * 服务环境数据
     *
     * @var array
     */
    public $server;

    /**
     * GET数据
     *
     * @var array
     */
    public $get;

    /**
     * POST数据
     *
     * @var array
     */
    public $post;

    /**
     * 输出的文件
     *
     * @var UploadedFile[]
     */
    public $files;

    /**
     * 输入的Cookie
     *
     * @var string[]
     */
    public $cookies;

    /**
     * 输入流
     *
     * @var \suda\framework\http\Stream|string
     */
    public $input;

    public function input():string
    {
        return $this->input;
    }

    /**
     * 创建请求
     *
     * @return Request
     */
    public static function create(): Request
    {
        $request = new Request;
        $request->buildEnv();
        $request->buildData();
        $request->buildFilesFromEnv();
        return $request;
    }

    /**
     * 构建文件数据
     *
     * @return void
     */
    protected function buildFilesFromEnv()
    {
        foreach ($_FILES as $name => $file) {
            $request->files[$name] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
        }
    }

    /**
     * 构建请求数据
     *
     * @return void
     */
    protected function buildData()
    {
        $request->cookies = $_COOKIES;
        $request->get = $_GET;
        $request->post = $_POST;
        $request->input = new Stream('php://input');
    }

    /**
     * 构建环境数据
     *
     * @return void
     */
    protected function buildEnv()
    {
        foreach ($_SERVER as $key => $value) {
            $name = \strtolower(\str_replace('_', '-', $name));
            if (strpos($key, 'http-') === 0) {
                $name = substr($key, strlen('http-'));
                $request->header[$name] = $value;
            } else {
                $request->server[$name] = $value;
            }
        }
    }
}
