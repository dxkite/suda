<?php

namespace suda\application\template\compiler;

/**
 * Class Command
 * @package suda\application\template\compiler
 */
class Command implements EchoValueInterface, CommandInterface
{
    use EchoValueTrait;
    /**
     * 配置
     *
     * @var array
     */
    protected $config;

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return method_exists($this, 'parse' . ucfirst($name));
    }

    /**
     * @param string $name
     * @param string $content
     * @return string
     */
    public function parse(string $name, string $content): string
    {
        if ($this->has($name)) {
            return $this->{'parse' . ucfirst($name)}($this->parseEchoValue($content));
        }
        return '';
    }

    /**
     * @param array $config
     * @return $this|mixed
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param $exp
     * @return string
     */
    public function parseSet($exp)
    {
        return "<?php \$this->set{$exp}; ?>";
    }

    /**
     * @param $exp
     * @return string
     */
    public function parseStartInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name = trim(str_replace('\'', '-', trim($v[1], '"\'')));
        return '<?php $this->insert(\'' . $name . '\',function () { ?>';
    }

    /**
     * @param $exp
     * @return string
     */
    public function parseInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name = str_replace('\'', '-', trim($v[1], '"\''));
        return "<?php \$this->exec('{$name}'); ?>";
    }

    /**
     * @return string
     */
    public function parseEndInsert()
    {
        return '<?php });?>';
    }


    /**
     * @param $exp
     * @return string
     */
    protected function parseIf($exp)
    {
        return "<?php if {$exp}: ?>";
    }

    /**
     * @return string
     */
    protected function parseEndif()
    {
        return '<?php endif; ?>';
    }

    /**
     * @return string
     */
    protected function parseElse()
    {
        return '<?php else: ?>';
    }

    /**
     * @param $exp
     * @return string
     */
    protected function parseElseif($exp)
    {
        return "<?php elseif {$exp}: ?>";
    }

    /**
     * @param $expression
     * @return string
     */
    protected function parseFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * @return string
     */
    protected function parseEndfor()
    {
        return '<?php endfor; ?>';
    }


    /**
     * @param $exp
     * @return string
     */
    protected function parseForeach($exp)
    {
        return "<?php foreach{$exp}: ?>";
    }

    /**
     * @return string
     */
    protected function parseEndforeach()
    {
        return '<?php endforeach; ?>';
    }

    /**
     * @param $exp
     * @return string
     */
    protected function parseWhile($exp)
    {
        return "<?php while{$exp}: ?>";
    }

    /**
     * @return string
     */
    protected function parseEndwhile()
    {
        return '<?php endwhile; ?>';
    }

    /**
     * @param $content
     * @return string
     */
    protected function parseInclude($content)
    {
        return '<?php $this->include' . $content . '; ?>';
    }

    /**
     * @param $content
     * @return string
     */
    protected function parseExtend($content)
    {
        return '<?php $this->extend' . $content . '; ?>';
    }

    /**
     * @param $content
     * @return string
     */
    protected function parseStatic($content)
    {
        $content = strlen(trim($content)) === 0 ? '()' : $content;
        return '<?php echo $this->getStaticPrefix' . $content . '; ?>';
    }

    /**
     * @param $exp
     * @return string
     */
    public function parseCall($exp)
    {
        return "<?php \$this->call{$exp}; ?>";
    }
}
