<?php
namespace suda\application\template;

use suda\application\template\compiler\Tag;
use suda\application\template\compiler\Compiler;

/**
 * 可执行命令表达式
 *
 */
class ModuleTemplateCompiler extends Compiler
{
    /**
     * 定义的标签
     *
     * @var array
     */
    protected $tag = [
        'raw' => ['{{!', '}}', '<?php echo $code; ?>'],
        'comment' => ['{--', '--}', '<?php /* $code */ ?>'],
        'echo' => [ '{{', '}}', '<?php echo htmlspecialchars($this->application->_($code), ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5); ?>' ],
        'string' => ['{=', '}', '<?php echo htmlspecialchars($this->application->_("$code"), ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5); ?>'],
        'raw-string' => ['@{', '}', '<?php echo htmlspecialchars($code, ENT_SUBSTITUTE | ENT_QUOTES | ENT_HTML5); ?>' ],
        'event' => ['{:', '}', '<?php $this->application->event()->exec("$code", [$this]); ?>' ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->registerCommand(new ModuleTemplateCommand);
        foreach ($this->tag as $name => $value) {
            $this->registerTag(new Tag($name, $value[0], $value[1], $value[2]));
        }
    }
}
