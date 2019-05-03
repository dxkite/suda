<?php
namespace suda\framework\http;

use function str_replace;
use function strtolower;
use suda\framework\http\stream\DataStream;

class HTTPRequest implements Request
{
    /**
     * 请求头数据
     *
     * @var array
     */
    protected $header = [];

    /**
     * 服务环境数据
     *
     * @var array
     */
    protected $server = [];

    /**
     * GET数据
     *
     * @var array
     */
    protected $get = [];

    /**
     * POST数据
     *
     * @var array
     */
    protected $post = [];

    /**
     * 输出的文件
     *
     * @var UploadedFile[]
     */
    protected $files = [];

    /**
     * 输入的Cookie
     *
     * @var string[]
     */
    protected $cookies = [];

    /**
     * 输入流
     *
     * @var Stream
     */
    protected $input;
    
    /**
     * 创建请求
     *
     * @return Request
     */
    public static function create(): Request
    {
        $request = new self;
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
            $this->files[$name] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
        }
    }

    /**
     * 构建请求数据
     *
     * @return void
     */
    protected function buildData()
    {
        $this->cookies = $_COOKIE;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->input = new DataStream('php://input');
    }

    /**
     * 构建环境数据
     *
     * @return void
     */
    protected function buildEnv()
    {
        foreach ($_SERVER as $key => $value) {
            $name = strtolower(str_replace('_', '-', $key));
            if (strpos($name, 'http-') === 0) {
                $name = substr($name, strlen('http-'));
                $this->header[$name] = $value;
            }
            $this->server[$name] = $value;
        }
    }

    /**
     * 获取请求头
     *
     * @return array
     */
    public function header():array {
        return $this->header;
    }
   
    /**
     * 获取服务环境
     *
     * @return array
     */
    public function server():array {
        return $this->server;
    }

    /**
     * GET数据
     *
     * @return array
     */
    public function get():array {
        return $this->get;
    }

    /**
     * POST数据
     *
     * @return array
     */
    public function post():array {
        return $this->post;
    }

    /**
     * 获取Cookie
     *
     * @return array
     */
    public function cookies():array {
        return $this->cookies;
    }

    /**
     * Get 输出的文件
     *
     * @return  UploadedFile[]
     */
    public function files():array {
        return $this->files;
    }

    /**
     * 获取原始输入
     *
     * @return string
     */
    public function input():string
    {
        return $this->input;
    }
}
