# 数据库

在框架中，提供了对数据库操作的类，数据库操作，数据库操作需要注意的地方包括两个部分，一个是数据源，一个是数据表，通过数据源我们可以配置数据库的链接，一个数据源由多个链接组成，目前框架支持数据源的类型为 `mysql` 和 `sqlite`，数据库的配置为 `@app-resouce/config/data-source`， 默认读取应用的配置 `app/resource/config/data-source.json` 文件，默认的配置如下：


```json
{
    "master": {
        "type": "mysql",
        "enable": true,
        "prefix": "dx_",
        "mode": "read+write",
        "host": "127.0.0.1",
        "port": 3306,
        "name": "suda_system",
        "user": "root",
        "password": "root",
        "charset": "utf8mb4"
    }
}
```


## 配置说明

配置JSON文件中支持读取PHP常量参数，语法为 `${常量名}`， 现有常量参考 [常量说明](06-constant.html)


**通用可配置项**

| 配置项 | 说明 |
|-------|------|
| `enable` |是否启用该链接 |
| `type` | 数据链接的类型，支持 mysql 与 sqlite，默认为 mysql |
| `mode` | 该链接启用的模式 read = 可读， write = 可写 ，read+write = 读写 |
| `prefix` | 数据库表前缀 |

**MySQL可配置项**

| 配置项 | 说明 |
|-------|------|
| host | 数据库主机 |
| port | 数据库端口 |
| name | 数据库用户名 |
| user | 数据库用户 |
| password | 数据库密码 |
| charset | 连接编码 |

**SQLite可配置项**

| 配置项 | 说明 |
|-------|------|
| path | 数据库地址 |


## 执行SQL语句

直接运行SQL语句需要使用 `\suda\database\statement\QueryStatement` 对象，通过使用 `\suda\application\Application->getDataSource()`
方法来获取数据源，来进行操作，如：

```php
// 对数据源进行写操作
$application->getDataSource()->write()->query(new QueryStatement('CREATE DATABASE test_demo'));

// 对数据库进行读操作
$query = (new QueryStatement('SELECT * FROM test_table'));
// 取全部
$query->setFetch(QueryStatement::FETCH_ALL);
// 执行
$rows = $application->getDataSource()->read()->query($query);
```


## 数据库CUDR操作

**注意：** 数据库建表需要手动建表

- [数据表](05-database.table.html)
- [C·添加记录](05-database.create.html)
- [R·读取记录](05-database.read.html)
- [U·更新记录](05-database.update.html)
- [D·删除记录](05-database.delete.html)
- [通用查询](05-database.query.html)

