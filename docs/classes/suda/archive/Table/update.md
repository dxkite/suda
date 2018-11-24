# Table::update
根据条件更新列
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
| values |  [type] | 无 |  更新的列 |
| where |  [type] | 无 |  条件区域 |
| bind |  array | Array |  扩展条件值 |

## 返回值
类型：integer
无

## 例子


条件可以为键值对也可以为特殊条件

**键值对**

更新 ID 为3 的name 为 $name 的值

```php
$table->update(['name'=>$name],['id'=>3]);
```

**条件**

更新 ID>3 的name 为 $name 的值

```php
$table->update(['name'=>$name],'id > :id ',['id'=>3]);
```