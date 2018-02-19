#  Compiler 

> *文件信息* suda\template\compiler\suda\Compiler.php: 30~373

Suda 模板编译器

## 描述

该类暂时无说明


## 常量列表
| 常量名  |  值|
|--------|----|
|Template | suda\template\compiler\suda\Template | 





## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected static  | rawTag | | 
| protected static  | echoTag | | 
| protected static  | hookTag | | 
| protected static  | commentTag | | 
| protected static  | strTransTag | | 
| protected static  | rawTransTag | | 
| protected static  | template | | 
| protected static  | command | 附加模板命令| 



## 方法


| 可见性 | 方法名 | 说明 |
|--------|-------|------|
| public static|[setBase](Compiler/setBase.md) |  |
| public |[compileText](Compiler/compileText.md) |  |
| public |[compile](Compiler/compile.md) | 编译文件 |
| public |[render](Compiler/render.md) |  |
| public static|[addCommand](Compiler/addCommand.md) | 扩展模板命令 |
| public static|[hasCommand](Compiler/hasCommand.md) | 检查模板扩展命令是否存在 |
| public static|[buildCommand](Compiler/buildCommand.md) | 创建模板扩展命令 |
| protected |[echoValue](Compiler/echoValue.md) |  |
| protected |[echoValueCallback](Compiler/echoValueCallback.md) |  |
| protected static|[parseValue](Compiler/parseValue.md) |  |
| protected |[parseEcho](Compiler/parseEcho.md) |  |
| protected |[parseData](Compiler/parseData.md) |  |
| protected |[parseFile](Compiler/parseFile.md) |  |
| protected |[parse_](Compiler/parse_.md) |  |
| protected |[parseIf](Compiler/parseIf.md) |  |
| protected |[parseEndif](Compiler/parseEndif.md) |  |
| protected |[parseElse](Compiler/parseElse.md) |  |
| protected |[parseElseif](Compiler/parseElseif.md) |  |
| protected |[parseFor](Compiler/parseFor.md) |  |
| protected |[parseEndfor](Compiler/parseEndfor.md) |  |
| protected |[parseForeach](Compiler/parseForeach.md) |  |
| protected |[parseEndforeach](Compiler/parseEndforeach.md) |  |
| protected |[parseWhile](Compiler/parseWhile.md) |  |
| protected |[parseEndwhile](Compiler/parseEndwhile.md) |  |
| protected |[parseInclude](Compiler/parseInclude.md) |  |
| protected |[parseU](Compiler/parseU.md) |  |
| protected |[parseSelf](Compiler/parseSelf.md) |  |
| protected |[parseSet](Compiler/parseSet.md) |  |
| public |[parseB](Compiler/parseB.md) |  |
| protected |[parseStatic](Compiler/parseStatic.md) |  |
| protected |[parseUrl](Compiler/parseUrl.md) |  |
| public static|[echo](Compiler/echo.md) |  |
| protected |[parseStartInsert](Compiler/parseStartInsert.md) |  |
| protected |[parseEndInsert](Compiler/parseEndInsert.md) |  |
| protected |[parseInsert](Compiler/parseInsert.md) |  |
| public |[error](Compiler/error.md) |  |
| public |[erron](Compiler/erron.md) |  |



## 例子

example