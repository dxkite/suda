<?php  class Classa5162b8560347a01cea77b2c12c3b412 extends suda\template\compiler\suda\Template { protected $name="25619a61a736abe9fd1027dafb86f137";protected $module=""; protected function _render_template() {  ?># 文档清单

> **注：** 文档由程序自动生成

- suda <?php echo htmlspecialchars(__(SUDA_VERSION)); ?> 
- <?php echo htmlspecialchars(__(date('Y-m-d H:i:s'))); ?>


## 函数列表 
| 函数名 | 说明 |
|------|-----|  
<?php foreach($this->get("functions")as $name => $info): ?>| [<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($name)); ?>.md) |  <?php echo $info['functionDoc']; ?>  |
<?php endforeach; ?><?php }}