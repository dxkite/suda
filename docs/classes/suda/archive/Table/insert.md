# Table::insert
插入一行记录
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
| values |  array | 无 |  待插入的值 |

## 返回值
类型：int
 插入影响的行数

## 例子


如果数据表中有name字段和value字段，那么可以通过如下方式插入一条记录

```php
$table->insert(['name'=>$name,'value'=>$value]);
```