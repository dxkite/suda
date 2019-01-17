<?php
namespace suda\template\compiler\suda;

use suda\template\Manager;

class Processer
{
    protected $compiler;
    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }
    
    public function parseValue($var)
    {
        return '<?php echo $this->get'.$this->compiler->echoValue($var) .'; ?>';
    }
    
    public function parseEcho($exp)
    {
        return "<?php echo htmlspecialchars{$exp}; ?>";
    }
    
    public function parseData($exp)
    {
        return "<?php \$this->data{$exp}; ?>";
    }

    public function parseFile($exp)
    {
        if (preg_match('/\((.+)\)/', $exp, $v)) {
            $name=trim($v[1], '"\'');
            return "<?php echo suda\\template\\Manager::file('{$name}',\$this) ?>";
        }
        return '@file';
    }

    public function parse_($exp)
    {
        return "<?php echo __$exp; ?>";
    }

    public function parseIf($exp)
    {
        return "<?php if {$exp}: ?>";
    }

    public function parseEndif()
    {
        return '<?php endif; ?>';
    }
    public function parseElse()
    {
        return '<?php else: ?>';
    }
    public function parseElseif($exp)
    {
        return "<?php elseif {$exp}: ?>";
    }
    // for
    public function parseFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }
    public function parseEndfor()
    {
        return '<?php endfor; ?>';
    }
    // foreach
    public function parseForeach($exp)
    {
        return "<?php foreach{$exp}: ?>";
    }
    public function parseEndforeach()
    {
        return '<?php endforeach; ?>';
    }
    // while
    public function parseWhile($exp)
    {
        return "<?php while{$exp}: ?>";
    }

    public function parseEndwhile()
    {
        return '<?php endwhile; ?>';
    }

    // include
    public function parseInclude($exp)
    {
        return '<?php $this->include'.$exp.'; ?>';
    }

    // extend
    public function parseExtend($exp)
    {
        return '<?php $this->extend'.$exp.'; ?>';
    }

    public function parseU($exp)
    {
        if ($exp==='') {
            $exp='()';
        }
        return "<?php echo \$this->url$exp; ?>";
    }
    
    public function parseSelf($exp)
    {
        if ($exp) {
            return '<?php echo suda\core\Router::getInstance()->buildUrl(suda\core\Response::$name,$_GET,false,'.$exp.'); ?>';
        }
        return '<?php echo suda\core\Router::getInstance()->buildUrl(suda\core\Response::$name,$_GET,false); ?>';
    }

    public function parseSet($exp)
    {
        return "<?php \$this->set{$exp}; ?>";
    }

    public function parseB($exp)
    {
        return "<?php echo \$this->boolecho{$exp}; ?>";
    }

    public function parseStatic($exp)
    {
        if (preg_match('/^\((.+)\)$/', $exp, $match)) {
            if (isset($match[1])&&$match[1]) {
                $module=trim(trim($match[1], '"\''));
                return '<?php echo suda\\template\\Manager::assetServer(\''.Manager::getStaticAssetPath($module).'\');?>';
            }
        }
        return '<?php echo suda\\template\\Manager::assetServer(suda\\template\\Manager::getStaticAssetPath($this->getModule())); ?>';
    }

    
    public function parseUrl($exp)
    {
        return "<?php echo u{$exp}; ?>";
    }
    
    public function parseStartInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=trim(str_replace('\'', '-', trim($v[1], '"\'')));
        return '<?php $this->execHook(\''.$name.'\',function () { ?>';
    }
    
    public function parseEndInsert()
    {
        return '<?php });?>';
    }

    public function parseNonce($exp)
    {
        if (preg_match('/\((.+)\)/', $exp, $v)) {
            preg_match('/\((.+)\)/', $exp, $v);
            $name=trim($v[1], '"\'');
            return 'nonce="<?php echo $this->getNonce("'.$name.'") ?>"';
        }
        return 'nonce="<?php echo $this->getNonce() ?>"';
    }

    public function parseInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        return "<?php \$this->exec('{$name}'); ?>";
    }
}
