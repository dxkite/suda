# Table::insertValue
按照表顺序插入一行记录
> *文件信息* suda\archive\Table.php: 31~911
## 所属类 

[Table](../Table.md)

## 可见性

  public  
## 说明


用于提供对数据表的操作


## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| values |  [type] | 无 |  待插入的值 |

## 返回值
类型：integer
 插入影响的行数

## 例子


如果数据表中有name字段和value字段，且表结构如下

| name | value |
|------|--------|
| dxkite| unlimit |

则可以通过如下插入一条语句

```php
$table->insertValue($name,$value);
```