# 最终PHP坑：苏打 简易PHP框架
此框架的目的是简化PHP程序的构建过程，不包含太多东西。是我尝试使用面向对象的思路去编写的`DxCore`,
老式的处理思路导致我在面向对象设计的过程中还保留很多过去面向函数编程的习惯。因此，本程序尝试使用新的思路
去编写代码，而且，如标题所言，这个是我最后的PHP自用辅助类库。

## 框架功能
- 单入口
- MVC
- 模块化


## 主要辅助功能：
- 控制台自动构建功能
- 应用路由
- 事件监听器
- 简易PHP模板
- 日志记录工具



## 快速开始使用

###1. 下载 Suda System

```
git clone https://github.com/DXkite/suda 
```

###2. 把 `public` 文件夹內的内容放到网站更目录，并保证其中对系统启动文件的引用为正确的路径,设置好应用目录（**应用目录最好不要放在网站目录下**）

```php
<?php    
    // App所在目录
    // 其中，app为固定的名词，尽量不要更改。
    // 否则当使用系统控制台(system/console)
    // 需要更改相应的目录表示
    // 当系统和应用处在同一目录下时，请保证APP_DIR的一致性
    define('APP_DIR',__DIR__.'/../app');
    // 系统所在目录
    define('SYSTEM',__DIR__.'/../system/');
    require_once SYSTEM.'/suda.php';
```

###3. 访问网站



## [文档参考](docs/)

[开始使用](docs/start.md)
[路由使用](docs/tools/router.md)


## 历史版本

- [DxSite](https://github.com/DXkite/DxSite)   
- [ATD_MINI](https://github.com/DXkite/atd_mini)   
- [ATD3CN](https://github.com/DXkite/atd3.cn)   

