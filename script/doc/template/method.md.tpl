<?php  class Classd35d0413a63f0337c6b2544715efa344 extends suda\template\compiler\suda\Template { protected $name="249e2d6ccc4269e2daa90e63af947e4d";protected $module=""; protected function _render_template() {  ?># 方法 `<?php echo htmlspecialchars(__($this->get("functionName"))); ?>`

> *文件信息* <?php echo htmlspecialchars(__($this->get("fileName",'未知文件'))); ?>: <?php echo htmlspecialchars(__($this->get("lineStart",'未知'))); ?>~<?php echo htmlspecialchars(__($this->get("lineEnd",'未知'))); ?>


## 所属类 

[<?php echo htmlspecialchars(__($this->get("className"))); ?>](../<?php echo htmlspecialchars(__($this->get("className"))); ?>.md)

## 可见性

<?php echo htmlspecialchars(__($this->get("visibility"))); ?>


## 说明

<?php echo $this->get("functionDoc",'该函数暂时无注释文档'); ?>


## 参数

<?php if(count($this->get("params",[]))): ?>

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
<?php foreach($this->get("params")as $name => $param): ?>| <?php echo htmlspecialchars($name); ?> |  <?php echo htmlspecialchars($param['type'] ??['无']); ?> | <?php echo htmlspecialchars($param['default']??'无'); ?> | <?php echo htmlspecialchars($param['description']??'无'); ?> |
<?php endforeach; ?>

<?php else: ?>

无参数
<?php endif; ?>


## 返回值
<?php if(count($this->get("return",[]))): ?>

类型：<?php echo htmlspecialchars(__($this->get("return")['type'])); ?>


<?php echo htmlspecialchars($this->get("return")['description']); ?>


<?php else: ?>

无返回值
<?php endif; ?><?php }}