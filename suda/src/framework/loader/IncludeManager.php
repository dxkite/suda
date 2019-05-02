<?php
namespace suda\framework\loader;

use function in_array;

/**
 * 包含管理器
 *
 */
class IncludeManager implements PathInterface
{
    use PathTrait;
    /**
     * 默认命名空间
     *
     * @var array
     */
    protected $namespace = [ __NAMESPACE__ ];

    /**
     * 包含路径
     *
     * @var array
     */
    protected $includePath = [];

    /**
     * 将JAVA，路径分割转换为PHP分割符
     *
     * @param string $name 类名
     * @return string 真实分隔符
     */
    public static function realName(string $name):string
    {
        return str_replace(['.','/'], '\\', $name);
    }

    /**
     * 获取真实或者虚拟存在的地址
     *
     * @param string $name
     * @return string|null
     */
    public static function realPath(string $name):?string
    {
        return Path::format($name);
    }

    /**
     * @param string $path
     * @param string|null $namespace
     */
    public function addIncludePath(string $path, ?string $namespace = null)
    {
        if ($path = static::realPath($path)) {
            $namespace = $namespace ?? 0;
            if (array_key_exists($namespace, $this->includePath)) {
                if (!in_array($path, $this->includePath[$namespace])) {
                    $this->includePath[$namespace][] = $path;
                }
            } else {
                $this->includePath[$namespace][] = $path;
            }
        }
    }

    public function getIncludePath()
    {
        return $this->includePath;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace)
    {
        if (!in_array($namespace, $this->namespace)) {
            $this->namespace[] = $namespace;
        }
    }
}
