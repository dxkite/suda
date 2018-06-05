#  Connection 

> *文件信息* suda\archive\Connection.php: 28~194


数据表链接对象


## 描述






## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| public    | type | | 
| public    | host | | 
| public    | port | | 
| public    | charset | | 
| public    | prefix | | 
| public    | user | | 
| public    | password | | 
| public    | database | | 
| public    | id | | 
| protected    | queryCount | | 
| protected    | times | | 
| protected    | pdo | | 
| protected    | transaction | | 
| protected  static  | _id | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  |[__toString](Connection/__toString.md) |  |
|  public  static|[getDefaultConnection](Connection/getDefaultConnection.md) |  |
|  protected  |[getDsn](Connection/getDsn.md) |  |
|  public  |[connect](Connection/connect.md) |  |
|  public  |[getPdo](Connection/getPdo.md) |  |
|  public  |[lastInsertId](Connection/lastInsertId.md) | 获取最后一次插入的主键ID（用于自增值 |
|  public  |[begin](Connection/begin.md) | 事务系列，开启事务 |
|  public  |[beginTransaction](Connection/beginTransaction.md) | 事务系列，开启事务 |
|  public  |[isConnected](Connection/isConnected.md) |  |
|  public  |[commit](Connection/commit.md) | 事务系列，提交事务 |
|  public  |[rollBack](Connection/rollBack.md) | 事务系列，撤销事务 |
|  protected  |[onBeforeSystemShutdown](Connection/onBeforeSystemShutdown.md) |  |
|  public  |[quote](Connection/quote.md) |  |
|  public  |[arrayQuote](Connection/arrayQuote.md) |  |
|  public  |[prefixStr](Connection/prefixStr.md) |  |
 

## 例子

example