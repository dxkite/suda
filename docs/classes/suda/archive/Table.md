#  Table 

> *文件信息* suda\archive\Table.php: 25~567


数据表抽象对象


## 描述




用于提供对数据表的操作
## 常量列表
| 常量名  |  值|
|--------|----|
|ORDER_ASC | 0 | 
|ORDER_DESC | 1 | 


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected    | statement | | 
| protected    | wants | | 
| protected    | orderField | | 
| protected    | order | | 
| protected    | connection | | 
| protected    | fields | | 
| protected    | primaryKey | | 
| protected    | tableName | | 
| protected    | cachePath | | 
| protected    | creator | | 
| protected    | allFields | | 
| protected    | exportFields | 设置导出列大小| 
| protected    | exportBlockSize | 设置导出数据分块大小| 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  |[__construct](Table/__construct.md) |  |
|  public  |[insert](Table/insert.md) | 插入一行记录 |
|  public  |[insertValue](Table/insertValue.md) | 按照表顺序插入一行记录 |
|  public  |[getByPrimaryKey](Table/getByPrimaryKey.md) | 通过主键查找元素 |
|  public  |[updateByPrimaryKey](Table/updateByPrimaryKey.md) | 通过主键更新元素 |
|  public  |[deleteByPrimaryKey](Table/deleteByPrimaryKey.md) | 通过主键删除元素 |
|  public  |[search](Table/search.md) | 根据字段搜索 |
|  public  |[searchWhere](Table/searchWhere.md) | 搜索指定字段 |
|  public  |[searchWhereCount](Table/searchWhereCount.md) | 通知搜索指定字段的个数 |
|  public  |[list](Table/list.md) | 分页列出元素 |
|  public  |[listWhere](Table/listWhere.md) | 条件列出元素 |
|  public  |[update](Table/update.md) | 根据条件更新列 |
|  public  |[select](Table/select.md) | 选择列 |
|  public  |[query](Table/query.md) | 原始查询查询 |
|  public  |[delete](Table/delete.md) | 根据条件删除列 |
|  public  |[setWants](Table/setWants.md) | 设置想要的列 |
|  public  |[getWants](Table/getWants.md) | 获取设置了的列 |
|  public  |[count](Table/count.md) | 计数 |
|  public  |[order](Table/order.md) | 排序 |
|  protected  static|[strify](Table/strify.md) |  |
|  protected  |[genOrderBy](Table/genOrderBy.md) |  |
|  public  |[createTable](Table/createTable.md) | 创建数据表 |
|  public  |[getCreateSql](Table/getCreateSql.md) |  |
|  public  |[getCreator](Table/getCreator.md) |  |
|abstract  protected  |[onBuildCreator](Table/onBuildCreator.md) |  |
|  public  |[begin](Table/begin.md) |  |
|  public  |[commit](Table/commit.md) |  |
|  public  |[rollBack](Table/rollBack.md) |  |
|  public  |[truncate](Table/truncate.md) | 清空数据表 |
|  public  |[drop](Table/drop.md) | 删除数据表 |
|  public  |[export](Table/export.md) | 导出数据到文件 |
|  public  |[import](Table/import.md) | 从导出文件中恢复数据 |
|  protected  |[checkPrimaryKey](Table/checkPrimaryKey.md) |  |
|  protected  |[checkFields](Table/checkFields.md) | 检查参数列 |
|  public  |[getPrimaryKey](Table/getPrimaryKey.md) | 获取主键 |
|  public  |[setPrimaryKey](Table/setPrimaryKey.md) | 设置主键 |
|  public  |[setTableName](Table/setTableName.md) | 设置表名 |
|  public  |[getTableName](Table/getTableName.md) | 获取表名 |
|  public  |[setFields](Table/setFields.md) | 设置表列 |
|  public  |[getFields](Table/getFields.md) | 获取全部的列 |
|  protected  |[initFromTable](Table/initFromTable.md) | 从数据表创建器创建数据表 |
|  protected  |[initTableFields](Table/initTableFields.md) | 初始化数据表字段 |
|  protected  |[initFromDatabase](Table/initFromDatabase.md) | 从数据表创建字段 |
|  protected  |[cacheDbInfo](Table/cacheDbInfo.md) |  |
|  protected  |[getDataStringLimit](Table/getDataStringLimit.md) | 获取导出数据 |
 

## 例子

example