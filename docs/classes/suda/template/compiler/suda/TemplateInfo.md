#  TemplateInfo 

> *文件信息* suda\template\compiler\suda\TemplateInfo.php: 21~100





## 描述



该类暂时无说明
## 常量列表
| 常量名  |  值|
|--------|----|
|Template | suda\template\compiler\suda\Template | 


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected    | values | | 
| protected    | includes | | 
| protected    | includes_info | | 
| protected    | name | | 
| protected    | module | | 
| protected    | path | | 
| protected  static  | templates | | 
| protected  static  | rawTag | | 
| protected  static  | echoTag | | 
| protected  static  | hookTag | | 
| protected  static  | commentTag | | 
| protected  static  | strTransTag | | 
| protected  static  | rawTransTag | | 
| protected  static  | template | | 
| protected  static  | command | 附加模板命令| 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  |[__construct](TemplateInfo/__construct.md) |  |
|  protected  |[echoValueCallback](TemplateInfo/echoValueCallback.md) |  |
|  protected  |[parseInclude](TemplateInfo/parseInclude.md) |  |
|  public  |[getValuesName](TemplateInfo/getValuesName.md) |  |
|  public  static|[getTemplates](TemplateInfo/getTemplates.md) |  |
|  protected  static|[getModuleTemplate](TemplateInfo/getModuleTemplate.md) |  |
|  public  static|[setBase](TemplateInfo/setBase.md) |  |
|  public  |[compileText](TemplateInfo/compileText.md) |  |
|  public  |[compile](TemplateInfo/compile.md) | 编译文件 |
|  public  |[render](TemplateInfo/render.md) |  |
|  public  static|[addCommand](TemplateInfo/addCommand.md) | 扩展模板命令 |
|  public  static|[hasCommand](TemplateInfo/hasCommand.md) | 检查模板扩展命令是否存在 |
|  public  static|[buildCommand](TemplateInfo/buildCommand.md) | 创建模板扩展命令 |
|  protected  |[echoValue](TemplateInfo/echoValue.md) |  |
|  protected  static|[parseValue](TemplateInfo/parseValue.md) |  |
|  protected  |[parseEcho](TemplateInfo/parseEcho.md) |  |
|  protected  |[parseData](TemplateInfo/parseData.md) |  |
|  protected  |[parseFile](TemplateInfo/parseFile.md) |  |
|  protected  |[parse_](TemplateInfo/parse_.md) |  |
|  protected  |[parseIf](TemplateInfo/parseIf.md) |  |
|  protected  |[parseEndif](TemplateInfo/parseEndif.md) |  |
|  protected  |[parseElse](TemplateInfo/parseElse.md) |  |
|  protected  |[parseElseif](TemplateInfo/parseElseif.md) |  |
|  protected  |[parseFor](TemplateInfo/parseFor.md) |  |
|  protected  |[parseEndfor](TemplateInfo/parseEndfor.md) |  |
|  protected  |[parseForeach](TemplateInfo/parseForeach.md) |  |
|  protected  |[parseEndforeach](TemplateInfo/parseEndforeach.md) |  |
|  protected  |[parseWhile](TemplateInfo/parseWhile.md) |  |
|  protected  |[parseEndwhile](TemplateInfo/parseEndwhile.md) |  |
|  protected  |[parseU](TemplateInfo/parseU.md) |  |
|  protected  |[parseSelf](TemplateInfo/parseSelf.md) |  |
|  protected  |[parseSet](TemplateInfo/parseSet.md) |  |
|  public  |[parseB](TemplateInfo/parseB.md) |  |
|  protected  |[parseStatic](TemplateInfo/parseStatic.md) |  |
|  protected  |[parseUrl](TemplateInfo/parseUrl.md) |  |
|  public  static|[echo](TemplateInfo/echo.md) |  |
|  protected  |[parseStartInsert](TemplateInfo/parseStartInsert.md) |  |
|  protected  |[parseEndInsert](TemplateInfo/parseEndInsert.md) |  |
|  protected  |[parseInsert](TemplateInfo/parseInsert.md) |  |
|  public  |[error](TemplateInfo/error.md) |  |
|  public  |[erron](TemplateInfo/erron.md) |  |
 

## 例子

example