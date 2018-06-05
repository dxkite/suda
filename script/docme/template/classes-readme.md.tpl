<?php  class Class68487c6ddb9ecc3b25b36212f3efcf5e extends suda\template\compiler\suda\Template { protected $name="31646491d2c22c05a313ffb9245b026f";protected $module=""; protected function _render_template() {  ?># 文档清单

> **注：** 文档由程序自动生成

- suda <?php echo htmlspecialchars(__(SUDA_VERSION)); ?> 
- <?php echo htmlspecialchars(__(date('Y-m-d H:i:s'))); ?>



## 类列表

| 类名 | 说明 |
|------|-----|
<?php foreach($this->get("classes")as $name => $info): ?>|[<?php echo htmlspecialchars(__($name)); ?>](classes/<?php echo htmlspecialchars(__(docme\Docme::realPath($name))); ?>.md) | <?php echo $info['classDoc']; ?> |
<?php endforeach; ?><?php }}