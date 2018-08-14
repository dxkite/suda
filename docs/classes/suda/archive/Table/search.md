# Table::search
根据字段搜索
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
| field |  [type] | 无 |  搜索的字段 |
| search |  string | 无 |  搜索列 |
| page |  int | null |  页码 |
| rows |  int | 10 |  每页数 |

## 返回值
类型：array|null
无

## 例子


搜索的字段必须为字符串
如：
根据name字段搜索值为$name的可能值

```php
 $table->search('name',$name);
```

如果想要实现分页效果，可以用如下代码：搜索，取第一页，每页10条数据

```php
 $table->search('name',$name,1,10);
```