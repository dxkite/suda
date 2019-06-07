<?php
namespace suda\database\middleware;

use Closure;
use function json_decode;
use function json_encode;
use function serialize;
use suda\framework\runnable\Runnable;
use function unserialize;

/**
 * 通用中间件
 */
class CommonMiddleware extends NullMiddleware
{

    /**
     * 注册中间件处理
     *
     * @var Runnable[]
     */
    protected $processor;

    /**
     * 设置某参数序列化
     *
     * @param string $name
     * @return CommonMiddleware
     */
    public function serializeIt(string $name): CommonMiddleware
    {
        $this->registerInput($name, [$this, 'inputSerialize']);
        $this->registerOutput($name, [$this, 'outputUnserialize']);
        return $this;
    }

    /**
     * 设置某参数序列化
     *
     * @param string $name
     * @return CommonMiddleware
     */
    public function jsonIt(string $name): CommonMiddleware
    {
        $this->registerInput($name, [$this, 'inputJson']);
        $this->registerOutput($name, [$this, 'outputJson']);
        return $this;
    }

    /**
     * 注册输入处理
     *
     * @param string $name
     * @param Runnable|Closure|array|string $runnable
     * @return CommonMiddleware
     */
    public function registerInput(string $name, $runnable): CommonMiddleware
    {
        $this->processor['input:'.$name] = $runnable;
        return $this;
    }

    /**
     * 输出数据处理
     *
     * @param string $name
     * @param Runnable|Closure|array|string $runnable
     * @return CommonMiddleware
     */
    public function registerOutput(string $name, $runnable): CommonMiddleware
    {
        $this->processor['output:'.$name] = $runnable;
        return $this;
    }

    /**
     * 输入名处理
     *
     * @param Runnable|Closure|array|string $runnable
     * @return CommonMiddleware
     */
    public function registerInputName($runnable): CommonMiddleware
    {
        $this->processor['input-name'] = $runnable;
        return $this;
    }

    /**
     * 输出名处理
     *
     * @param Runnable|Closure|array|string $runnable
     * @return CommonMiddleware
     */
    public function registerOutputName($runnable): CommonMiddleware
    {
        $this->processor['output-name'] = $runnable;
        return $this;
    }

    /**
     * 输出字段处理
     *
     * @param Runnable|Closure|array|string $runnable
     * @return CommonMiddleware
     */
    public function registerRow($runnable): CommonMiddleware
    {
        $this->processor['output-row'] = $runnable;
        return $this;
    }


    /**
     * 处理输入数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function input(string $name, $data)
    {
        $key = 'input:'.$name;
        if (array_key_exists($key, $this->processor)) {
            return (new Runnable($this->processor[$key]))($data);
        }
        return $data;
    }

    /**
     * 处理输出数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function output(string $name, $data)
    {
        $key = 'output:'.$name;
        if (array_key_exists($key, $this->processor)) {
            return (new Runnable($this->processor[$key]))($data);
        }
        return $data;
    }

    /**
     * 对输出列进行处理
     *
     * @param mixed $row
     * @return mixed
     */
    public function outputRow($row)
    {
        $key = 'output-row';
        if (array_key_exists($key, $this->processor)) {
            return (new Runnable($this->processor[$key]))($row);
        }
        return $row;
    }

    /**
     * 输入参数名
     *
     * @param string $name
     * @return string
     */
    public function inputName(string $name):string
    {
        $key = 'input-name';
        if (array_key_exists($key, $this->processor)) {
            return (new Runnable($this->processor[$key]))($name);
        }
        return $name;
    }

    /**
     * 输出参数名
     *
     * @param string $name
     * @return string
     */
    public function outputName(string $name):string
    {
        $key = 'output-name';
        if (array_key_exists($key, $this->processor)) {
            return (new Runnable($this->processor[$key]))($name);
        }
        return $name;
    }

    /**
     * 序列化
     *
     * @param mixed $data
     * @return string
     */
    private function inputSerialize($data)
    {
        return $data === null? $data : serialize($data);
    }

    /**
     * 序列化
     *
     * @param mixed $data
     * @return mixed
     */
    private function outputUnserialize($data)
    {
        return unserialize($data) ?: null;
    }

    /**
     * 序列化
     *
     * @param mixed $data
     * @return string
     */
    private function inputJson($data)
    {
        return $data === null? $data : json_encode($data);
    }

    /**
     * 序列化
     *
     * @param mixed $data
     * @return mixed
     */
    private function outputJson($data)
    {
        return json_decode($data) ?: null;
    }
}
