# 路由 

在Nabula中，采用OOP的方式开发，所有的请求都由请求处理器处理，具体详情如下，需要添加一个请求处理，需要在模块的 `route.json` 中加入相应的处理语句，这里我们使用最开始创建的Demo程序路由配置文件来说明路由配置。

## 路由配置文件

Demo应用的 `suda/welcome` 模块路由配置如下：

```json
{
    "index": {
        "template": "welcome",
        "uri": "\/"
    },
    "hello": {
        "template": "helloworld",
        "uri": "\/helloworld"
    },
    "simple": {
        "class": "suda.welcome.response.SimpleResponse",
        "uri": "\/simple"
    }
}
```
### 模板路由

这里的路由包括了两种路由，一种是 `模板路由`， 访问后不处理页面，直接渲染页面的模板。如 `index` 和 `hello` 为键名的两个路由，都是模板路由，其中参数说明如下：

| 参数 | 说明 |
|-----|------|
| template | 模板，采用自定义的 `URI` 来标识模板位置 |
| uri | 页面访问的 URI （不带参数的URL） |

当收到访问时，框架会解析 URL 提取 URI 来匹配对应的路由规则，响应对应的处理方式。

### 处理器路由

处理器路由与模板路由不同，处理器路由采用 `class` 来标识由指定类处理请求，这个类必须继承 `suda\application\processor\RequestProcessor` 接口，如 `simple` 路由的处理器如下：

```php
class SimpleResponse implements RequestProcessor
{
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $template = $application->getTemplate('simple', $request);
        $template->set('ip', $request->getRemoteAddr());
        return $template;
    }
}
```

该处理器中 `onRequest` 为**请求处理**的时候会被回调的函数，框架会构造如下类作为参数：

- `suda\application\Application` 应用类，包含了当前模块信息和各种环境信息
- `suda\framework\Request` 对请求的封装，包括了各种请求的信息和参数
- `suda\framework\Response` 对响应的封装，避免直接输出，规格化输出响应

其中，如果 `onRequest` 包含返回值，则会托管到 `suda\framework\Response` 的内容包装器进行包装输出，如返回数组格式会被包装成JSON响应
> 说明： 语句 `$application->getTemplate('simple', $request)` 含义为获取 `simple` 资源，这里为获取 `simple` 模板，由于没有指定全部的信息，suda会尝试自动推导为 `suda/welcome:1.0:simple` 模板，即为 `suda/welcome` 的 `1.0` 版本下的模板文件 `simple.tpl.html`


## 反向路由

路由的引用同样采用模块资源标识来应用，如：

可以使用 `welcome:index` 表示  `index` 路由，构建路由的URL可以参考如下代码：

```php
return [
    $application->getUrl($request, 'welcome:index'),
    $application->getUrl($request, 'welcome:hello', ['name' => 'dxkite', 'version' => SUDA_VERSION]),
    $application->getUribase($request).$application->getUrl($request, 'welcome:simple', ['name' => 'dxkite', 'version' => SUDA_VERSION]),
];
```

生成的结果如下：

```json
[
    "/",
    "/helloworld?name=dxkite&version=3.0.0",
    "http://suda.dev.dx/simple?name=dxkite&version=3.0.0"
]
```

如果需要生成跳转URL，**推荐使用反向路由构建URL**，以免配置文件修改导致URL失效出错

