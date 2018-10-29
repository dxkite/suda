#  Request 

> *文件信息* suda\core\Request.php: 24~492


请求描述类，客户端向框架发送请求时会生成此类


## 描述



该类暂时无说明


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | instance | | 
| protected  static  | type | | 
| protected  static  | query | | 
| protected  static  | url | | 
| protected  static  | baseUrl | | 
| protected  static  | host | | 
| protected  static  | port | | 
| protected  static  | scheme | | 
| protected  static  | script | | 
| protected    | mapping | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[getInstance](Request/getInstance.md) |  |
|  public  |[setMapping](Request/setMapping.md) |  |
|  public  static|[json](Request/json.md) | 获取请求的JSON文档 |
|  public  static|[input](Request/input.md) | 获取请求的原始输入 |
|  public  static|[method](Request/method.md) | 获取请求的方法 |
|  public  static|[getMethod](Request/getMethod.md) | 获取请求的方法 |
|  public  static|[url](Request/url.md) | 获取请求的URL数据 |
|  public  static|[set](Request/set.md) | 设置get的值 |
|  public  static|[get](Request/get.md) | 获取请求的GET数据 |
|  public  static|[post](Request/post.md) | 获取POST请求的值 |
|  public  static|[files](Request/files.md) | 获取请求的文件 |
|  public  static|[cookie](Request/cookie.md) | 获取Cookie的值 |
|  public  static|[ip](Request/ip.md) | 获取请求的 IP |
|  public  static|[isPost](Request/isPost.md) | 判断是否是POST请求 |
|  public  static|[isGet](Request/isGet.md) | 判断是否是GET请求 |
|  public  static|[hasGet](Request/hasGet.md) | 判断是否有GET请求 |
|  public  static|[hasPost](Request/hasPost.md) | 判断是否有POST数据请求 |
|  public  static|[hasJson](Request/hasJson.md) | 判断是否有JSON数据请求 |
|  public  static|[isJson](Request/isJson.md) | 判断请求的数据是否为 json |
|  public  static|[signature](Request/signature.md) | 根据IP生成HASH |
|  public  static|[getHeader](Request/getHeader.md) | 获取请求头的内容 |
|  public  static|[hasHeader](Request/hasHeader.md) | 判断请求头中是否包含某一字段 |
|  public  static|[parseUrl](Request/parseUrl.md) | 处理请求的URL |
|  protected  static|[parseRequest](Request/parseRequest.md) |  |
|  public  static|[virtualUrl](Request/virtualUrl.md) |  |
|  public  static|[referer](Request/referer.md) |  |
|  public  static|[hostBase](Request/hostBase.md) |  |
|  public  static|[getScheme](Request/getScheme.md) |  |
|  public  static|[getHost](Request/getHost.md) |  |
|  public  static|[getPort](Request/getPort.md) |  |
|  public  static|[baseUrl](Request/baseUrl.md) |  |
|  protected  static|[getBaseUrl](Request/getBaseUrl.md) |  |
|  public  |[isCrawler](Request/isCrawler.md) |  |
|  public  |[getMapping](Request/getMapping.md) |  |
 

## 例子

example