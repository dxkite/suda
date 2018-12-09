# SQLStatementPrepare::where
在数据表总搜索
> *文件信息* suda\archive\SQLStatementPrepare.php: 24~324
## 所属类 

[SQLStatementPrepare](../SQLStatementPrepare.md)

## 可见性

  public  
## 说明



## 参数

 
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
 | table |  string | 无 |  表名 |
 | wants |  string|array | * |  提取的列 |
 | condithon |  string|array | 1 |  提取的条件 |
 | binds |  array | Array |  模板绑定的值 |
 | page |  array | null |  分页获取 |
 | scroll |  bool |  |  滚动获取 |
## 返回值
 
类型：RawQuery
无
## 例子

如下语句
```php
$fetch=Query::where('user', ['id', 'name', 'email', 'available', 'avatar', 'ip'], '1', [], [$page, $count])->fetchAll();
```
与如下SQL语句等价
```sql
SELECT `id`, `name`,`email`,`available`, `avatar`, `ip` FROM mc_user WHERE 1;
```