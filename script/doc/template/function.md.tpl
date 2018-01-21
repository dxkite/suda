<?php  class Class0f568d3c3d2ea0cdd4c6dffbcd41812d extends suda\template\compiler\suda\Template { protected $name="3087eff0ca8ef71fab9957305ac4df4e";protected $module=""; protected function _render_template() {  ?># 函数 `<?php echo htmlspecialchars(__($this->get("functionName"))); ?>`

> *文件信息* <?php echo htmlspecialchars(__($this->get("fileName",'未知文件'))); ?>: <?php echo htmlspecialchars(__($this->get("lineStart",'未知'))); ?>~<?php echo htmlspecialchars(__($this->get("lineEnd",'未知'))); ?>


<?php echo htmlspecialchars(__($this->get("functionDoc",'该函数暂时无注释文档'))); ?>



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