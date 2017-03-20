# 网页渲染引擎使用说明
在项目中，页面渲染的渲染器引擎为自行开发，语法类似Smarty语法
其中，页面元素包含了三种输出语句控制符


## 逻辑控制符
### 基本输出控制
| 控制符      | 说明|
|-------------|------------------------------------------------------------------------------------------|
| {{ value }} | 直接输出value的值, `{{ func() }}` 会输出函数的返回值,所有的输出会被函数`htmlspecialchars`转义 |
| {{! html }} | 功能与上述标识一样，但是不会调用函数`htmlspecialchars`对其转义，用于输出HTML                  |
| {-- --}     | 模板注释，会生成PHP注释，不会出现在网页中                                             |

>:notice: 以上输出控制在前面加`!`后会被忽略，即为转义，用于适配js模板;

### 逻辑控制符
| 控制符                | 说明                                       |
|-----------------------|-------------------------------------------|
| @if ( expression )    | if    --> `<?php if (expression) :?>`     |
| @else                 | else  --> `<?php else: ?>`                |
| @elseif (expression)  | elseif--> ` <?php elseif (expression): ?>`|
| @endif                | endif --> `<?php endif; ?>`               |
| @for (expressions)    | for   --> `<?php for(expressions): ?>`    |
| @endfor               | endfor--> `<?php endfor; ?>`              |
| @foreach(exp)         | foreach--> `<?php foreach( exp): ?>`      |
| @endforeach           | endforeach --> `<?php endforeach; ?>`     |
| @while(exp)           | while --> `<?php while(exp): ?>`          |
| @endwhile             | endwhile -->`<?php endwhile; ?>`          |

### 控制符
| 控制符                | 说明                                          |
|----------------------|-----------------------------------------------|
| @include(name)| 包含模板 |
| @data(commandstr)| 调用函数生成数据 |
| @insert(insertname)| 添加页面插入点 |
| @startInsert(insertname) |开始插入块 |
| @endInsert|　结束插入块|

## 页面赋值
1. 设置的变量获取值   
    在页面中，通过display的第二个参数设置页面的值，通过表达式 `$v->name` 可以获取到`name`的值`value`

2. 获取值时设置默认值  
    如果想在`name`的值为空时，输出一个默认值，只需要以`$v->name(default)`的方式调用，如果`name`为值，就会输出
`default`的值；

3. 格式化输出值    
    当以 `$v->name(default,arg1,arg2,...)`的方式调用时，会开启格式化输出功能，其中 `name` 或者 `default` 
    的字符串会作为模板，`argx`作为模板中的各个参数。

4. 使用默认值的情况    
    当 name的值为 NULL、未设置和是空字符串的时候会使用默认值。

> 语法糖： 使用 `$:name` 可以替代 `$v->name` , 为推荐用法。

## 模板静态资源
模板的静态资源统一放置在模板文件夹下的`static`目录，在激活模块时，会自动复制到`public/static`目录。
引用模板的资源：`@static/path/to/resource.type` 注意`@`，