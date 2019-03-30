<?php
namespace suda\application\template;

use suda\application\template\compiler\Command;

class ModuleTemplateCommand extends Command
{
    protected function parseU(string $data)
    {
        if (strlen(trim($data)) === 0) {
            return '<?php echo $this->getUrl(); ?>';
        }
        return '<?php echo $this->getUrl'.$data.'; ?>';
    }
    
    protected function parseStatic($content)
    {
        $content = strlen(trim($content)) === 0 ?'()':$content;
        return '<?php echo $this->getStaticModulePrefix'.$content.'; ?>';
    }

    protected function parseFile($content)
    {
        $content = strlen(trim($content)) === 0 ?'()':$content;
        return '<?php echo $this->getModulePrefix'.$content.'; ?>';
    }
}
