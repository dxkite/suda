# Query::insert

向数据表中插入一行

> *文件信息* suda\core\Query.php: 26~327

## 所属类 

[Query](../Query.md)

## 可见性

 public static

## 说明

提供了数据库的查询方式



## 参数


| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| table |  string | 无 |  为数据表名，会自动添加数据表前缀。 |
| values |  [type] | 无 |   为插入的值，可以为字符串或者MAP数组。 |
| binds |  array | Array |  为values中出现的模板控制待绑定字符。 |
| object |  [type] | null |  数据库回调对象 |



## 返回值

类型：array|false

 当ID&gt;0时返回ID，否者返回true/false



## 例子

```php
// 建议使用
$id = Query::insert('user',['name'=>$name,'password'=>  $password ,'group'=>$group, 'available'=>false,'email'=>$email,'ip'=>$ip,]);
// 或
$id = Query::insert('user',"(`name`,`password`,`group`,`available`,`email`,`ip`)", ['name'=>$name, 'password'=>  $password ,'group'=>$group,'available'=>false,'email'=>$email,'ip'=>$ip,]
```
以上调用会编译成类似如下模板
```sql
INSERT INTO `mc_user` (`name`,`password`,`group`,`available`,`email`,`ip`) VALUES (:name,:password,:group,:available,:email,:ip);
```