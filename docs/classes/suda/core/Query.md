#  Query 

> *文件信息* suda\core\Query.php: 26~327


数据库查询类


## 描述



提供了数据库的查询方式



## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | defaultQuery | | 
| protected  static  | query | | 
| protected    | rawQuery | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[insert](Query/insert.md) | 向数据表中插入一行 |
|  public  static|[that](Query/that.md) |  |
|  public  static|[where](Query/where.md) | 在数据表总搜索 |
|  public  static|[search](Query/search.md) | 搜索列 |
|  public  static|[select](Query/select.md) | 选择列 |
|  public  static|[update](Query/update.md) | 更新列 |
|  public  static|[delete](Query/delete.md) | 删除列 |
|  public  static|[prepareIn](Query/prepareIn.md) |  |
|  public  static|[prepareSearch](Query/prepareSearch.md) |  |
|  public  static|[prepareWhere](Query/prepareWhere.md) |  |
|  public  static|[count](Query/count.md) |  |
|  public  static|[nextId](Query/nextId.md) |  |
|  protected  static|[table](Query/table.md) |  |
|  protected  static|[page](Query/page.md) |  |
|  public  |[__construct](Query/__construct.md) | 构造查询 |
|  public  |[getConnection](Query/getConnection.md) |  |
|  public  |[setConnection](Query/setConnection.md) |  |
|  public  |[fetch](Query/fetch.md) | 获取查询结果的一列 |
|  public  static|[useQuery](Query/useQuery.md) |  |
|  public  static|[resetQuery](Query/resetQuery.md) |  |
|  public  |[fetchObject](Query/fetchObject.md) | 获取查询结果的一列，并作为类对象 |
|  public  |[fetchAll](Query/fetchAll.md) | 获取全部的查询结果 |
|  public  |[exec](Query/exec.md) | 运行SQL语句 |
|  public  static|[value](Query/value.md) | 生成一个数据输入值 |
|  public  |[values](Query/values.md) | SQL语句模板绑定值 |
|  public  |[query](Query/query.md) | 生成一条查询语句 |
|  public  |[use](Query/use.md) | 切换使用的数据表 |
|  public  |[error](Query/error.md) | 获取语句查询错误 |
|  public  |[erron](Query/erron.md) | 获取语句查询错误编号 |
|  public  |[lastInsertId](Query/lastInsertId.md) | 获取最后一次插入的主键ID（用于自增值 |
|  public  static|[begin](Query/begin.md) | 事务系列，开启事务 |
|  public  static|[beginTransaction](Query/beginTransaction.md) | 事务系列，开启事务 |
|  public  static|[commit](Query/commit.md) | 事务系列，提交事务 |
|  public  static|[rollBack](Query/rollBack.md) | 事务系列，撤销事务 |
|  public  |[quote](Query/quote.md) |  |
|  public  |[arrayQuote](Query/arrayQuote.md) |  |
|  protected  static|[_connect](Query/_connect.md) |  |
|  public  |[object](Query/object.md) | 添加列处理类 |
 

## 例子

example