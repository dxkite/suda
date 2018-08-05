# Application::onRequest
截获请求，请求发起的时候会调用
> *文件信息* suda\core\Application.php: 30~737
## 所属类 

[Application](../Application.md)

## 可见性

  public  
## 说明


包含了应用的各种处理方式，可以用快捷函数 app() 来使用本类


## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| request |  suda\core\Request | 无 | 无 |

## 返回值
类型：boolean
 true 表示请求可达,false将截获请求

## 例子

example