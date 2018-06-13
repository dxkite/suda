<?php  class Class18f035d909eda1e9f5374a8622ce88c7 extends suda\template\compiler\suda\Template { protected $name="fe97626d018e3712cc0fdb38f03c666f";protected $module=""; protected function _render_template() {  ?># 文档清单

> **注：** 文档由程序自动生成

- suda <?php echo htmlspecialchars(__(SUDA_VERSION)); ?> 
- <?php echo htmlspecialchars(__(date('Y-m-d H:i:s'))); ?>


## 函数列表 
| 函数名 | 说明 |
|------|-----|  
<?php foreach($this->get("functions")as $name => $info): ?>| [<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($name)); ?>.md) |  <?php echo $info['functionDoc']; ?>  |
<?php endforeach; ?><?php }}