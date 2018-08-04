# Router::buildUrl
根据路由名称创建URL
> *文件信息* suda\core\Router.php: 27~548
## 所属类 

[Router](../Router.md)

## 可见性

  public  
## 说明

用于处理访问的路由信息

## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| name |  string | 无 |  路由名称 |
| values |  array | Array |  路由中的参数 |
| query |  bool | 1 |  是否使用多余路由参数作为查询参数 |
| queryArr |  array | Array |  查询参数 |
| moduleDefault |  string | null |  路由未指定模块时的默认模块 |

## 返回值
类型：string
无

## 例子

example