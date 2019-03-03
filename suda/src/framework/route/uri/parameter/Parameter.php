<?php
namespace suda\framework\route\uri\parameter;

/**
 * 匹配参数
 */
abstract class Parameter   {

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
    
    public function __construct(string $extra) {
    }

    public static function name():string {
        return static::$name;
    }

    public static function build(string $indexName, string $extra):Parameter {
        $parameter =  new static($extra);
        $parameter->setIndexName($indexName);
        return $parameter;
    }
    
    public function unpackValue(string $value) {
        return $value;
    }

    public function packValue(string $value) {
        return $value;
    }

    public function getDefaultValue() {
        return isset($this->default) ? $this->default : null;
    }

    /**
     * 获取匹配字符串
     *
     * @return string
     */
    public abstract function getMatch():string;

    public function getCommonDefault(string $extra):string {
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
}