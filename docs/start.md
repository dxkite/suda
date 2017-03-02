# 快速开始

1. 下载 Suda System

```
git clone https://github.com/DXkite/suda 
```

2. 把 `public` 文件夹內的内容放到网站更目录，并保证其中对系统启动文件的引用为正确的路径,设置好应用目录（**应用目录最好不要放在网站目录下**）

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

3. 访问网站

