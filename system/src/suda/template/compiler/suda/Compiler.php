<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\template\compiler\suda;

use suda\core\Storage;
use suda\core\Application;
use suda\tool\Command;
use suda\template\Compiler as CompilerImpl;
use suda\template\Manager;

/**
 * Suda 模板编译器
 */
class Compiler implements CompilerImpl
{
    protected static $tagConfig = [
        'raw' => [
            'open' => '{{!',
            'close' => '}}'
        ],
        'echo' => [
            'open' => '{{',
            'close' => '}}'
            ],
        'comment' =>[
            'open' => '{--',
            'close' => '--}'
        ],
        'hook' => [
            'open' => '{:',
            'close' => '}'
        ],
        'str' =>[
            'open' => '{=',
            'close' => '}'
        ],
        'rawStr' =>[
            'open' => '@{',
            'close' => '}'
        ],
        'command' => [
            'open' => '@',
            'close' => ''
        ],
    ];

    protected static $compiledPhp = [
        'raw' => '<?php echo $code; ?>',
        'echo' => '<?php echo htmlspecialchars(__($code), ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5); ?>',
        'comment' => '<?php /* $code */ ?>',
        'hook'=> '<?php $this->execGlobalHook("$code"); ?>',
        'str'=> '<?php echo htmlspecialchars(__("$code"), ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5); ?>',
        'rawStr' => '<?php echo htmlspecialchars($code, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5); ?>',
    ];
    
    const Template='suda\template\compiler\suda\Template';

    protected static $template=self::Template;
    
    /**
     * 附加模板命令
     *
     * @var array
     */
    protected static $command=[];

    /**
     * 编译文本
     *
     * @param string $text
     * @param array|null $tagConfig
     * @return string
     */
    public function compileText(string $text, ?array $tagConfig=null):string
    {
        $result='';
        $tagConfig = $tagConfig ?? self::$tagConfig;
        foreach (token_get_all($text) as $token) {
            if (is_array($token)) {
                list($tag, $content) = $token;
                // 所有将要编译的文本
                // 跳过各种的PHP
                if ($tag == T_INLINE_HTML) {
                    $content=self::compileString($content, $tagConfig);
                    $content=self::compileCommand($content, $tagConfig);
                }
                $result .=$content;
            } else {
                $result .=$token;
            }
        }
        /*
        // 合并相邻标签
        $result=preg_replace('/\?\>([^\S\r\n]*?)\<\?php/i', '', $result);
        // PHP行末标签吃掉换行符处理
        $result=preg_replace('/\?\>\r?\n/ms', "?>\r\n\r\n", $result);
        */
        return $result;
    }

    /**
     * 编译文件
     *
     * @param string $name
     * @param string $input
     * @param string $output
     * @return boolean
     */
    public function compile(string $module, string $root, string $name, string $input, string $output):bool
    {
        $timeName = __('compile $0 $1', $module, $name);
        debug()->time($timeName);
        if (!Storage::exist($input)) {
            debug()->warning(__('compile error no sorce file <$0> $1 at $1', $module, $name, $input));
            return false;
        }
        $tagConfig = $this->getTagConfig($root, $input);
        $content= $this->compileText(Storage::get($input), $tagConfig);
        if (!Storage::isDir($dir=dirname($output))) {
            Storage::mkdirs(dirname($output));
        }
        $classname=Manager::className($module, $name);
        $content='<?php if (!class_exists("'.$classname.'", false)) { class '.$classname.' extends '.self::$template.' { protected $name="'. addslashes($name) .'";protected $module="'.addslashes($module).'"; protected $source="'. addslashes($input).'";protected function _render_template() {  ?>'.$content.'<?php }} } return ["class"=>"'.$classname.'","name"=>"'.addslashes($name).'","source"=>"'.addslashes($input).'","module"=>"'.addslashes($module).'"]; ';
        Storage::put($output, $content);
        debug()->timeEnd($timeName);
        $syntax= Manager::checkSyntax($output, $classname);
        if ($syntax !== true) {
            if (!conf('debug')) {
                storage()->delete($output);
            }
            if ($syntax instanceof \Exception || $syntax instanceof \Error) {
                throw new \suda\core\Exception(new \ErrorException(__('compile error: $0 near line $1', $syntax->getMessage(), $syntax->getLine()), $syntax->getCode(), conf('exception.compile-error', true)?E_ERROR:E_WARNING, $input, $syntax->getLine()), 'TemplateError');
            }
        }
        return $syntax===true?:$syntax;
    }

    public function getTagConfig(?string $root=null, ?string $input=null)
    {
        $tagConfig = null;
        // 加载特定配置
        if (!is_null($input)) {
            if ($path = config()->resolve($input.'.ini')) {
                $tagConfig = config()->loadConfig($path);
            }
        }
        // 加载全局配置
        if (is_null($tagConfig) && !is_null($root)) {
            if ($path = config()->resolve($root.'/.tpl.ini')) {
                $tagConfig = config()->loadConfig($path);
            }
        }
        if (is_null($tagConfig)) {
            return self::$tagConfig;
        } else {
            foreach (self::$tagConfig as $key => $config) {
                if (\array_key_exists($key, $tagConfig)) {
                    if (!\array_key_exists('open', $tagConfig[$key])) {
                        $tagConfig[$key]['open'] = $config['open'];
                    }
                    if (!\array_key_exists('close', $tagConfig[$key])) {
                        $tagConfig[$key]['close'] = $config['close'];
                    }
                } else {
                    $tagConfig[$key] = $config;
                }
            }
        }
        return $tagConfig;
    }

    public function render(string $viewfile, ?string $name =null)
    {
        if (storage()->exist($viewfile)) {
            $templateInfo =  include $viewfile;
            $classname = $templateInfo['class'];
            return $template=new $classname;
        } else {
            throw new \suda\exception\KernelException(__('template $0 is not ready!', $name ?? $viewfile));
        }
    }
    
    /**
     * 扩展模板命令
     *
     * @param string $name
     * @param string $callback
     * @param bool $echo
     * @return void
     */
    public static function addCommand(string $name, $callback, bool $echo=true)
    {
        $name=ucfirst($name);
        self::$command[$name]=['command'=>$callback,'echo'=>$echo];
    }
    
    /**
     * 检查模板扩展命令是否存在
     *
     * @param string $name
     * @return bool
     */
    public static function hasCommand(string $name):bool
    {
        $name=ucfirst($name);
        return isset(self::$command[$name]);
    }

    /**
     * 创建模板扩展命令
     *
     * @param string $name
     * @param string $exp
     * @return string
     */
    public static function buildCommand(string $name, string $exp):string
    {
        $name=ucfirst($name);
        if (self::hasCommand($name)) {
            $echo=self::$command[$name]['echo']?'echo':'';
            $command=self::$command[$name]['command'];
            if (is_string($command)) {
                return '<?php '.$echo.' (new \suda\tool\Command(\''.$command.'\'))->args'. ($exp?:'()').' ?>';
            } else {
                if ($command instanceof Command) {
                    return $command->exec([$exp]);
                } else {
                    return (new Command($command))->exec([$exp]);
                }
            }
        }
        return '';
    }

    private function compileString(string $str, array $tagConfig)
    {
        $callback=function ($match) {
            if (count($match) >= 5) {
                list($input, $ignore, $name, $space, $params) = $match;
            } else {
                list($input, $ignore, $name, $space) = $match;
                $params = '';
            }
            if ($ignore ==='!') {
                return \str_replace('@!', '', $input);
            } else {
                $code = null;
                if (self::hasCommand(ucfirst($name))) {
                    $code = self::buildCommand($name, $params);
                } elseif (method_exists($this, $method = 'parse'.ucfirst($name))) {
                    $code = $this->$method($params);
                }
                if (is_null($code)) {
                    return $input;
                } else {
                    $code = $this->echoValue($code);
                    return empty($params) ? $code : $code.$space;
                }
            }
        };
        $key = 'command';
        // \x{4e00}-\x{9aff} 为中文字符集范围
        $pregExp = sprintf('/\B(!)?%s([\w\x{4e00}-\x{9aff}]+)(\s*)(\( ( (?>[^()]+) | (?4) )* \) )? /ux', preg_quote($tagConfig[$key]['open']));
        $code = preg_replace_callback($pregExp, $callback, $str);
        $error = preg_last_error();
        if ($error !== PREG_NO_ERROR) {
            throw new \suda\exception\RegexException($error);
        }
        return $code;
    }

    private function compileCommand(string $code, array $tagConfig)
    {
        foreach (self::$compiledPhp as $key => $value) {
            $pregExp = sprintf('/(!)?%s\s*(.+?)\s*%s/', preg_quote($tagConfig[$key]['open']), preg_quote($tagConfig[$key]['close']));
            $code = \preg_replace_callback($pregExp, function ($match) use ($value) {
                if ($match[1] === '!') {
                    return substr($match[0], 1);
                } else {
                    return \str_replace('$code', $this->echoValue($match[2]), $value);
                }
            }, $code);
        }
        return $code;
    }

    protected function echoValue($var)
    {
        // 任意变量名: 中文点下划线英文数字
        $code = preg_replace_callback('/\B[$](\?)?[:]([.\w\x{4e00}-\x{9aff}]+)(\s*)(\( ( (?>[^()]+) | (?4) )* \) )?/ux', [$this,'echoValueCallback'], $var);
        $error = preg_last_error();
        if ($error !== PREG_NO_ERROR) {
            throw new \suda\exception\RegExException($error);
        }
        return $code;
    }
    
    protected function echoValueCallback($matchs)
    {
        $name=$matchs[2];
        if ($matchs[1]==='?') {
            return '$this->has("'.$name.'")';
        }
        if (isset($matchs[4])) {
            if (preg_match('/\((.+)\)/', $matchs[4], $v)) {
                $args = trim($v[1]);
                $args= strlen($args) ?','.$args:'';
                return '$this->get("'.$name.'"'.$args.')';
            }
        }
        return '$this->get("'.$name.'")';
    }

    protected static function parseValue($var)
    {
        return '<?php echo $this->get'.self::echoValue($var) .'; ?>';
    }
    
    protected function parseEcho($exp)
    {
        return "<?php echo htmlspecialchars{$exp}; ?>";
    }
    
    protected function parseData($exp)
    {
        return "<?php \$this->data{$exp}; ?>";
    }

    protected function parseFile($exp)
    {
        if (preg_match('/\((.+)\)/', $exp, $v)) {
            $name=trim($v[1], '"\'');
            return "<?php echo suda\\template\\Manager::file('{$name}',\$this) ?>";
        }
        return '@file';
    }

    protected function parse_($exp)
    {
        return "<?php echo __$exp; ?>";
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
    // for
    protected function parseFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }
    protected function parseEndfor()
    {
        return '<?php endfor; ?>';
    }
    // foreach
    protected function parseForeach($exp)
    {
        return "<?php foreach{$exp}: ?>";
    }
    protected function parseEndforeach()
    {
        return '<?php endforeach; ?>';
    }
    // while
    protected function parseWhile($exp)
    {
        return "<?php while{$exp}: ?>";
    }

    protected function parseEndwhile()
    {
        return '<?php endwhile; ?>';
    }

    // include
    protected function parseInclude($exp)
    {
        return '<?php $this->include'.$exp.'; ?>';
    }

    // extend
    protected function parseExtend($exp)
    {
        return '<?php $this->extend'.$exp.'; ?>';
    }

    protected function parseU($exp)
    {
        if ($exp==='') {
            $exp='()';
        }
        return "<?php echo \$this->url$exp; ?>";
    }
    
    protected function parseSelf($exp)
    {
        if ($exp) {
            return '<?php echo suda\core\Router::getInstance()->buildUrl(suda\core\Response::$name,$_GET,false,'.$exp.'); ?>';
        }
        return '<?php echo suda\core\Router::getInstance()->buildUrl(suda\core\Response::$name,$_GET,false); ?>';
    }

    protected function parseSet($exp)
    {
        return "<?php \$this->set{$exp}; ?>";
    }

    public function parseB($exp)
    {
        return "<?php echo \$this->boolecho{$exp}; ?>";
    }

    protected function parseStatic($exp)
    {
        if (preg_match('/^\((.+)\)$/', $exp, $match)) {
            if (isset($match[1])&&$match[1]) {
                $module=trim(trim($match[1], '"\''));
                return '<?php echo suda\\template\\Manager::assetServer(\''.Manager::getStaticAssetPath($module).'\');?>';
            }
        }
        return '<?php echo suda\\template\\Manager::assetServer(suda\\template\\Manager::getStaticAssetPath($this->getModule())); ?>';
    }

    
    protected function parseUrl($exp)
    {
        return "<?php echo u{$exp}; ?>";
    }
    // View echo
    public static function echo($something)
    {
        foreach (func_get_args() as $arg) {
            echo htmlspecialchars($arg);
        }
    }

    protected function parseStartInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=trim(str_replace('\'', '-', trim($v[1], '"\'')));
        return '<?php $this->execHook(\''.$name.'\',function () { ?>';
    }
    
    protected function parseEndInsert()
    {
        return '<?php });?>';
    }

    protected function parseNonce()
    {
        return 'nonce="<?php echo $this->getNonce() ?>"';
    }

    protected function parseInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        return "<?php \$this->exec('{$name}'); ?>";
    }

    // 错误报错
    public function error()
    {
        return self::$error[self::$erron];
    }
    // 错误码
    public function erron()
    {
        return self::$erron;
    }
}
