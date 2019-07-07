# 内置模板

模板的语法类似PHP，实例化一个模板可以使用  `suda\application\Application` 的 `getTemplate` 方法来获取一个模板：

## 实例化模板

```php
<?php

namespace suda\welcome\response;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;
use suda\application\processor\RequestProcessor;

class SimpleResponse implements RequestProcessor
{
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $template = $application->getTemplate('simple', $request);
        $template->set('ip', $request->getRemoteAddr());
        return $template;
    }
}
```

或者使用 `\suda\application\template\Template` 类来`new`一个模板对象，模板采用 `set` 方法进行复制，`get` 方法可以获取到模板设置的值，
模板在设置变量参数后，将模板作为请求处理器的返回值即可自动渲染，也可以用 `render` 方法手动渲染


## 变量

### 输出变量

在模板中使用变量采用 `$:变量名` 的形式，与PHP的变量区分开来。如上设置的变量，例如显示 title 变量的值在页面中，则使用 `{{ $:title }}` 来获取值即可，所有的输出变量都会经过页面的转码以及调用I18N支持的翻译文件，如果有匹配则会翻译，如果需要输出包含HTML的字串请使用 `{{! }}` 来控制。

### 输出直接变量

标签 `{=  }` 用于输出直接变量，并且会调用翻译函数，如 `{= 用户名}` 将会输出“用户名”，如果在语言包中设置了翻译的话，会将这个字符串替换成对应语言的。

标签 `@{ }` 会输出其中PHP表达式的值，建议只使用内置变量，其功能等同 `{{ }}`  不同的是输出的字符串不会经过翻译函数翻译。

## 表达式

模板语法支持 `@if` 表达式和 `@foreach` 表达式控制流程。其中语法如下：

### @if

```
 @if (condition)

   @elseif (condition)

   @else

 @endif
```

### @foreach

```
@foreach ( $:value as $item )

@endforeach
```

```
@foreach ( $:value as $index => $item )

@endforeach
```

如上的语法类似于PHP函数。

## 例子

```html
            @if (count($:items([])))
                <tr>
                    <th colspan="2">数据录入</th>
                </tr>
                @foreach ( $:items as $name)
                <tr>
                    <th>{{$name}}</th>
                    <td> <input type="number" name="data[{{$name}}]" placeholder="{{$name}}" required></td>
                </tr>
                @endforeach 
            @endif
        </table>
```

## 内置函数

### @include 函数

使用该函数是用于在模板中包含其他模板的函数。  
使用例子：包含 suda 模块下的 header.tpl.html 模板(如果是本模块，则建议不指定模块)

```
@include ('suda:header')
```

### @extend 函数

使用该函数是用于继承其他模板的函数（一个模板只有一个）
使用例子：继承 suda 模块下的 header.tpl.html 模板

```
@extend ('suda:header')
```

### @insert 函数

@insert 函数配合 @startInsert 和 @endInsert函数使用，其中 startInsert 用于在页面中标记插入起始点，接受一个参数 标记插入点的名字，如 `@startPoint('bs-head')` ,@endInsert 用于标记插入块的结束点，不接受参数。

`@insert` 函数用于标记插入点，接受一个参数，插入点的名字。

插入点函数主要用于往被包含的页面中插入模板或者HTML代码。如有如下两个模板，都属于test模块下,则：

#### parent.tpl.html

```html
<html>
    <head>
        <title>测试页面</title>
    </head>
    <body>
        @insert('body')
    </body>
</html>
```

#### test.tpl.html

**include**

```html
@startInsert('body')
<h1>Hello World!</h1>
@endInsert
@include ('parent')
```

**extend**

```html
@extend ('parent')
@startInsert('body')
<h1>Hello World!</h1>
@endInsert

```

当使用模板 test.tpl.html 时，渲染的结果为：

```html
<html>
    <head>
        <title>测试页面</title>
    </head>
    <body>
        <h1>Hello World!</h1>
    </body>
</html>
```

插入函数用于控制页面中大块的模板复用显示。

### @u 函数

函数用于创页面URL，其中 `@u` 表示当前页面。

##### 例子

```html
<a href="@u(['download'=>'true','passwd'=>$:passwd])">下载CSV</a>
<a href="@u('route-name',['download'=>'true','passwd'=>$:passwd])">下载CSV</a>
```

#### @static 函数

static函数用于生成静态文件所在的前置URL，接受一个参数，可指定使用特定模块的资源文件。

##### 例子

```html
<script type="text/javascript" src="@static/ueditor/ueditor.all.js"></script>
<script src="@static('corelib')/remote.js"></script>
```


### @set 函数

用于给模板中的变量赋值，与模板set方法一致

```html
@set('a.b', 1)
```

### @call 函数

使用字符串形式调用函数，其中第一个参数为调用函数的模板，表达式支持 `\suda\framework\runnable\Runnable` 支持的表达式。

常用：

```
namespace\class->method
namespace\class::staticMethod
function
```

如

```html
@call('\suda\welcome\event\LoadEnvironment::handle', ['simple'])
```
