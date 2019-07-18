<?php
namespace suda\application\template\compiler;

use Exception;
use function preg_replace_callback;
use function str_replace;

/**
 * 可执行命令表达式
 *
 */
class Compiler
{
    /**
     * 定义的标签
     *
     * @var array
     */
    protected $tag = [
        'raw' => ['{{!', '}}', '<?php echo $code; ?>'],
        'comment' => ['{--', '--}', '<?php /* $code */ ?>'],
    ];

    /**
     * 编译的TAG
     *
     * @var Tag[]
     */
    protected $tags=[];

    /**
     * 命令对象
     *
     * @var CommandInterface[]
     */
    protected $commands=[];

    public function init()
    {
        $this->registerCommand(new Command);
        foreach ($this->tag as $name => $value) {
            $this->registerTag(new Tag($name, $value[0], $value[1], $value[2]));
        }
    }

    /**
     * 注册命令
     *
     * @param CommandInterface $command
     * @return void
     */
    public function registerCommand(CommandInterface $command)
    {
        $this->commands[] = $command;
    }

    /**
     * 注册标记
     *
     * @param Tag $tag
     * @return void
     */
    public function registerTag(Tag $tag)
    {
        $this->tags[$tag->getName()] =$tag;
    }

    /**
     * 编译
     *
     * @param string $text 代码文本
     * @param array $tagConfig 标签配置
     * @return string
     * @throws Exception
     */
    public function compileText(string $text, array $tagConfig = []):string
    {
        $this->applyTagConfig($tagConfig);
        $result  = '';
        foreach (token_get_all($text) as $token) {
            if (is_array($token)) {
                list($tag, $content) = $token;
                // 所有将要编译的文本
                // 跳过各种的PHP
                if ($tag == T_INLINE_HTML) {
                    $content=$this->processTags($content);
                    $content=$this->processCommands($content);
                }
                $result .= $content;
            } else {
                $result .= $token;
            }
        }
        return $result;
    }

    protected function applyTagConfig(array $config)
    {
        foreach ($config as $name => $tagConfig) {
            if (array_key_exists($name, $this->tags)) {
                $this->tags[$name]->setConfig($tagConfig);
            }
        }
    }


    protected function processTags(string $text):string
    {
        foreach ($this->tags as $tag) {
            $pregExp = sprintf('/(!)?%s\s*(.+?)\s*%s/', preg_quote($tag->getOpen()), preg_quote($tag->getClose()));
            $text = preg_replace_callback($pregExp, function ($match) use ($tag) {
                if ($match[1] === '!') {
                    return substr($match[0], 1);
                } else {
                    return $tag->compile($match[2]);
                }
            }, $text);
        }
        return $text;
    }

    /**
     * @param string $text
     * @return string
     * @throws Exception
     */
    protected function processCommands(string $text):string
    {
        $pregExp ='/\B\@(\!)?([\w\x{4e00}-\x{9aff}]+)(\s*)(\( ( (?>[^()]+) | (?4) )* \) )? /ux';
        $code = preg_replace_callback($pregExp, [$this,'doMatchCommand'], $text);
        $error = preg_last_error();
        if ($error !== PREG_NO_ERROR) {
            throw new Exception($error);
        }
        return $code;
    }

    protected function doMatchCommand($match)
    {
        if (count($match) >= 5) {
            list($input, $ignore, $name, $space, $params) = $match;
        } else {
            list($input, $ignore, $name, $space) = $match;
            $params = '';
        }
        if ($ignore ==='!') {
            return str_replace('@!', '@', $input);
        } else {
            foreach ($this->commands as $command) {
                if ($command->has($name)) {
                    $code = $command->parse($name, $params);
                    return empty($params) ? $code : $code.$space;
                }
            }
            return $input;
        }
    }
}
