<?php
namespace suda\framework\runnable\target;

/**
 * 可执行命令：文件类型
 *
 */
class FileTarget extends RunnableTarget
{

    /**
     * 需要的文件
     *
     * @var string|null
     */
    protected $requireFile = null;

    /**
     * FileTarget constructor.
     * @param string|null $path
     * @param array $parameter
     */
    public function __construct(?string $path, array $parameter = [])
    {
        $this->setRequireFile($path);
        $this->setParameter($parameter);
    }

    public function getRunnableTarget()
    {
        return null;
    }

    /**
     * Get 需要的文件
     *
     * @return  string|null
     */
    public function getRequireFile()
    {
        return $this->requireFile;
    }

    /**
     * Set 需要的文件
     *
     * @param  string|null  $requireFile  需要的文件
     *
     * @return  self
     */
    public function setRequireFile($requireFile)
    {
        $this->requireFile = $requireFile;
        $this->name = '@'.$requireFile;
        return $this;
    }

    /**
     * 是否可执行
     *
     * @return boolean
     */
    public function isValid():bool
    {
        return $this->requireFile !== null && file_exists($this->requireFile);
    }

    /**
     * 执行代码
     *
     * @param array $args
     * @return mixed
     */
    public function apply(array $args)
    {
        if (count($args) == 0) {
            $args = $this->getParameter();
        }
        array_unshift($args, $this->requireFile);
        $_SERVER['argv'] = $args;
        $_SERVER['args'] = count($args);
        return include $this->requireFile;
    }
}
