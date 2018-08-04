#  Application 

> *文件信息* suda\core\Application.php: 29~620


应用处理类


## 描述




包含了应用的各种处理方式


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
|  public  |[checkModuleRequire](Application/checkModuleRequire.md) |  |
|  public  |[init](Application/init.md) |  |
|  public  |[installModule](Application/installModule.md) |  |
|  public  |[getModules](Application/getModules.md) | 获取所有模块 |
|  public  |[getModuleDirs](Application/getModuleDirs.md) |  |
|  public  |[getActiveModule](Application/getActiveModule.md) |  |
|  public  |[getModuleConfig](Application/getModuleConfig.md) |  |
|  public  |[getConfig](Application/getConfig.md) |  |
|  public  |[getModuleResourcePath](Application/getModuleResourcePath.md) |  |
|  public  |[getModuleConfigPath](Application/getModuleConfigPath.md) |  |
|  public  |[getModulePrefix](Application/getModulePrefix.md) |  |
|  public  |[checkModuleExist](Application/checkModuleExist.md) |  |
|  public  |[getLiveModules](Application/getLiveModules.md) |  |
|  public  |[getReachableModules](Application/getReachableModules.md) |  |
|  public  |[isModuleReachable](Application/isModuleReachable.md) |  |
|  public  |[activeModule](Application/activeModule.md) | 激活运行的模块 |
|  public  |[onRequest](Application/onRequest.md) |  |
|  public  |[onShutdown](Application/onShutdown.md) |  |
|  public  |[uncaughtException](Application/uncaughtException.md) |  |
|  public  |[getModuleName](Application/getModuleName.md) | 获取模块名，不包含版本号 |
|  public  |[getModuleFullName](Application/getModuleFullName.md) | 获取模块全名（包括版本） |
|  public  |[getModuleDir](Application/getModuleDir.md) | 获取模块所在的文件夹名 |
|  public  |[moduleName](Application/moduleName.md) | 根据模块目录名转换成模块名 |
|  public  |[registerModule](Application/registerModule.md) |  |
|  public  |[getModulesInfo](Application/getModulesInfo.md) |  |
|  public  |[getModulePath](Application/getModulePath.md) |  |
|  protected  static|[versionCompire](Application/versionCompire.md) | 比较版本 |
|  public  static|[getThisModule](Application/getThisModule.md) |  |
|  public  static|[getFileModule](Application/getFileModule.md) |  |
 

## 例子

example