<?php
namespace suda\framework\route\uri\parameter;

/**
 * 匹配参数
 */
abstract class Parameter
{
    /**
     * @var
     */
    protected static $name;
    /**
     * 索引名
     *
     * @var string
     */
    protected $indexName;
    /**
     * 默认值
     *
     * @var mixed
     */
    protected $default;

    /**
     * 参数出现的索引
     *
     * @var int
     */
    protected $index;

    /**
     * Parameter constructor.
     * @param string $extra
     */
    public function __construct(string $extra)
    {
    }

    /**
     * @return string
     */
    public static function name():string
    {
        return static::$name;
    }

    /**
     * @param int $index
     * @param string $indexName
     * @param string $extra
     * @return Parameter
     */
    public static function build(int $index, string $indexName, string $extra):Parameter
    {
        $parameter = new static($extra);
        $parameter->setIndex($index);
        $parameter->setIndexName($indexName);
        return $parameter;
    }

    /**
     * @param string $value
     * @return string
     */
    public function unpackValue(string $value)
    {
        return $value;
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    public function packValue(?string $value)
    {
        return $value;
    }
    
    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->hasDefault() ? $this->default : null;
    }

    /**
     * @return bool
     */
    public function hasDefault()
    {
        return isset($this->default);
    }

    /**
     * 获取匹配字符串
     *
     * @return string
     */
    abstract public function getMatch():string;

    /**
     * @param string $extra
     * @return string
     */
    public function getCommonDefault(string $extra):string
    {
        return $extra;
    }

    /**
     * Get 索引名
     *
     * @return  string
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * Set 索引名
     *
     * @param  string  $indexName  索引名
     *
     * @return  self
     */
    public function setIndexName(string $indexName)
    {
        $this->indexName = $indexName;

        return $this;
    }

    /**
     * Get 参数出现的索引
     *
     * @return  int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set 参数出现的索引
     *
     * @param  int  $index  参数出现的索引
     *
     * @return  self
     */
    public function setIndex(int $index)
    {
        $this->index = $index;

        return $this;
    }
}
