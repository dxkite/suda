#  TableAccess 

> *文件信息* suda\archive\TableAccess.php: 13~407


表创建器


## 描述



用于创建和数据表的链接，如果表不存在则创建
 
## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
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
 |  public  |[__construct](TableAccess/__construct.md) |  |
 |  public  |[createTable](TableAccess/createTable.md) | 创建数据表 |
 |  public  |[getCreateSql](TableAccess/getCreateSql.md) |  |
 |  public  |[getCreator](TableAccess/getCreator.md) |  |
 |abstract  protected  |[onBuildCreator](TableAccess/onBuildCreator.md) |  |
 |  public  |[begin](TableAccess/begin.md) |  |
 |  public  |[commit](TableAccess/commit.md) |  |
 |  public  |[rollBack](TableAccess/rollBack.md) |  |
 |  public  |[truncate](TableAccess/truncate.md) | 清空数据表 |
 |  public  |[drop](TableAccess/drop.md) | 删除数据表 |
 |  public  |[export](TableAccess/export.md) | 导出数据到文件 |
 |  public  |[import](TableAccess/import.md) | 从导出文件中恢复数据 |
 |  protected  |[checkPrimaryKey](TableAccess/checkPrimaryKey.md) |  |
 |  protected  |[checkFields](TableAccess/checkFields.md) | 检查参数列 |
 |  public  |[getPrimaryKey](TableAccess/getPrimaryKey.md) | 获取主键 |
 |  public  |[setPrimaryKey](TableAccess/setPrimaryKey.md) | 设置主键 |
 |  public  |[setTableName](TableAccess/setTableName.md) | 设置表名 |
 |  public  |[getTableName](TableAccess/getTableName.md) | 获取表名 |
 |  public  |[setFields](TableAccess/setFields.md) | 设置表列 |
 |  public  |[getFields](TableAccess/getFields.md) | 获取全部的列 |
 |  protected  |[initFromTable](TableAccess/initFromTable.md) | 从数据表创建器创建数据表 |
 |  protected  |[initTableFields](TableAccess/initTableFields.md) | 初始化数据表字段 |
 |  protected  |[initFromDatabase](TableAccess/initFromDatabase.md) | 从数据表创建字段 |
 |  protected  |[cacheDbInfo](TableAccess/cacheDbInfo.md) |  |
 |  protected  |[getDataStringLimit](TableAccess/getDataStringLimit.md) | 获取导出数据 |
## 例子

example