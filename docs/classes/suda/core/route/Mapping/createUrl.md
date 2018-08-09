# Mapping::createUrl
创建URL
> *文件信息* suda\core\route\Mapping.php: 23~548
## 所属类 

[Mapping](../Mapping.md)

## 可见性

  public  
## 说明



## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| args |  array | 无 |  URL中的参数 |
| query |  bool | 1 |  除URL中必要参数外是否添加 $args 参数中多参数到查询字符串 |
| queryArr |  array | Array |  查询参数 ($args) 中的参数优先覆盖 |

## 返回值
类型：string
 路由构建成功的URL

## 例子

example