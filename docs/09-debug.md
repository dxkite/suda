# 调试输出

框架提供了一套调试用的函数，输出内置变量到日志中，函数接口遵循 `PSR3` 日志接口规则

## 使用

日志调试输出对象可以使用 `\suda\application\Application->debug()` 获取，使用：

```php
$application->debug()->info('something {name}', ['name' => 'dxkite', 'value' => $application]);
```

输出的日志内容如下：

```
0.0632278919 - 1.18 MB [info] file:line something dxkite
value = suda\application\Application {}
```
