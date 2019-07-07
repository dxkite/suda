<?php

namespace suda\application\template;

use Exception;

/**
 * 模板
 */
class Template extends ModuleTemplate
{

    /**
     * 获取模板源路径
     *
     * @return string|null
     */
    public function getSourcePath(): ?string
    {
        if (strlen($this->source) === 0) {
            $path = $this->seekSourcePath();
            if (count($path) === 2) {
                list($this->source, $this->raw) = $path;
            }
        }
        return $this->source;
    }

    /**
     * 包含模板
     *
     * @param string $name
     * @return void
     * @throws Exception
     */
    public function include(string $name)
    {
        $included = new self($name, $this->application, $this->request, $this->module);
        $included->parent = $this;
        $included->value = $this->value;
        echo $included->getRenderedString();
    }

    /**
     * @return array|null
     */
    private function seekSourcePath():?array
    {
        $extArray = [];
        if (array_key_exists('subfix', $this->config)) {
            $extArray[$this->config['subfix']] = $this->config['raw'] ?? false;
        }
        $extArray['.tpl.html'] = false;
        $extArray['.php'] = true;
        $resource = $this->getResource($this->module);
        foreach ($extArray as $ext => $isRaw) {
            $path = $resource->getResourcePath($this->getTemplatePath() . '/' . $this->name . $ext);
            if ($path !== null) {
                return [$path , $isRaw];
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if ($this->isRaw()) {
            return $this->source;
        }
        return parent::getPath();
    }
}
