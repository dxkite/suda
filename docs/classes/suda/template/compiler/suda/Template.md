#  Template 

> *文件信息* suda\template\compiler\suda\Template.php: 25~265



## 描述

该类暂时无说明





## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected   | value | 模板的值| 
| protected   | response | 模板所属于的响应| 
| protected   | name | | 
| protected   | parent | | 
| protected   | hooks | | 
| protected   | module | | 
| protected static  | render | | 



## 方法


| 可见性 | 方法名 | 说明 |
|--------|-------|------|
| public |[__construct](Template/__construct.md) |  |
| public |[render](Template/render.md) | 渲染页面 |
|abstract protected |[_render_template](Template/_render_template.md) | 渲染语句 |
| public |[getRenderedString](Template/getRenderedString.md) | 获取渲染后的字符串 |
| protected |[_render_start](Template/_render_start.md) |  |
| protected |[_render_end](Template/_render_end.md) |  |
| public |[__toString](Template/__toString.md) | 获取当前模板的字符串 |
| public |[getRenderStack](Template/getRenderStack.md) |  |
| public |[set](Template/set.md) | 单个设置值 |
| public |[assign](Template/assign.md) | 直接压入值 |
| public |[parent](Template/parent.md) | 创建模板 |
| public |[response](Template/response.md) | 创建模板 |
| public |[get](Template/get.md) | 创建模板获取值 |
| public |[data](Template/data.md) |  |
| public |[hook](Template/hook.md) |  |
| public |[execGloHook](Template/execGloHook.md) |  |
| public |[exec](Template/exec.md) |  |
| public |[name](Template/name.md) |  |
| public |[responseName](Template/responseName.md) |  |
| public |[isMe](Template/isMe.md) |  |
| public |[boolecho](Template/boolecho.md) |  |
| public |[getModule](Template/getModule.md) |  |
| public |[getValue](Template/getValue.md) |  |
| public |[getResponse](Template/getResponse.md) |  |



## 例子

example