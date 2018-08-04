<?php  class Class4b5188a17937cc30ff0ad7f5b03ace4d extends suda\template\compiler\suda\Template { protected $name="496ba4f6fcf577f75c4f18c9949404a0";protected $module=""; protected function _render_template() {  ?>#  <?php echo htmlspecialchars(__($this->get("className"))); ?> 

> *文件信息* <?php echo htmlspecialchars(__($this->get("fileName",'未知文件'))); ?>: <?php echo htmlspecialchars(__($this->get("lineStart",'未知'))); ?>~<?php echo htmlspecialchars(__($this->get("lineEnd",'未知'))); ?>



<?php echo htmlspecialchars(__($this->get("classDoc",'无说明信息'))); ?>



## 描述



<?php echo $this->get("document",'该类暂时无说明'); ?>

<?php if(count($this->get("constants"))): ?>
## 常量列表
| 常量名  |  值|
|--------|----|
<?php foreach($this->get("constants")as $name => $value): ?>|<?php echo htmlspecialchars(__($name)); ?> | <?php echo $value; ?> | 
<?php endforeach; ?>
<?php endif; ?>


<?php if(count($this->get("properties"))): ?>
## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
<?php foreach($this->get("properties")as $name => $info): ?>| <?php echo htmlspecialchars(__($info['visibility'].' ')); ?> <?php echo htmlspecialchars(__($info['static'])); ?>  | <?php echo htmlspecialchars(__($name)); ?> | <?php echo htmlspecialchars(__($info['docs']??'无')); ?>| 
<?php endforeach; ?>
<?php endif; ?>

## 方法

<?php if(count($this->get("methods",[]))): ?>
| 可见性 | 方法名 | 说明 |
|--------|-------|------|
<?php foreach($this->get("methods")as $name => $info ): ?>|<?php echo htmlspecialchars(__($info['abstract'] .' ')); ?> <?php echo htmlspecialchars(__($info['visibility'].' ')); ?> <?php echo htmlspecialchars(__($info['static'])); ?>|[<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($this->get("className"))); ?>/<?php echo htmlspecialchars(__($name)); ?>.md) | <?php echo htmlspecialchars(__($info['functionDoc']??'无')); ?> |
<?php endforeach; ?> <?php else: ?>
无方法
<?php endif; ?>


## 例子

<?php echo $this->get("example"); ?><?php }}