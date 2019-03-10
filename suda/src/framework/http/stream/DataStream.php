<?php
namespace suda\framework\http\stream;

use SplFileObject;
use suda\framework\http\Stream;

/**
 * 数据流
 */
class DataStream implements Stream
{
    /**
     * 设置偏移
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * 设置长度
     *
     * @var int|null
     */
    protected $length = null;

    /**
     * 读取快大小
     *
     * @var integer
     */
    protected $blockSize = 8192;

    /**
     * 流描述
     *
     * @var SplFileObject
     */
    protected $stream;

    /**
     * 输入文件流
     *
     * @param SplFileObject|string $stream
     * @param integer $offset
     * @param integer|null $length
     * @param integer $blockSize
     */
    public function __construct($stream, int $offset = 0, ?int $length = null, int $blockSize = 8192)
    {
        $this->stream = $stream instanceof SplFileObject? $stream : new SplFileObject($stream, 'rb');
        $this->offset = $offset;
        $this->length = $length;
        $this->blockSize = $blockSize;
    }

    /**
     * 获取全部内容
     *
     * @return string
     */
    public function __toString()
    {
        \ob_start();
        $this->echo();
        return \ob_get_clean();
    }

    /**
     * 输出
     *
     * @return void
     */
    public function echo()
    {
        $remain = $this->length();
        if ($this->stream->fseek($this->offset) === 0) {
            while ($this->stream->eof() === false && $remain > 0) {
                $readLength = $remain >= $this->blockSize ? $this->blockSize : $remain;
                $remain -= $readLength;
                echo $this->stream->fread($readLength);
            }
        }
    }

    /**
     * 获取流长度
     *
     * @return integer
     */
    public function length():int
    {
        if ($this->length !== null) {
            return $this->length;
        }
        $this->stream->fseek(0, \SEEK_END);
        return $this->stream->ftell();
    }
}
