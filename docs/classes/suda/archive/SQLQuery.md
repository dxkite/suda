#  类 SQLQuery

> *文件信息* suda\archive\SQLQuery.php: 26~365



## 描述

该类暂时无说明





## 变量列表
| 可见性 |  变量名  |  值| 说明 |
|--------|----|---|---|
| protected static  | queryCount | 0 | | 
| protected static  | times | 0 | | 
| protected static  | pdo | null | | 
| protected static  | prefix | null | | 
| protected static  | transaction | 0 | | 
| protected   | object | 0 | | 
| protected   | stmt | 0 | | 
| protected   | query | 0 | | 
| protected   | values | 0 | | 
| protected   | scroll | 0 | | 
| protected   | database | 0 | | 
| protected   | dbchange | 0 | | 



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
