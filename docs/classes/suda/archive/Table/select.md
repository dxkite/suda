# Table::select
选择列
> *文件信息* suda\archive\Table.php: 25~567
## 所属类 

[Table](../Table.md)

## 可见性

  public  
## 说明


用于提供对数据表的操作

## 参数

 
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
 | wants |  [type] | 无 |  想要查询的列 |
 | where |  [type] | 无 |  查询条件 |
 | whereBinder |  array | Array |  查询条件的值 |
 | page |  int | null |  分页页码 |
 | row |  int | 10 |  分页行 |
 | offset |  bool |  |  直接偏移 |
## 返回值
 
类型：RawQuery
无
## 例子


相当于 select 语句，返回一个 SQLQuery类

查询：取一列name，当id为2的时候

```php
$table->select(['name'],['id'=>2])->fetch();
```

查询：取所有列name，当 status = 2的时候

```php
$table->select(['name'],['status'=>2])->fetchAll();
```

查询：取多列，当 id >2 的时候

```php
$table->select(['name'],'id > :id',['id'=>2])->fetchAll();
```