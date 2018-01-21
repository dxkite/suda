<?php  class Classb9aef97055e0826649a0192a7006f8ba extends suda\template\compiler\suda\Template { protected $name="2b3b840f9b3cf5bf1e8616b66f6bbff1";protected $module=""; protected function _render_template() {  ?># 文档清单

> **注：** 文档由程序自动生成

## 函数列表 

<?php foreach($this->get("functions")as $name => $info): ?>

### [<?php echo htmlspecialchars(__($name)); ?>](functions/<?php echo htmlspecialchars(__($name)); ?>.md)
 <?php echo $info['functionDoc']; ?> 
<?php endforeach; ?>




## 类列表

<?php foreach($this->get("classes")as $name => $info): ?>

### [<?php echo htmlspecialchars(__($name)); ?>](classes/<?php echo htmlspecialchars(__(doc\Summary::realPath($name))); ?>.md)
<?php echo $info['classDoc']; ?> 
<?php endforeach; ?><?php }}