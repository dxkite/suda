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

## 页面模板使用与赋值获取
### 使用页面模板


## 模板静态资源
模板的静态资源统一放置在模板文件夹下的`static`目录，在激活模块时，会自动复制到`public/static`目录。
引用模板的资源：`@static/path/to/resource.type` 注意`@`，