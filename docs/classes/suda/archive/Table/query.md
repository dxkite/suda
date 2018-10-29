# Table::query
原始查询查询
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
| query |  string | 无 | 无 |
| binds |  array | Array | 无 |
| scroll |  bool |  | 无 |

## 返回值
类型：RawQuery
无

## 例子


请尽量避免使用此函数
其中 #{user} 表示user表，加上 #{} 框架会自动处理浅醉

```php
$table->query('select * from #{user} where id > :id',['id'=>2]);
```

可以使用 @table@ 代指本表

```php
$table->query('select * from #{@table@} where id > :id',['id'=>2]);
```