<?php  class Class1a4cb0757f5259ceef363e5a5a4c8575 extends suda\template\compiler\suda\Template { protected $name="a599423370bb55453a91041d79994259";protected $module=""; protected function _render_template() {  ?># Summary

* [内置函数](functions/README.md)
<?php foreach($this->get("functions")as $name => $info): ?>    * [<?php echo htmlspecialchars(__($name)); ?>](functions/<?php echo htmlspecialchars(__($name)); ?>.md) 
<?php endforeach; ?>
* [核心类参考](classes/README.md)
<?php foreach($this->get("classes")as $name => $info): ?>    * [<?php echo htmlspecialchars(__($name)); ?>](classes/<?php echo htmlspecialchars(__(docme\Docme::realPath($name))); ?>.md)
<?php endforeach; ?>
<?php }}