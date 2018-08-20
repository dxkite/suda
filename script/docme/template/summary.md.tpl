<?php  class Classcdf2deef31b57decdb93a8e35e3fa239 extends suda\template\compiler\suda\Template { protected $name="4c43ca935ab3513c5c9a468c7d0b3e6c";protected $module=""; protected function _render_template() {  ?># Summary

* [说明](README.md)
* [内置函数](functions/README.md)
<?php foreach($this->get("functions")as $name => $info): ?>    * [<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($this->get("docme")->exportPath($info))); ?>) 
<?php endforeach; ?>
* [核心类参考](classes/README.md)
<?php foreach($this->get("classes")as $name => $info): ?>    * [<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($this->get("docme")->exportPath($info['path']))); ?>)
<?php foreach($info['methods'] as $method => $path): ?>        * [<?php echo htmlspecialchars(__($method)); ?>](<?php echo htmlspecialchars(__($this->get("docme")->exportPath($path))); ?>)
<?php endforeach; ?>
<?php endforeach; ?>
<?php }}