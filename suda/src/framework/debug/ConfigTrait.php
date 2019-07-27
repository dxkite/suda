<?php
namespace suda\framework\debug;

trait ConfigTrait
{
    
    /**
     * 配置信息
     *
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function applyConfig(array $config)
    {
        $defaultConfig = $this->getDefaultConfig();
        foreach ($defaultConfig as $name => $value) {
            $this->config[$name] = $config[$name] ?? $this->config[$name] ?? $value;
        }
    }

    /**
     * @param string $name
     * @param $value
     */
    public function setConfig(string $name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getConfig(string $name)
    {
        $defaultConfig = $this->getDefaultConfig();
        return $this->config[$name] ?? $defaultConfig[$name];
    }

    abstract public function getDefaultConfig():array;
}
