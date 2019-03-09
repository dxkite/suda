<?php
namespace suda\framework\http\stream;

use suda\framework\http\stream\DataStream;

/**
 * 数据流
 */
class StringStream implements DataStream
{
    public function __construct(string $stream, int $offset = 0, int $length = null)
    {
        $this->stream = $stream;
        $this->offset = $offset;
        $this->length = $length;
    }

    /**
     * 获取全部内容
     *
     * @return string
     */
    public function __toString()
    {
        return $this->length === null ? substr($this->stream, $this->offset) : substr($this->stream, $this->offset, $this->length);
    }

    /**
     * 输出
     *
     * @return void
     */
    public function echo()
    {
        echo $this->__toString();
    }

    /**
     * 获取流长度
     *
     * @return integer
     */
    public function length():int
    {
        return strlen($this->__toString());
    }
}
