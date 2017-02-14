<?php
namespace suda\template;

use Storage;
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
            if (method_exists($this, $method = 'parse'.ucfirst($match[1]))) {
                $match[0] = $this->$method(isset($match[3])?$match[3]:null);
            }
            return isset($match[3]) ? $match[0] : $match[0].$match[2];
        };
        return preg_replace_callback('/\B@(\w+)(\s*)(\( ( (?>[^()]+) | (?3) )* \) )? /x', $callback, $str);
    }

    private function compileCommand(string $str)
    {
        $echo=sprintf('/(?<!!)%s\s*(.+?)\s*?%s/', preg_quote(self::$echoTag[0]), preg_quote(self::$echoTag[1]));
        $rawecho=sprintf('/(?<!!)%s\s*(.+?)\s*?%s/', preg_quote(self::$rawTag[0]), preg_quote(self::$rawTag[1]));
        $comment=sprintf('/(?<!!)%s(.+)%s/', preg_quote(self::$commentTag[0]), preg_quote(self::$commentTag[1]));
        return preg_replace(
            [$rawecho, $echo, $comment],
            ['<?php echo($1) ?>', '<?php echo htmlspecialchars($1) ?>', '<?php /* $1 */ ?>'],
            $str
        );
    }
    protected function parseEcho($exp)
    {
        return "<?php echo htmlspecialchars{$exp} ?>";
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
    protected function parseInclude($exp, array $includes=[])
    {
        preg_match('/^\(([\'"])(.+)(?1)/', $exp, $match);
        return "<?php suda\\template\\Manager::include('{$match[2]}',\$v->_getVar()) ?>";
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
        return '<?php suda\\template\\Manager::hook('.$v[1].',function () { ?>';
    }
    
    protected function parseEndInsert()
    {
        return '<?php });?>';
    }
    protected function parseInsert($exp)
    {
        return "<?php suda\\template\\Manager::exec($exp) ?>";
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
