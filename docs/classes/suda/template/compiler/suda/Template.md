#  Template 

> *文件信息* suda\template\compiler\suda\Template.php: 26~338





## 描述



该类暂时无说明


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected    | value | 模板的值| 
| protected    | response | 模板所属于的响应| 
| protected    | name | | 
| protected    | parent | | 
| protected    | hooks | | 
| protected    | module | | 
| protected    | source | | 
| protected  static  | render | | 
| protected  static  | scriptNonce | | 
| protected    | extend | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  |[render](Template/render.md) | 渲染页面 |
|abstract  protected  |[_render_template](Template/_render_template.md) | 渲染语句 |
|  public  |[getRenderedString](Template/getRenderedString.md) | 获取渲染后的字符串 |
|  protected  |[_render_start](Template/_render_start.md) |  |
|  protected  |[_render_end](Template/_render_end.md) |  |
|  public  |[__toString](Template/__toString.md) | 获取当前模板的字符串 |
|  public  |[echo](Template/echo.md) | 输出当前模板 |
|  public  |[extend](Template/extend.md) |  |
|  public  |[include](Template/include.md) |  |
|  public  |[getRenderStack](Template/getRenderStack.md) |  |
|  protected  |[getScriptNonce](Template/getScriptNonce.md) |  |
|  public  |[set](Template/set.md) | 单个设置值 |
|  public  |[assign](Template/assign.md) | 直接压入值 |
|  public  |[parent](Template/parent.md) | 创建模板 |
|  public  |[response](Template/response.md) | 创建响应 |
|  public  |[get](Template/get.md) | 创建模板获取值 |
|  public  |[has](Template/has.md) | 检测值 |
|  public  |[data](Template/data.md) |  |
|  public  |[execHook](Template/execHook.md) |  |
|  public  |[execGlobalHook](Template/execGlobalHook.md) |  |
|  public  |[url](Template/url.md) |  |
|  public  |[exec](Template/exec.md) |  |
|  public  |[name](Template/name.md) |  |
|  public  |[responseName](Template/responseName.md) |  |
|  public  |[isMe](Template/isMe.md) |  |
|  public  |[boolecho](Template/boolecho.md) |  |
|  public  |[getModule](Template/getModule.md) |  |
|  public  |[getValue](Template/getValue.md) |  |
|  public  |[getResponse](Template/getResponse.md) |  |
 

## 例子

example