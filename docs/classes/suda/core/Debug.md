#  Debug 

> *文件信息* suda\core\Debug.php: 29~633


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
|LOG_PACK | # Error> Array to string conversion
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\class.md.tpl.php:21
    => D:\Server\vhost\atd3.org\suda\script\docme\template\class.md.tpl.php:21 suda\core\System::uncaughtError(8,Array to string conversion,D:\Server\vhost\atd3.org\suda\script\docme\template\class.md.tpl.php,21,{"value":["{%","%}"],"name":"LOG_PACK"})
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:132 Class4b5188a17937cc30ff0ad7f5b03ace4d->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:96 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:72 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\Debug.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:27 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
Array | 


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | level | | 
| protected  static  | runInfo | | 
| protected  static  | log | | 
| protected  static  | time | | 
| protected  static  | hash | | 
| protected  static  | ip | | 
| protected  static  | dump | | 
| protected  static  | removeFiles | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[init](Debug/init.md) |  |
|  public  static|[time](Debug/time.md) |  |
|  public  static|[timeEnd](Debug/timeEnd.md) |  |
|  protected  static|[compareLevel](Debug/compareLevel.md) |  |
|  protected  static|[writeLogLevel](Debug/writeLogLevel.md) |  |
|  protected  static|[writeLog](Debug/writeLog.md) |  |
|  public  static|[displayException](Debug/displayException.md) |  |
|  protected  static|[printTrace](Debug/printTrace.md) |  |
|  protected  static|[printConsole](Debug/printConsole.md) |  |
|  protected  static|[printHTML](Debug/printHTML.md) |  |
|  protected  static|[dumpException](Debug/dumpException.md) |  |
|  protected  static|[displayLog](Debug/displayLog.md) |  |
|  public  static|[writeException](Debug/writeException.md) |  |
|  protected  static|[save](Debug/save.md) |  |
|  protected  static|[formatBytes](Debug/formatBytes.md) |  |
|  public  static|[beforeSystemRun](Debug/beforeSystemRun.md) |  |
|  public  static|[getInfo](Debug/getInfo.md) |  |
|  public  static|[afterSystemRun](Debug/afterSystemRun.md) |  |
|  public  static|[phpShutdown](Debug/phpShutdown.md) |  |
|  public  static|[die](Debug/die.md) |  |
|  public  static|[__callStatic](Debug/__callStatic.md) |  |
|  protected  static|[strify](Debug/strify.md) |  |
|  public  |[__call](Debug/__call.md) |  |
|  protected  static|[checkSize](Debug/checkSize.md) | 检查日志文件大小 |
|  protected  static|[packLogFile](Debug/packLogFile.md) | 打包日志文件 |
|  public  static|[addDump](Debug/addDump.md) |  |
|  protected  static|[assginDebugInfo](Debug/assginDebugInfo.md) |  |
|  protected  static|[dumpArray](Debug/dumpArray.md) |  |
 

## 例子

example