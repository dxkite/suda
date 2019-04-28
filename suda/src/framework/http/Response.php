<?php
namespace suda\framework\http;

use suda\framework\http\Cookie;
use suda\framework\http\Header;
use suda\framework\http\Status;
use suda\framework\http\Stream;
use suda\framework\http\HeaderContainer;
use suda\framework\http\stream\DataStream;

/**
 * 原始HTTP响应
 */
interface Response
{
    /**
     * 设置状态码
     *
     * @param integer $statusCode
     * @return $this
     */
    public function status(int $statusCode);

    /**
     * 设置响应版本
     *
     * @param string $version
     * @return $this
     */
    public function version(string $version);

    /**
     * 判断是否发送
     *
     * @return boolean
     */
    public function isSended(): bool;

    /**
     * 设置头部信息
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @param boolean $ucfirst
     * @return $this
     */
    public function header(string $name, string $value, bool $replace = false, bool $ucfirst = true);
    
    /**
     * 设置Cookie信息
     *
     * @param Cookie $cookie
     * @return $this
     */
    public function cookie(Cookie $cookie);

    /**
     * 写数据
     *
     * @param Stream|string $data
     * @return void
     */
    public function write($data);

    /**
     * 发送数据
     *
     * @param Stream|string $data
     * @return void
     */
    public function send($data);

    /**
     * 发送文件内容
     *
     * @param string $filename
     * @param integer $offset
     * @param integer $length
     * @return void
     */
    public function sendFile(string $filename, int $offset = 0, int $length = null);

    /**
     * 跳转
     *
     * @param string $url
     * @param integer $httpCode
     * @return void
     */
    public function redirect(string $url, int $httpCode = 302);
}
