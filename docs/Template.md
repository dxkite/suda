## 模板语法

- Suda 18.1.0
- 最后编辑：2018年8月6日

模板的语法类似PHP，实例化一个模板只能在Response类中调用 page方法来实例化。  
实例化的例子如下：

```php
<?php
namespace score;

class IndexResponse extends \suda\core\Response 
{
    public function onRequest(\suda\core\Request $request)
    {
        $this->page('score:index', 
        [
            'title'=>conf('module.title'), 
            'items'=>conf('module.subject')
        ])->render();
    }
}
```

实例化时，page方法会接受两个参数，一个是模板名，一个是模板中会使用的变量列表。  
这里使用了`score:index` 模板，这个是后续开发实例中的一串代码，并且压入了变量 title 和 items 这两个参数  
除了这种直接在实例化的时候赋值外，还可以通过 set方法来赋值。

```php
<?php
namespace score;

class IndexResponse extends \suda\core\Response 
{
    public function onRequest(\suda\core\Request $request)
    {
        $page=$this->page('score:index');
        $page->set('title',conf('module.title'));
        $page->set('items',conf('module.subject'));
        $page->render();
    }
}
```

模板在设置变量参数后，使用 render 方法将页面显示给浏览器。

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

函数等同路由函数 u 函数。

##### 例子

```
<a href="@u(['download'=>'true','passwd'=>$:passwd])">下载CSV</a>
```

#### @static 函数

static函数用于生成静态文件所在的前置URL，接受一个参数，可指定使用特定模块的资源文件。

##### 例子

```html
<script type="text/javascript" src="@static/ueditor/ueditor.all.js"></script>
<script src="@static('corelib')/remote.js"></script>
```
