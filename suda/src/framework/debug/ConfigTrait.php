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

    public function applyConfig(array $config)
    {
        $defaultConfig = $this->getDefaultConfig();
        foreach ($defaultConfig as $name => $value) {
            $this->config[$name] = $config[$name] ?? $this->config[$name] ?? $value;
        }
    }
    public function getConfig(string $name) {
        $defaultConfig = $this->getDefaultConfig();
        return $this->config[$name] ?? $defaultConfig[$name];
    }

    abstract public function getDefaultConfig():array;
}
