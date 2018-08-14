<?php  class Classf6f012ebe9831bee078ba4b2adf426d4 extends suda\template\compiler\suda\Template { protected $name="818f3b386952dd7bb41c7d81ffff3c61";protected $module=""; protected function _render_template() {  ?># Summary

* [内置函数](functions/README.md)
<?php foreach($this->get("functions")as $name => $info): ?>    * [<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($this->get("docme")->exportPath($info))); ?>) 
<?php endforeach; ?>
* [核心类参考](classes/README.md)
<?php foreach($this->get("classes")as $name => $info): ?>    * [<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($this->get("docme")->exportPath($info['path']))); ?>)
<?php foreach($info['methods'] as $method => $path): ?>        * [<?php echo htmlspecialchars(__($method)); ?>](<?php echo htmlspecialchars(__($this->get("docme")->exportPath($path))); ?>)
<?php endforeach; ?>
<?php endforeach; ?>
<?php }}