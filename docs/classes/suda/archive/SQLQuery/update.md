# SQLQuery::update
更新列
> *文件信息* suda\archive\SQLQuery.php: 28~273
## 所属类 

[SQLQuery](../SQLQuery.md)

## 可见性

  public  
## 说明

单列数据查询方案


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