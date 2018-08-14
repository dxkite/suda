# Table::listWhere
条件列出元素
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
| where |  [type] | 无 |  条件描述 |
| binds |  array | Array |  条件附带参数 |
| page |  int | null |  是否分页（页数） |
| rows |  int | 10 |  分页的元素个数 |
| offset |  bool |  |  使用Offset |

## 返回值
类型：array|null
无

## 例子


当不填页码的时候，默认列出所有数据
填入页码时列出对应页
使用条件列出：

等值情况
```php
$table->list(['status'=>1],[],1,10);
```

特殊情况
```php
$table->list('status > :status ',['status'=>1],1,10);
```
**注意** :status 必须后面的键名对上