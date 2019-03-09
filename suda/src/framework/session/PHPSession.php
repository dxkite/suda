<?php
namespace suda\framework\session;

use suda\framework\Request;
use suda\framework\Session;
use suda\framework\Response;
use suda\framework\filesystem\FileSystem;

/**
 * Session 接口
 */
class PHPSession implements Session
{
    /**
     * 会话ID
     *
     * @var int
     */
    protected $id;

    /**
     * Session对象配置
     *
     * @var array
     */
    protected $conifg;

    /**
     * 请求
     *
     * @var \suda\framework\Request
     */
    protected $request;

    /**
     * 创建Session
     *
     * @param \suda\framework\Request $request 请求
     * @param \suda\framework\Response $response 响应
     * @param array $config 配置属性
     */
    public function __construct(Request $request, Response $response, array $config = [])
    {
        $this->conifg = $config;
        $this->request = $request;
        if (session_status() === PHP_SESSION_NONE) {
            $this->init($request, $config);
        } else {
            $this->update();
        }
    }

    protected function init(Request $request, array $config)
    {
        $path = './session';
        if (array_key_exists('path', $config)) {
            $path = $config['path'];
        } elseif (\defined('SUDA_DATA')) {
            $path = constant('SUDA_DATA').'/session';
        } else {
            throw new \Exception('php session save path missing');
        }
        $name = $config['name'] ?? 'php_session';
        FileSystem::makes($path);

        if ($id = $request->getCookie($name)) {
            session_id($id );
        }else{
            $id = md5($path.$request->getRemoteAddr().uniqid());
            session_id($id);
        }
        session_save_path($path);
        session_name($name);
        session_cache_limiter($config['limiter'] ?? 'private');
        session_cache_expire($config['expire'] ?? 0);
        session_start();
        $this->id = $id;
    }

    /**
     * 设置Session
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function set(string $name, $value):bool
    {
        $_SESSION[$name] = $value;
        return $this->has($name);
    }

    /**
     * 获取Session
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name = null, $default = null)
    {
        if ($name !== null) {
            return $this->has($name) ?$_SESSION[$name]:$default;
        } else {
            return $_SESSION;
        }
    }

    /**
     * 删除一个或者全部Session数据
     *
     * @param string|null $name
     * @return bool
     */
    public function delete(?string $name = null):bool
    {
        if (null === $name) {
            session_unset();
        } else {
            unset($_SESSION[$name]);
        }
        return true;
    }

    /**
     * 检测是否存在Session
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name):bool
    {
        return \array_key_exists($name, $_SESSION);
    }

    /**
     * 获取Session唯一ID
     *
     * @return string
     */
    public function id():string
    {
        return $this->id;
    }

    /**
     * 销毁Session
     *
     * @return boolean
     */
    public function destory():bool
    {
        session_unset();
        session_destroy();
        return true;
    }

    /**
     * 更新SessionId
     *
     * @return boolean
     */
    public function update():bool
    {
        $this->destory();
        $this->init($this->request, $this->conifg);
        return true;
    }
}
