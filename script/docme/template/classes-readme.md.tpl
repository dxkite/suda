<?php  class Class0b779953b3e7be187f8b489243dcf736 extends suda\template\compiler\suda\Template { protected $name="574e2dbc7e6aeb4d55ca6ea718b35516";protected $module=""; protected function _render_template() {  ?># 文档清单

> **注：** 文档由程序自动生成

- suda <?php echo htmlspecialchars(__(SUDA_VERSION)); ?> 
- <?php echo htmlspecialchars(__(date('Y-m-d H:i:s'))); ?>



## 类列表

| 类名 | 说明 |
|------|-----|
<?php foreach($this->get("classes")as $name => $info): ?>|[<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__(docme\Docme::realPath($name))); ?>.md) | <?php echo $info['classDoc']; ?> |
<?php endforeach; ?><?php }}