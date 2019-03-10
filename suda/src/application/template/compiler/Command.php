<?php
namespace suda\application\template\compiler;

use suda\application\template\compiler\CommandInterface;

class Command implements EchoValueInterface, CommandInterface
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
    
    public function parseSet($exp)
    {
        return "<?php \$this->set{$exp}; ?>";
    }

    public function parseStartInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name = trim(str_replace('\'', '-', trim($v[1], '"\'')));
        return '<?php $this->insert(\''.$name.'\',function () { ?>';
    }
    
    public function parseInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name = str_replace('\'', '-', trim($v[1], '"\''));
        return "<?php \$this->exec('{$name}'); ?>";
    }
    
    public function parseEndInsert()
    {
        return '<?php });?>';
    }


    protected function parseIf($exp)
    {
        return "<?php if {$exp}: ?>";
    }

    protected function parseEndif()
    {
        return '<?php endif; ?>';
    }
    
    protected function parseElse()
    {
        return '<?php else: ?>';
    }

    protected function parseElseif($exp)
    {
        return "<?php elseif {$exp}: ?>";
    }

    protected function parseFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    protected function parseEndfor()
    {
        return '<?php endfor; ?>';
    }


    protected function parseForeach($exp)
    {
        return "<?php foreach{$exp}: ?>";
    }

    protected function parseEndforeach()
    {
        return '<?php endforeach; ?>';
    }

    protected function parseWhile($exp)
    {
        return "<?php while{$exp}: ?>";
    }

    protected function parseEndwhile()
    {
        return '<?php endwhile; ?>';
    }

    protected function parseInclude($content)
    {
        return '<?php $this->include'.$content.'; ?>';
    }

    protected function parseExtend($content)
    {
        return '<?php $this->extend'.$content.'; ?>';
    }

    protected function parseStatic($content)
    {
        $content = strlen(trim($content)) === 0 ?'()':$content;
        return '<?php echo $this->getStaticPrefix'.$content.'; ?>';
    }
}
