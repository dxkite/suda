#  类 Table

数据表抽象对象
用于提供对数据表的操作



## 变量


## 方法


- [public - __construct](Table/__construct.md)
    
- [public - insert](Table/insert.md)
    插入一行记录
- [public - insertValue](Table/insertValue.md)
    插入一行记录
- [public - getByPrimaryKey](Table/getByPrimaryKey.md)
    通过主键查找元素
主键的值可以为关联数组或单独的一个值
查询成功返回查询成功的列，失败返回false

- [public - updateByPrimaryKey](Table/updateByPrimaryKey.md)
    通过主键更新元素

- [public - deleteByPrimaryKey](Table/deleteByPrimaryKey.md)
    通过主键删除元素

- [public - search](Table/search.md)
    
- [public - list](Table/list.md)
    分页列出元素

- [public - listWhere](Table/listWhere.md)
    条件列出元素

- [public - update](Table/update.md)
    根据条件更新列

- [public - select](Table/select.md)
    根据条件删除列

- [public - query](Table/query.md)
    纯查询

- [public - delete](Table/delete.md)
    根据条件获取列

- [public - getPrimaryKey](Table/getPrimaryKey.md)
    获取主键

- [public - setPrimaryKey](Table/setPrimaryKey.md)
    设置主键

- [public - setTableName](Table/setTableName.md)
    设置表名

- [public - getTableName](Table/getTableName.md)
    获取表名

- [public - setFields](Table/setFields.md)
    设置表列

- [public - getFields](Table/getFields.md)
    获取全部的列

- [public - setWants](Table/setWants.md)
    设置想要的列

- [public - getWants](Table/getWants.md)
    获取设置了的列

- [public - count](Table/count.md)
    计数

- [public - order](Table/order.md)
    排序

- [public - createTable](Table/createTable.md)
    
- [public - getCreateSql](Table/getCreateSql.md)
    
- [public - begin](Table/begin.md)
    
- [public - commit](Table/commit.md)
    
- [public - rollBack](Table/rollBack.md)
    
- [public - truncate](Table/truncate.md)
    清空数据表

- [public - drop](Table/drop.md)
    删除数据表

- [public - export](Table/export.md)
    导出数据到文件

- [public - import](Table/import.md)
    从导出文件中恢复数据

- [protected - checkPrimaryKey](Table/checkPrimaryKey.md)
    
- [protected - checkFields](Table/checkFields.md)
    检查参数列

- [protected - checkFieldsType](Table/checkFieldsType.md)
    检查参数列

- [protected - checkField](Table/checkField.md)
    
- [protected - checkValueType](Table/checkValueType.md)
    
- [protected - onBuildCreator](Table/onBuildCreator.md)
    
- [protected - initFromTable](Table/initFromTable.md)
    
- [protected - initTableFields](Table/initTableFields.md)
    
- [protected - initFromDatabase](Table/initFromDatabase.md)
    
- [protected - cacheDbInfo](Table/cacheDbInfo.md)
    
- [protected - strify](Table/strify.md)
    
- [protected - _order](Table/_order.md)
    
- [protected - getDataString](Table/getDataString.md)
    获取数据SQL字符串


