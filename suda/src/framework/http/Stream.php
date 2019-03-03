<?php
namespace suda\framework\http;

/**
 * 数据流
 */
class Stream
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
     * @var string
     */
    protected $stream;

    public function __construct(string $stream, int $offset = 0, int $length = null, int $blockSize = 8192)
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
        $handle = fopen($this->stream, 'rb');
        if (is_resource($handle)) {
            \fseek($handle, $this->offset);
            while (!feof($handle)) {
                echo fread($handle, $this->blockSize);
            }
            fclose($handle);
        }
    }

    /**
     * 获取流长度
     *
     * @return integer
     */
    public function length():int
    {
        return null === $this->length ? $this->getStreamLength() : $this->length;
    }

    /**
     * 获取流长度
     *
     * @return integer
     */
    protected function getStreamLength(): int
    {
        $handle = fopen($this->stream, 'rb');
        if (is_resource($handle)) {
            \fseek($handle, 0, \SEEK_END);
            $size = \ftell($handle);
            \fclose($handle);
            return $size;
        }
        return 0;
    }
}
