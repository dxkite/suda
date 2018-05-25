<?php  class Class58c4dc44ba92029deb606e691f948cd8 extends suda\template\compiler\suda\Template { protected $name="aacd319c19cef1654eb1a403f69b97df";protected $module=""; protected function _render_template() {  ?># <?php echo htmlspecialchars(__($this->get("className"))); ?>::<?php echo htmlspecialchars(__($this->get("functionName"))); ?>

<?php echo $this->get("functionDoc"); ?>

> *文件信息* <?php echo htmlspecialchars(__($this->get("fileName"))); ?>: <?php echo htmlspecialchars(__($this->get("lineStart"))); ?>~<?php echo htmlspecialchars(__($this->get("lineEnd"))); ?>

## 所属类 

[<?php echo htmlspecialchars(__($this->get("className"))); ?>](../<?php echo htmlspecialchars(__($this->get("className"))); ?>.md)

## 可见性

<?php echo htmlspecialchars(__($this->get("abstract").' ')); ?> <?php echo htmlspecialchars(__($this->get("visibility").' ')); ?> <?php echo htmlspecialchars(__($this->get("static"))); ?>

## 说明

<?php echo $this->get("document"); ?>


## 参数

<?php if(count($this->get("params"))): ?>
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
<?php foreach($this->get("params")as $name => $param): ?>| <?php echo htmlspecialchars($name); ?> |  <?php echo htmlspecialchars($param['type'] ??['无']); ?> | <?php echo htmlspecialchars($param['default']??'无'); ?> | <?php echo htmlspecialchars($param['description']??'无'); ?> |
<?php endforeach; ?>
<?php else: ?>
无参数
<?php endif; ?>

## 返回值
<?php if(count($this->get("return"))): ?>
类型：<?php echo htmlspecialchars(__($this->get("return")['type'])); ?>

<?php echo htmlspecialchars($this->get("return")['description']); ?>

<?php else: ?>
返回值类型不定
<?php endif; ?>

## 例子

<?php echo $this->get("example"); ?><?php }}