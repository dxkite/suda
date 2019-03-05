<?php
namespace suda\orm;

interface Statement
{
    const WRITE = 0;
    const READ = 1;
    /**
     * 获取语句类型
     *
     * @return integer
     */
    public function getType():int;
    /**
     * 是否滚动
     *
     * @return boolean
     */
    public function scroll():bool;
    /**
     * 获取SQL字符串
     *
     * @return string
     */
    public function getString();
    /**
     * 获取绑定信息
     *
     * @return Binder[]
     */
    public function getBinder();

    public function isFetchOne():bool;

    public function isFetchAll():bool;
}
