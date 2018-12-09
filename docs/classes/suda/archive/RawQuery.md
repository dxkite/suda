#  RawQuery 

> *文件信息* suda\archive\RawQuery.php: 29~457


数据库查询方案，提供原始查询方案


## 描述




 
## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
 | protected    | connection | | 
 | protected    | object | | 
 | protected    | stmt | | 
 | protected    | query | 查询语句| 
 | protected    | values |  模板值| 
 | protected    | scroll | | 
 | protected    | database | 使用的数据库| 
 | protected    | dbchange | | 
## 方法

 
| 可见性 | 方法名 | 说明 |
|--------|-------|------|
 |  public  |[__construct](RawQuery/__construct.md) | 构造查询 |
 |  public  |[getConnection](RawQuery/getConnection.md) |  |
 |  public  |[setConnection](RawQuery/setConnection.md) |  |
 |  public  |[fetch](RawQuery/fetch.md) | 获取查询结果的一列 |
 |  public  |[fetchObject](RawQuery/fetchObject.md) | 获取查询结果的一列，并作为类对象 |
 |  public  |[fetchAll](RawQuery/fetchAll.md) | 获取全部的查询结果 |
 |  public  |[exec](RawQuery/exec.md) | 运行SQL语句 |
 |  public  static|[value](RawQuery/value.md) | 生成一个数据输入值 |
 |  public  |[values](RawQuery/values.md) | SQL语句模板绑定值 |
 |  public  |[query](RawQuery/query.md) | 生成一条查询语句 |
 |  public  |[use](RawQuery/use.md) | 切换使用的数据表 |
 |  public  |[error](RawQuery/error.md) | 获取语句查询错误 |
 |  public  |[erron](RawQuery/erron.md) | 获取语句查询错误编号 |
 |  public  |[lastInsertId](RawQuery/lastInsertId.md) | 获取最后一次插入的主键ID（用于自增值 |
 |  public  |[begin](RawQuery/begin.md) | 事务系列，开启事务 |
 |  public  |[beginTransaction](RawQuery/beginTransaction.md) | 事务系列，开启事务 |
 |  public  |[commit](RawQuery/commit.md) | 事务系列，提交事务 |
 |  public  |[rollBack](RawQuery/rollBack.md) | 事务系列，撤销事务 |
 |  public  |[quote](RawQuery/quote.md) |  |
 |  public  |[arrayQuote](RawQuery/arrayQuote.md) |  |
 |  public  |[object](RawQuery/object.md) | 添加列处理类 |
 |  protected  |[__dataTransfrom](RawQuery/__dataTransfrom.md) | 转换函数；统一处理数据库输入输出 |
## 例子

example