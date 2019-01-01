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

    const Template = Template::class;

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
                    $content=$this->compileString($content, $tagConfig);
                    $content=$this->compileCommand($content, $tagConfig);
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
     * @return void
     */
    public function compile(string $module, string $root, string $name, string $input, string $output)
    {
        $timeName = __('compile $0 $1', $module, $name);
        debug()->time($timeName);
        if (!Storage::exist($input)) {
            $this->raiseException(1, __('input file <$0> $1 at $1 not exist ', $module, $name, $input), 'unknown', 0);
        }

        $tagConfig = $this->getTagConfig($root, $input);
        $content= $this->compileText(Storage::get($input), $tagConfig);
        if (!Storage::isDir($dir=dirname($output))) {
            Storage::mkdirs(dirname($output));
        }
        $classname=Manager::className($module, $name);
        $content='<?php if (!class_exists("'.$classname.'", false)) { class '.$classname.' extends \\'. Template::class .' { protected $name="'. addslashes($name) .'";protected $module="'.addslashes($module).'"; protected $source="'. addslashes($input).'";protected function _render_template() {  ?>'.$content.'<?php }} } return ["class"=>"'.$classname.'","name"=>"'.addslashes($name).'","source"=>"'.addslashes($input).'","module"=>"'.addslashes($module).'"]; ';
        Storage::put($output, $content);
        debug()->timeEnd($timeName);
        $syntax = Manager::checkSyntax($output, $classname);
        if ($syntax !== true) {
            if (!conf('debug')) {
                storage()->delete($output);
            }
            $this->raiseException(2, __('syntax error: $0 near $1:$2', $syntax->getMessage(), $input, $syntax->getLine()), $input, $syntax->getLine());
        }
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
            return new $classname;
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
        $commandString = '';
        if (self::hasCommand($name)) {
            $echo=self::$command[$name]['echo']?'echo':'';
            $command=self::$command[$name]['command'];
            if (is_string($command)) {
                $commandString = '<?php '.$echo.' (new \suda\tool\Command(\''.$command.'\'))->args'. ($exp?:'()').' ?>';
            } elseif ($command instanceof Command) {
                $commandString = $command->exec([$exp]);
            } else {
                $commandString = (new Command($command))->exec([$exp]);
            }
        }
        return strval($commandString);
    }

    private function compileString(string $str, array $tagConfig)
    {
        $processer = new Processer($this);
        $callback=function ($match) use ($processer) {
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
                } elseif (method_exists($processer, $method = 'parse'.ucfirst($name))) {
                    $code = $processer->$method($params);
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

    // View echo
    public static function echo($something)
    {
        foreach (func_get_args() as $arg) {
            echo htmlspecialchars($arg);
        }
    }

    protected function raiseException(int $code, string $message, string $file, int $line)
    {
        throw new \suda\core\Exception(new \ErrorException($message, $code, E_ERROR, $file, $line), 'CompileError');
    }
}
