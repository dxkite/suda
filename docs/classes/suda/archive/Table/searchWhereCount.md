# Table::searchWhereCount
通知搜索指定字段的个数
> *文件信息* suda\archive\Table.php: 31~944
## 所属类 

[Table](../Table.md)

## 可见性

  public  
## 说明


用于提供对数据表的操作


## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| field |  [type] | 无 | 无 |
| search |  string | 无 | 无 |
| where |  [type] | 无 | 无 |
| bind |  array | Array | 无 |

## 返回值
类型：integer
无

## 例子


搜索的字段必须为字符串
如：
根据name字段搜索值为$name的可能值，搜索 status=1 的所有记录

```php
 $table->searchWhereCount('name',$name,['status'=>1]);
```

如果条件不是等于，则可以用如下：
**注意** 如下中第三个参数的 :status 必须与第四个参数的键名对上

```php
 $table->searchWhereCount('name',$name,' status > :status ',['status'=>1]);
```