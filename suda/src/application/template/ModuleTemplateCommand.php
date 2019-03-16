<?php
namespace suda\application\template;

use suda\application\template\compiler\CommandInterface;

class ModuleTemplateCommand implements CommandInterface
{
    use EchoValueTrait;
    
    /**
     * 配置
     *
     * @var array
     */
    protected $config;

    public function has(string $name):bool
    {
        return method_exists($this, 'parse'.ucfirst($name));
    }
    
    public function parse(string $name, string $content):string
    {
        if ($this->has($name)) {
            return $this->{'parse'.ucfirst($name)}($this->parseEchoValue($content));
        }
        return '';
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    protected function parseU(string $data)
    {
        if (strlen(trim($data)) === 0) {
            return '<?php $this->getUrl(); ?>';
        }
        return '<?php $this->getUrl'.$data.'; ?>';
    }
}
