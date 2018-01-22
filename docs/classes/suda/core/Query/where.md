# Query::where

在数据表总搜索

> *文件信息* suda\core\Query.php: 26~263

## 所属类 

[Query](../Query.md)

## 可见性

 public static

## 说明

提供了数据库的查询方式



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

类型：SQLQuery

无



## 例子

example