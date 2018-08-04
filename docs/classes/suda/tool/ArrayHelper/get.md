# ArrayHelper::get
获取数组元素
> *文件信息* suda\tool\ArrayHelper.php: 23~172
## 所属类 

[ArrayHelper](../ArrayHelper.md)

## 可见性

  public  static
## 说明


设置值， 获取值，导出成文件

## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| array |  array | 无 | 无 |
| name |  string | 无 | 无 |
| def |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
	Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl:25
 | null | 无 |

## 返回值
类型：mixed
 查询的值

## 例子

array_get_value('a.b.c.d',$arr);
返回 $arr['a']['b']['c']['d'];