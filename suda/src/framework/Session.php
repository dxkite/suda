<?php
namespace suda\framework;

/**
 * Session 接口
 */
interface Session
{
    /**
     * 创建Session
     *
     * @param Request $request 请求
     * @param Response $response 响应
     * @param array $config 配置属性
     */
    public function __construct(Request $request, Response $response, array $config = []);

    /**
     * 设置Session
     *
     * @param string $name
     * @param mixed $value
     * @return boolean
     */
    public function set(string $name, $value):bool;

    /**
     * 获取Session
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name=null, $default=null);

    /**
     * 删除一个或者全部Session数据
     *
     * @param string|null $name
     * @return bool
     */
    public function delete(?string $name=null):bool;

    /**
     * 检测是否存在Session
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name):bool;

    /**
     * 获取Session唯一ID
     *
     * @return string
     */
    public function id():string;

    /**
     * 销毁Session
     *
     * @return boolean
     */
    public function destory():bool;

    /**
     * 更新SessionId
     *
     * @return boolean
     */
    public function update():bool;
}
