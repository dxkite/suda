<?php  class Class7da95941b8390a37cc15f99588b0b455 extends suda\template\compiler\suda\Template { protected $name="55c1cc2edb1c85ff12389cca9648951f";protected $module=""; protected function _render_template() {  ?>#  类 <?php echo htmlspecialchars(__($this->get("className"))); ?>


> *文件信息* <?php echo htmlspecialchars(__($this->get("fileName",'未知文件'))); ?>: <?php echo htmlspecialchars(__($this->get("lineStart",'未知'))); ?>~<?php echo htmlspecialchars(__($this->get("lineEnd",'未知'))); ?>


<?php echo htmlspecialchars(__($this->get("classDoc",'无说明信息'))); ?>


## 描述

<?php echo $this->get("document",'该类暂时无说明'); ?>



## 变量


## 方法

<?php if(count($this->get("methods",[]))): ?>

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
<?php foreach($this->get("methods")as $name => $info ): ?>|<?php echo htmlspecialchars(__($info['abstract'] .' '));  echo htmlspecialchars(__($info['visibility'].' '));  echo htmlspecialchars(__($info['static'])); ?>|[<?php echo htmlspecialchars(__($name)); ?>](<?php echo htmlspecialchars(__($this->get("className"))); ?>/<?php echo htmlspecialchars(__($name)); ?>.md) | <?php echo htmlspecialchars(__($info['functionDoc']??'无')); ?> |
<?php endforeach;  else: ?>

无方法
<?php endif; ?><?php }}