# SQLStatementPrepare::update
更新列
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
| set_fields |  [type] | 无 |  为设置的字段，使用键值数组式设置值。 |
| where |  string | 1 |  为更新的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。 |
| binds |  array | Array |  查询字符串中绑定的数据 |

## 返回值
类型：integer
无

## 例子


```php
Query::update('user_token', 'expire = :time , token=:new_token,value=:refresh', 'id=:id AND UNIX_TIMESTAMP() < `time` + :alive AND value = :value ', ['id'=>$id, 'value'=>$value, 'new_token'=>$new, 'refresh'=>$refresh, 'time'=>time() + $get['beat'], 'alive'=>$get['alive']]);
```
等价于
```sql
UPDATE `mc_user_token` SET expire = :time , token=:new_token,value=:refresh  WHERE id=:id AND UNIX_TIMESTAMP() < `time` + :alive AND value = :value ;
```