<?php
namespace suda\framework\http\stream;

use function ob_get_clean;
use function ob_start;
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
     * @var SplFileObject|string
     */
    protected $stream;

    /**
     * 输入文件流
     *
     * @param SplFileObject|string $stream
     * @param int $offset
     * @param int|null $length
     * @param int $blockSize
     */
    public function __construct($stream, int $offset = 0, ?int $length = null, int $blockSize = 8192)
    {
        $this->stream = $stream;
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
        ob_start();
        $this->echo();
        return ob_get_clean();
    }

    /**
     * 输出
     *
     * @return void
     */
    public function echo()
    {
        $remain = $this->length();
        $stream = $this->openStream();
        if ($stream->fseek($this->offset) === 0) {
            // 持续链接则继续发送内容
            while ($stream->eof() === false && $remain > 0) {
                $readLength = $remain >= $this->blockSize ? $this->blockSize : $remain;
                $remain -= $readLength;
                $data = $stream->fread($readLength);
                echo $data;
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
        if ($this->length === null) {
            $stream = $this->openStream();
            $stream->fseek(0, SEEK_END);
            $this->length = $stream->ftell();
        }
        return $this->length;
    }

    /**
     * 获取流名称
     *
     * @return string
     */
    public function getStreamName()
    {
        if (is_string($this->stream)) {
            return $this->stream;
        } else {
            return $this->stream->getPathname();
        }
    }

    protected function openStream(): SplFileObject
    {
        if (is_string($this->stream)) {
            return new SplFileObject($this->stream);
        } else {
            return $this->stream;
        }
    }
}
