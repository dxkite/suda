# Table::searchWhere
搜索指定字段
> *文件信息* suda\archive\Table.php: 31~933
## 所属类 

[Table](../Table.md)

## 可见性

  public  
## 说明


用于提供对数据表的操作


## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| field |  [type] | 无 |  搜索字段 |
| search |  string | 无 |  搜索值 |
| where |  [type] | 无 |  限制搜索条件 |
| bind |  array | Array |  条件值绑定 |
| page |  int | null |  条件页 |
| rows |  int | 10 |  页列 |
| offset |  bool |  |  是否是偏移 |

## 返回值
类型：array|null
无

## 例子


搜索的字段必须为字符串
如：
根据name字段搜索值为$name的可能值，搜索 status=1 的所有记录

```php
 $table->searchWhere('name',$name,['status'=>1]);
```

搜索 status=1 的所有记录,如果想要实现分页效果，可以用如下代码：搜索，取第一页，每页10条数据

```php
 $table->searchWhere('name',$name,['status'=>1],[], 1,10);
```

如果条件不是等于，则可以用如下：
**注意** 如下中第三个参数的 :status 必须与第四个参数的键名对上

```php
 $table->searchWhere('name',$name,' status > :status ',['status'=>1]);
```