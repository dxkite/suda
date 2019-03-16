<?php
namespace suda\framework\context;

use suda\framework\Request;
use suda\framework\http\Request as HttpRequest;
use suda\framework\Response;

/**
 * 服务器环境
 */
class ServerContext
{
    /**
     * 服务器请求
     *
     * @var \suda\framework\Request
     */
    protected $request;
    /**
     * 服务器响应
     *
     * @var \suda\framework\Response
     */
    protected $response;

    public function __construct(HttpRequest $request)
    {
        $this->request = new Request($request);
        $this->response = new Response;
    }

    /**
     * 获取服务器请求
     *
     * @return \suda\framework\Request
     */
    public function request():Request
    {
        return $this->request;
    }

    /**
     * 获取服务器响应
     *
     * @return \suda\framework\Response
     */
    public function response():Response
    {
        return $this->response;
    }

   

    /**
     * Get 服务器请求
     *
     * @return  \suda\framework\Request
     */ 
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set 服务器请求
     *
     * @param  \suda\framework\Request  $request  服务器请求
     *
     * @return  self
     */ 
    public function setRequest(\suda\framework\Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set 服务器响应
     *
     * @param  \suda\framework\Response  $response  服务器响应
     *
     * @return  self
     */ 
    public function setResponse(\suda\framework\Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get 服务器响应
     *
     * @return  \suda\framework\Response
     */ 
    public function getResponse()
    {
        return $this->response;
    }
}
