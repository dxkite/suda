<?php

namespace suda\framework\http;

/**
 * 数据流
 */
interface Stream
{
    /**
     * 获取全部内容
     *
     * @return string
     */
    public function __toString();

    /**
     * 输出
     *
     * @return void
     */
    public function echo();

    /**
     * 获取流长度
     *
     * @return integer
     */
    public function length(): int;
}
