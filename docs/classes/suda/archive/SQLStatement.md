#  SQLStatement 

> *文件信息* suda\archive\SQLStatement.php: 24~113


数据库查询语句接口


## 描述







## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|abstract  public  |[getConnection](SQLStatement/getConnection.md) |  |
|abstract  public  |[setConnection](SQLStatement/setConnection.md) |  |
|abstract  public  |[fetch](SQLStatement/fetch.md) | 获取查询结果的一列 |
|abstract  public  |[fetchObject](SQLStatement/fetchObject.md) | 获取查询结果的一列，并作为类对象 |
|abstract  public  |[fetchAll](SQLStatement/fetchAll.md) | 获取全部的查询结果 |
|abstract  public  |[exec](SQLStatement/exec.md) | 运行SQL语句 |
|abstract  public  static|[value](SQLStatement/value.md) | 生成一个数据输入值 |
|abstract  public  |[values](SQLStatement/values.md) | SQL语句模板绑定值 |
|abstract  public  |[query](SQLStatement/query.md) | 生成一条查询语句 |
|abstract  public  |[use](SQLStatement/use.md) | 切换使用的数据表 |
|abstract  public  |[error](SQLStatement/error.md) | 获取语句查询错误 |
|abstract  public  |[erron](SQLStatement/erron.md) | 获取语句查询错误编号 |
|abstract  public  |[object](SQLStatement/object.md) | 添加列处理类 |
 

## 例子

example