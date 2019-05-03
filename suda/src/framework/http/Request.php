<?php
namespace suda\framework\http;

interface Request
{
    /**
     * 获取请求头
     *
     * @return array
     */
    public function header():array;
   
    /**
     * 获取服务环境
     *
     * @return array
     */
    public function server():array;

    /**
     * GET数据
     *
     * @return array
     */
    public function get():array;

    /**
     * POST数据
     *
     * @return array
     */
    public function post():array;

    /**
     * 获取Cookie
     *
     * @return array
     */
    public function cookies():array;

    /**
     * 获取原始输入
     *
     * @return string
     */
    public function input():string;
 

    /**
     * Get 输出的文件
     *
     * @return  UploadedFile[]
     */
    public function files():array;
}
