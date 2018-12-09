#  SQLQuery 

> *文件信息* suda\archive\SQLQuery.php: 28~273


数据库查询方案，简化数据库查


## 描述



单列数据查询方案

 
## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
 | protected  static  | defaultQuery | | 
 | protected  static  | currentQuery | | 
 | protected    | connection | | 
 | protected    | query | | 
## 方法

 
| 可见性 | 方法名 | 说明 |
|--------|-------|------|
 |  public  |[__construct](SQLQuery/__construct.md) | 构造查询 |
 |  public  |[setConnection](SQLQuery/setConnection.md) |  |
 |  public  |[fetch](SQLQuery/fetch.md) | 获取查询结果的一列 |
 |  public  static|[useQuery](SQLQuery/useQuery.md) |  |
 |  public  static|[resetQuery](SQLQuery/resetQuery.md) |  |
 |  public  static|[currentQuery](SQLQuery/currentQuery.md) |  |
 |  public  |[fetchObject](SQLQuery/fetchObject.md) | 获取查询结果的一列，并作为类对象 |
 |  public  |[fetchAll](SQLQuery/fetchAll.md) | 获取全部的查询结果 |
 |  public  |[exec](SQLQuery/exec.md) | 运行SQL语句 |
 |  public  static|[value](SQLQuery/value.md) | 生成一个数据输入值 |
 |  public  |[values](SQLQuery/values.md) | SQL语句模板绑定值 |
 |  public  |[query](SQLQuery/query.md) | 生成一条查询语句 |
 |  public  |[use](SQLQuery/use.md) | 切换使用的数据表 |
 |  public  |[error](SQLQuery/error.md) | 获取语句查询错误 |
 |  public  |[erron](SQLQuery/erron.md) | 获取语句查询错误编号 |
 |  public  |[lastInsertId](SQLQuery/lastInsertId.md) | 获取最后一次插入的主键ID（用于自增值 |
 |  public  static|[begin](SQLQuery/begin.md) | 事务系列，开启事务 |
 |  public  static|[beginTransaction](SQLQuery/beginTransaction.md) | 事务系列，开启事务 |
 |  public  static|[commit](SQLQuery/commit.md) | 事务系列，提交事务 |
 |  public  static|[rollBack](SQLQuery/rollBack.md) | 事务系列，撤销事务 |
 |  public  |[quote](SQLQuery/quote.md) |  |
 |  public  |[arrayQuote](SQLQuery/arrayQuote.md) |  |
 |  protected  static|[prepareQuery](SQLQuery/prepareQuery.md) |  |
 |  public  |[object](SQLQuery/object.md) | 添加列处理类 |
 |  public  |[getConnection](SQLQuery/getConnection.md) |  |
 |  public  |[insert](SQLQuery/insert.md) |  |
 |  public  |[where](SQLQuery/where.md) | 在数据表总搜索 |
 |  public  |[search](SQLQuery/search.md) | 搜索列 |
 |  public  |[select](SQLQuery/select.md) | 选择列 |
 |  public  |[update](SQLQuery/update.md) | 更新列 |
 |  public  |[delete](SQLQuery/delete.md) | 删除列 |
 |  public  static|[prepareIn](SQLQuery/prepareIn.md) |  |
 |  public  static|[prepareSearch](SQLQuery/prepareSearch.md) |  |
 |  public  static|[prepareWhere](SQLQuery/prepareWhere.md) |  |
 |  public  |[count](SQLQuery/count.md) |  |
 |  public  |[nextId](SQLQuery/nextId.md) |  |
 |  protected  static|[table](SQLQuery/table.md) |  |
 |  protected  static|[prepareLimit](SQLQuery/prepareLimit.md) |  |
 |  protected  static|[page](SQLQuery/page.md) |  |
## 例子