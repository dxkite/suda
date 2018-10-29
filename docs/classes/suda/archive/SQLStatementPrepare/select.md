# SQLStatementPrepare::select
选择列
> *文件信息* suda\archive\SQLStatementPrepare.php: 24~324
## 所属类 

[SQLStatementPrepare](../SQLStatementPrepare.md)

## 可见性

  public  
## 说明



## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| table |  string | 无 |  数据表名 |
| wants |  [type] | 无 |  为查询的字段，可以为字符串如`&quot;field1,field2&quot;` 或者数组 `[ &quot;field1&quot;,&quot;field2&quot; ]`； 建议使用数组模式。 |
| conditions |  [type] | 无 |  为查询的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。 |
| binds |  array | Array |  查询字符串中绑定的数据 |
| page |  array | null |  分页查询，接受数组 ，格式为： [为分页的页数,每页长度,是否为OFFSET] |
| scroll |  bool |  |  滚动查询，一次取出一条记录 |

## 返回值
类型：RawQuery
无

## 例子


```php
$fetch=Query::select('user_group', 'auths', ' JOIN `#{user}` ON `#{user}`.`id` = :id  WHERE `user` = :id  or `#{user_group}`.`id` =`#{user}`.`group` LIMIT 1;', ['id'=>$uid])->fetch()
```
等价于
```sql
SELECT auths FROM `mc_user_group`  JOIN `mc_user` ON `mc_user`.`id` = :id  WHERE `user` = :id  or `mc_user_group`.`id` =`mc_user`.`group` LIMIT 1;
```