<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\template\compiler\suda;

use Storage;
use suda\core\Application;
use suda\core\Hook;
use suda\tool\Value;
use suda\tool\Command;
use suda\template\Compiler as CompilerImpl;
use suda\template\Manager;
use suda\core\Request;

/**
 * Suda 模板编译器
 */
class Compiler implements CompilerImpl
{
    protected static $rawTag=['{{!','}}'];
    protected static $echoTag=['{{','}}'];
    protected static $hookTag=['{:','}'];
    protected static $commentTag=['{--','--}'];
    protected static $strTransTag=['{=','}'];
    protected static $rawTransTag=['@{','}'];
    const Template='suda\template\compiler\suda\Template';
    protected static $template=self::Template;
    /**
     * 附加模板命令
     *
     * @var array
     */
    protected static $command=[];
    
    public static function setBase(string $tpl=self::Template)
    {
        self::$template=$tpl;
    }

    // 编译文本
    public function compileText(string $text)
    {
        Hook::exec('template:SudaCompiler:init', [$this]);
        $result='';
        foreach (token_get_all($text) as $token) {
            if (is_array($token)) {
                list($tag, $content) = $token;
                // 所有将要编译的文本
                // 跳过各种的PHP
                if ($tag == T_INLINE_HTML) {
                    $content=self::compileString($content);
                    $content=self::compileCommand($content);
                    $content=self::echoValue($content);
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
     * @param $input
     * @return mixed
     */
    public function compile(string $name, string $input, string $output)
    {
        debug()->time('compile '.$name);
        if (!Storage::exist($input)) {
            debug()->warning(__('compile error:no sorce file => %s %s', $name, $input));
            return false;
        }
        $content= $this->compileText(Storage::get($input));
        if (!Storage::isDir($dir=dirname($output))) {
            Storage::mkdirs(dirname($output));
        }
        $classname=Manager::className($name);
        preg_match('/^((?:[a-zA-Z0-9_\-.]+\/)?[a-zA-Z0-9_\-.]+)(?::([^:]+))?(?::(.+))?$/', $name, $match);
        $module = isset($match[3])?$match[1].(isset($match[2])?':'.$match[2]:''):$match[1];
        $content='<?php if (!class_exists("'.$classname.'", false)) { class '.$classname.' extends '.self::$template.' { protected $name="'. addslashes($name) .'";protected $module="'.addslashes($module).'"; protected $source="'. addslashes($input).'";protected function _render_template() {  ?>'.$content.'<?php }} } return ["class"=>"'.$classname.'","name"=>"'.addslashes($name).'","source"=>"'.addslashes($input).'","module"=>"'.addslashes($module).'"]; ';
        Storage::put($output, $content);
        debug()->timeEnd('compile '.$name);
        $syntax=Manager::checkSyntax($output, $classname);
        if ($syntax !== true) {
            if (!conf('debug')) {
                storage()->delete($output);
            }
            if ($syntax instanceof \Exception || $syntax instanceof \Error) {
                throw new \suda\core\Exception(new \ErrorException(__('compile error: %s near line %d', $syntax->getMessage(), $syntax->getLine()), $syntax->getCode(), conf('exception.compileError', true)?E_ERROR:E_WARNING , $input, $syntax->getLine()), 'TemplateError');
            }
        }
        return $syntax===true?:$syntax;
    }

    public function render(string $viewfile, ?string $name =null)
    {
        if (storage()->exist($viewfile)) {
            $templateInfo =  include $viewfile;
            $classname = $templateInfo['class'];
            return $template=new $classname;
        } else {
            throw new \suda\exception\KernelException(__('template %s is not ready!', $name ?? $viewfile));
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

    private function compileString(string $str)
    {
        $callback=function ($match) {
            if (self::hasCommand(ucfirst($match[1]))) {
                $match[0]=self::buildCommand($match[1], $match[3] ?? '');
            } elseif (method_exists($this, $method = 'parse'.ucfirst($match[1]))) {
                $match[0] = $this->$method($match[3] ?? '');
            }
            return isset($match[3]) ? $match[0] : $match[0].$match[2];
        };
        // \x{4e00}-\x{9aff} 为中文字符集范围
        return preg_replace_callback('/\B@([\w\x{4e00}-\x{9aff}]+)(\s*)(\( ( (?>[^()]+) | (?3) )* \) )? /ux', $callback, $str);
    }

    private function compileCommand(string $str)
    {
        $echo=sprintf('/(?<!!)%s\s*(.+?)\s*%s/', preg_quote(self::$echoTag[0]), preg_quote(self::$echoTag[1]));
        $rawecho=sprintf('/(?<!!)%s\s*(.+?)\s*%s/', preg_quote(self::$rawTag[0]), preg_quote(self::$rawTag[1]));
        $comment=sprintf('/(?<!!)%s\s*(.+?)\s*%s/', preg_quote(self::$commentTag[0]), preg_quote(self::$commentTag[1]));
        $hook=sprintf('/(?<!!)%s\s*(.+?)\s*%s/', preg_quote(self::$hookTag[0]), preg_quote(self::$hookTag[1]));
        $strTransTag=sprintf('/(?<!!)%s\s*(.+?)\s*%s/', preg_quote(self::$strTransTag[0]), preg_quote(self::$strTransTag[1]));
        $rawTransTag=sprintf('/(?<!!)%s\s*(.+?)\s*%s/', preg_quote(self::$rawTransTag[0]), preg_quote(self::$rawTransTag[1]));
        return self::echoValue(preg_replace(
            [$rawecho, $echo, $comment, $hook,$strTransTag,$rawTransTag],
            ['<?php echo $1; ?>', '<?php echo htmlspecialchars(__($1)); ?>', '<?php /* $1 */ ?>', '<?php $this->execGlobalHook("$1"); ?>','<?php echo htmlspecialchars(__("$1")); ?>','<?php echo htmlspecialchars($1); ?>'],
            $str
        ));
    }

    protected function echoValue($var)
    {
        // 任意变量名: 中文点下划线英文数字
        return preg_replace_callback('/\B[$](\?)?[:]([.\w\x{4e00}-\x{9aff}]+)(\s*)(\( ( (?>[^()]+) | (?3) )* \) )?/ux', [$this,'echoValueCallback'], $var);
    }
    
    protected function echoValueCallback($matchs)
    {
        $name=$matchs[2];
        if ($matchs[1]==='?') {
            return '$this->has("'.$name.'")';
        }
        if (isset($matchs[4])) {
            preg_match('/\((.+)\)/', $matchs[4], $v);
            if (isset($v[1])) {
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
        preg_match('/\((.+)\)/', $exp, $v);
        $name=trim($v[1], '"\'');
        return "<?php echo suda\\template\\Manager::file('{$name}',\$this) ?>";
    }

    protected function parse_($exp)
    {
        return "<?php echo __$exp; ?>";
    }

    // IF 语句
    protected function parseIf($exp)
    {
        return "<?php if{$exp}: ?>";
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
        preg_match('/\((.+)\)/', $exp, $v);
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        return "<?php suda\\template\\Manager::include('{$name}',\$this)->echo(); ?>";
    }

    protected function parseExtend($exp) {
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
        preg_match('/^\((.+?)\)$/', $exp, $match);
        if (isset($match[1])&&$match[1]) {
            $match[1]=trim($match[1], '"\'');
            return '<?php echo suda\\template\\Manager::assetServer(\''.Manager::getStaticAssetPath(trim($match[1])).'\');?>';
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
