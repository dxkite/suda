<?php  class Class0a0e12ac4b46505b1d2021786133deae extends suda\template\compiler\suda\Template { protected $name="e2ea98f42e5f4edaab3e3ac104887c9c";protected $module=""; protected function _render_template() {  ?># 文档清单

> **注：** 文档由程序自动生成

- suda <?php echo htmlspecialchars(__(SUDA_VERSION)); ?> 
- <?php echo htmlspecialchars(__(date('Y-m-d H:i:s'))); ?>


## 函数列表 

| 函数名 | 说明 |
|------|-----|  
<?php foreach($this->get("functions")as $name => $info): ?>| [<?php echo htmlspecialchars(__($name)); ?>](functions/<?php echo htmlspecialchars(__($name)); ?>.md) |  <?php echo $info['functionDoc']; ?>  |
<?php endforeach; ?>



## 类列表

| 类名 | 说明 |
|------|-----|
<?php foreach($this->get("classes")as $name => $info): ?>|[<?php echo htmlspecialchars(__($name)); ?>](classes/<?php echo htmlspecialchars(__(docme\Docme::realPath($name))); ?>.md) | <?php echo $info['classDoc']; ?> |
<?php endforeach; ?><?php }}