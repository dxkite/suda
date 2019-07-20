<?php
namespace suda\application\template;

use suda\application\template\compiler\Command;

/**
 * Class ModuleTemplateCommand
 * @package suda\application\template
 */
class ModuleTemplateCommand extends Command
{
    /**
     * @param string $data
     * @return string
     */
    protected function parseU(string $data)
    {
        if (strlen(trim($data)) === 0) {
            return '<?php echo $this->getUrl(); ?>';
        }
        return '<?php echo $this->getUrl'.$data.'; ?>';
    }

    /**
     * @param $content
     * @return string
     */
    protected function parseStatic($content)
    {
        $content = strlen(trim($content)) === 0 ?'()':$content;
        return '<?php echo $this->getStaticModulePrefix'.$content.'; ?>';
    }

    /**
     * @param $content
     * @return string
     */
    protected function parseE($content)
    {
        $content = strlen(trim($content)) === 0 ?'()':$content;
        return '<?php echo $this->application->_'.$content.'; ?>';
    }
}
