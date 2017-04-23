<?php
namespace suda\template;

use Storage;
use suda\core\Application;
use suda\tool\Value;

class Compiler
{
    protected static $rawTag=['{{!','}}'];
    protected static $echoTag=['{{','}}'];
    protected static $commentTag=['{--','--}'];


    // 编译文本
    public function compileText(string $text)
    {
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
        return $result;
    }

    private function compileString(string $str)
    {
        $callback=function ($match) {
            if (Manager::hasCommand(ucfirst($match[1]))) {
                $match[0]=Manager::buildCommand($match[1], $match[3] ?? '');
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
        $echo=sprintf('/(?<!!)%s\s*(.+?)\s*?%s/', preg_quote(self::$echoTag[0]), preg_quote(self::$echoTag[1]));
        $rawecho=sprintf('/(?<!!)%s\s*(.+?)\s*?%s/', preg_quote(self::$rawTag[0]), preg_quote(self::$rawTag[1]));
        $comment=sprintf('/(?<!!)%s(.+)%s/', preg_quote(self::$commentTag[0]), preg_quote(self::$commentTag[1]));
        return self::echoValue(preg_replace(
            [$rawecho, $echo, $comment],
            ['<?php echo($1) ?>', '<?php echo htmlspecialchars($1) ?>', '<?php /* $1 */ ?>'],
            $str
        ));
    }

    protected static function echoValue($var)
    {
        // 任意变量名(除空格,和字符串界定符号)
        return preg_replace_callback('/\B[$][:]([^()\s\'"]+)(\s*)(\( ( (?>[^()]+) | (?3) )* \) )?/x', function ($matchs) {
            $name=$matchs[1];
            $args=isset($matchs[4])?','.$matchs[4]:'';
            return 'suda\\core\\Response::get("'.$name.'"'.$args.')';
        }, $var);
    }

    protected static function parseValue($var)
    {
        return '<?php echo suda\\core\\Response::get'.self::echoValue($var) .'?>';
    }
    
    protected function parseEcho($exp)
    {
        return "<?php echo htmlspecialchars{$exp} ?>";
    }

    protected function parseData($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        return "<?php suda\\template\\Manager::dataset('{$name}',\$this) ?>";
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
        return "<?php suda\\template\\Manager::include{$exp} ?>";
    }

    protected function parseU($exp)
    {
        return "<?php echo u$exp ?>";
    }

    protected function parseSet($exp)
    {
        return "<?php suda\\core\\Response::get{$exp} ?>";
    }
    protected function parseStatic($exp)
    {
        preg_match('/^\((.+?)\)$/', $exp, $match);
        $module=$match[1]??Application::getActiveModule();
        $module=trim($module, '"\'');
        $path=Manager::prepareResource($module);
        $static_url=Storage::cut($path, APP_PUBLIC);
        $static_url=preg_replace('/[\\\\\/]+/', '/', $static_url);
        return '/'.$static_url;
    }

    
    protected function parseUrl($exp)
    {
        return "<?php echo u{$exp} ?>";
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
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        return '<?php suda\\template\\Manager::hook(\''.$name.'\',function () { ?>';
    }
    
    protected function parseEndInsert()
    {
        return '<?php });?>';
    }
    protected function parseInsert($exp)
    {
        preg_match('/\((.+)\)/', $exp, $v);
        $name=str_replace('\'', '-', trim($v[1], '"\''));
        return "<?php suda\\template\\Manager::exec('{$name}') ?>";
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
