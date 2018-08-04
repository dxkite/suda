#  Router 

> *文件信息* suda\core\Router.php: 27~548


路由处理类


## 描述



用于处理访问的路由信息
## 常量列表
| 常量名  |  值|
|--------|----|
|CACHE_NAME | route.mapping | 


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | router | | 
| protected    | routers | | 
| protected  static  | cacheName | | 
| protected  static  | cacheModules | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[getInstance](Router/getInstance.md) |  |
|  public  static|[getModulePrefix](Router/getModulePrefix.md) |  |
|  public  |[load](Router/load.md) |  |
|  protected  |[loadModuleConfig](Router/loadModuleConfig.md) |  |
|  protected  |[loadFile](Router/loadFile.md) |  |
|  protected  |[saveFile](Router/saveFile.md) |  |
|  public  |[loadModulesRouter](Router/loadModulesRouter.md) |  |
|  public  |[routerCached](Router/routerCached.md) |  |
|  public  |[prepareRouterInfo](Router/prepareRouterInfo.md) |  |
|  public  |[parseUrl](Router/parseUrl.md) |  |
|  protected  |[matchRouterMap](Router/matchRouterMap.md) |  |
|  public  static|[parseName](Router/parseName.md) | 解析模板名 |
|  public  |[getRouterFullName](Router/getRouterFullName.md) |  |
|  public  |[buildUrlArgs](Router/buildUrlArgs.md) |  |
|  public  |[decode](Router/decode.md) | 将 router:// 协议指定的URL转换为 URL |
|  public  |[encode](Router/encode.md) | 将URL转换为 router:// 协议形式 |
|  public  |[buildUrl](Router/buildUrl.md) | 根据路由名称创建URL |
|  public  |[dispatch](Router/dispatch.md) |  |
|  public  |[getRouter](Router/getRouter.md) | 获取路由 |
|  public  |[setRouterAlias](Router/setRouterAlias.md) | 设置路由别名 |
|  public  |[routerReplace](Router/routerReplace.md) | 路由替换 |
|  public  |[routerMove](Router/routerMove.md) | 路由移动 |
|  public  |[addMapping](Router/addMapping.md) |  |
|  public  |[refreshMapping](Router/refreshMapping.md) |  |
|  public  |[addRouter](Router/addRouter.md) | 动态添加运行命令 |
|  public  |[replaceMatch](Router/replaceMatch.md) | 替换匹配表达式 |
|  public  |[replaceClass](Router/replaceClass.md) | 替换路由指定类 |
|  protected  static|[runRouter](Router/runRouter.md) |  |
|  public  static|[error](Router/error.md) |  |
|  public  |[getRouters](Router/getRouters.md) |  |
 

## 例子

example