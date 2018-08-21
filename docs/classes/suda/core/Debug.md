#  Debug 

> *文件信息* suda\core\Debug.php: 26~568


异常日志类


## 描述



用于记录运行日志和运行信息以及提供错误显示
## 常量列表
| 常量名  |  值|
|--------|----|
|MAX_LOG_SIZE | 2097152 | 
|TRACE | trace | 
|DEBUG | debug | 
|INFO | info | 
|NOTICE | notice | 
|WARNING | warning | 
|ERROR | error | 


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | level | | 
| protected  static  | run_info | | 
| protected  static  | log | | 
| protected  static  | time | | 
| protected  static  | hash | | 
| protected  static  | dump | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[init](Debug/init.md) |  |
|  public  static|[time](Debug/time.md) |  |
|  public  static|[timeEnd](Debug/timeEnd.md) |  |
|  protected  static|[compareLevel](Debug/compareLevel.md) |  |
|  protected  static|[_loginfo](Debug/_loginfo.md) |  |
|  public  static|[displayException](Debug/displayException.md) |  |
|  protected  static|[printTrace](Debug/printTrace.md) |  |
|  protected  static|[printConsole](Debug/printConsole.md) |  |
|  protected  static|[printHTML](Debug/printHTML.md) |  |
|  protected  static|[displayLog](Debug/displayLog.md) |  |
|  public  static|[logException](Debug/logException.md) |  |
|  protected  static|[save](Debug/save.md) |  |
|  public  static|[memshow](Debug/memshow.md) |  |
|  public  static|[beforeSystemRun](Debug/beforeSystemRun.md) |  |
|  public  static|[getInfo](Debug/getInfo.md) |  |
|  public  static|[afterSystemRun](Debug/afterSystemRun.md) |  |
|  public  static|[phpShutdown](Debug/phpShutdown.md) |  |
|  public  static|[die](Debug/die.md) |  |
|  public  static|[__callStatic](Debug/__callStatic.md) |  |
|  protected  static|[strify](Debug/strify.md) |  |
|  public  |[__call](Debug/__call.md) |  |
|  public  static|[addDump](Debug/addDump.md) |  |
|  protected  static|[assginDebugInfo](Debug/assginDebugInfo.md) |  |
|  protected  static|[dumpArray](Debug/dumpArray.md) |  |
 

## 例子

example