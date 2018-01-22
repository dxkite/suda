#  SQLQuery 

> *文件信息* suda\archive\SQLQuery.php: 31~370

数据库查询方案，简化数据库查

## 描述







## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected static  | queryCount | | 
| protected static  | times | | 
| protected static  | pdo | | 
| protected static  | prefix | | 
| protected static  | transaction | | 
| protected   | object | | 
| protected   | stmt | | 
| protected   | query | | 
| protected   | values | | 
| protected   | scroll | | 
| protected   | database | | 
| protected   | dbchange | | 



## 方法


| 可见性 | 方法名 | 说明 |
|--------|-------|------|
| public |[__construct](SQLQuery/__construct.md) | 构造查询 |
| public |[fetch](SQLQuery/fetch.md) |  |
| public |[fetchObject](SQLQuery/fetchObject.md) |  |
| public |[fetchAll](SQLQuery/fetchAll.md) |  |
| public |[exec](SQLQuery/exec.md) |  |
| public static|[value](SQLQuery/value.md) |  |
| public |[values](SQLQuery/values.md) |  |
| public |[query](SQLQuery/query.md) |  |
| public |[use](SQLQuery/use.md) |  |
| public |[error](SQLQuery/error.md) |  |
| public |[erron](SQLQuery/erron.md) |  |
| public static|[lastInsertId](SQLQuery/lastInsertId.md) |  |
| public static|[begin](SQLQuery/begin.md) |  |
| public static|[beginTransaction](SQLQuery/beginTransaction.md) |  |
| public static|[commit](SQLQuery/commit.md) |  |
| public static|[rollBack](SQLQuery/rollBack.md) |  |
| public |[quote](SQLQuery/quote.md) |  |
| public |[arrayQuote](SQLQuery/arrayQuote.md) |  |
| public static|[getRuninfo](SQLQuery/getRuninfo.md) |  |
| protected static|[connectPdo](SQLQuery/connectPdo.md) |  |
| public |[object](SQLQuery/object.md) | 添加列处理类 |
| protected |[__dataTransfrom](SQLQuery/__dataTransfrom.md) | 转换数据，数据库统处理输入输出数据 |
