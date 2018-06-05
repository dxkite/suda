<?php  class Class1cc04be653d213d32f1a1ea1fc0abd6c extends suda\template\compiler\suda\Template { protected $name="fcd199f32b6dbf9e361108302c58ffd0";protected $module=""; protected function _render_template() {  ?># 文档清单

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