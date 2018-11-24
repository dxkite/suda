#  Application 

> *文件信息* suda\core\Application.php: 33~835


应用处理类


## 描述




包含了应用的各种处理方式，可以用快捷函数 app() 来使用本类



## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | instance | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  protected  |[__construct](Application/__construct.md) |  |
|  public  static|[getInstance](Application/getInstance.md) |  |
|  public  |[addModulesPath](Application/addModulesPath.md) | 添加模块扫描目录 |
|  protected  |[loadModules](Application/loadModules.md) | 载入模块 |
|  public  |[loadModule](Application/loadModule.md) | 加载模块 |
|  public  |[checkModuleRequire](Application/checkModuleRequire.md) | 检查模块依赖 |
|  protected  |[initDatabase](Application/initDatabase.md) |  |
|  public  |[init](Application/init.md) |  |
|  public  |[installModule](Application/installModule.md) | 安装有自动安装功能的模块 |
|  public  |[getModules](Application/getModules.md) | 获取所有的模块 |
|  public  |[getModuleDirs](Application/getModuleDirs.md) | 获取所有模块的目录 |
|  public  |[getActiveModule](Application/getActiveModule.md) | 获取当前激活的模块 |
|  public  |[getModuleConfig](Application/getModuleConfig.md) | 获取模块的配置信息 |
|  public  |[getConfig](Application/getConfig.md) | 获取app/resource/config下的配置 |
|  public  |[getModuleResourcePath](Application/getModuleResourcePath.md) | 获取模块 resouce 目录路径 |
|  public  |[getModuleConfigPath](Application/getModuleConfigPath.md) | 获取模块 resource/config 路径 |
|  public  |[getModulePrefix](Application/getModulePrefix.md) | 获取模块URL前缀 |
|  public  |[checkModuleExist](Application/checkModuleExist.md) | 检查模块是否存在 |
|  public  |[getLiveModules](Application/getLiveModules.md) | 获取激活的模块 |
|  public  |[getReachableModules](Application/getReachableModules.md) | 获取网页端可达的模块 |
|  public  |[isModuleReachable](Application/isModuleReachable.md) | 判断模块是否可达 |
|  public  |[addReachableModule](Application/addReachableModule.md) | 添加可达模块 |
|  public  |[activeModule](Application/activeModule.md) | 激活运行的模块 |
|  public  |[onRequest](Application/onRequest.md) | 截获请求，请求发起的时候会调用 |
|  public  |[onShutdown](Application/onShutdown.md) | 请求关闭的时候会调用 |
|  public  |[uncaughtException](Application/uncaughtException.md) | 请求发生异常的时候会调用 |
|  public  |[getModuleName](Application/getModuleName.md) | 获取模块名，不包含版本号 |
|  public  |[getModuleFullName](Application/getModuleFullName.md) | 获取模块全名（包括版本） |
|  public  |[getModuleDir](Application/getModuleDir.md) | 获取模块所在的文件夹名 |
|  public  |[moduleName](Application/moduleName.md) | 根据模块目录名转换成模块名 |
|  protected  |[registerModules](Application/registerModules.md) | 注册所有模块信息 |
|  public  |[registerModule](Application/registerModule.md) | 注册模块 |
|  public  |[getModulesInfo](Application/getModulesInfo.md) |  |
|  public  |[getModulePath](Application/getModulePath.md) |  |
|  protected  static|[versionCompire](Application/versionCompire.md) | 比较版本 |
|  public  static|[getThisModule](Application/getThisModule.md) | 根据函数调用栈判断调用时所属模块 |
|  public  static|[getFileModule](Application/getFileModule.md) | 根据文件名判断所属模块 |
 

## 例子

example