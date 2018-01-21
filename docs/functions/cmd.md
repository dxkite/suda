# 函数 `cmd`

> *文件信息* suda\core\functions.php: 241~244

新建一个命令对象，命令对象可以是一个字符串或者一个数组，也可以是一个匿名包对象
还可以是一个标准可调用的格式的字符串

### 静态方法
```
类名::方法名
```

### 动态方法

```
类名->方法名
```


## 参数


| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| command |  [type] | 无 |  可调用的对象 |
| params |  array | # Error> htmlspecialchars() expects parameter 1 to be string, array given
	Cause By D:\Server\Local\suda\script\doc\template\function.md.tpl:15
 |  调用时的参数 |



## 返回值

类型：suda\tool\Command

 可调用命令对象

