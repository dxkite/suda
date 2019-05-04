# 如何运行项目

项目运行环境中，PHP的版本不应该小于 `PHP 7.2`

## 框架运行

直接 [下载项目代码](https://github.com/dxkite/suda/archive/master.zip) 解压后在项目的结构如下：

```
├── composer.json
├── docs
├── phpunit.xml
├── public
├── README.md
├── suda
└── tests
```

切换目录到 `public` ，运行命令 

```
php -S 127.0.0.1:8080
```

即可运行项目，访问网页 http://127.0.0.1:8080/ 即可查看网站运行效果

![](/01-run-suda/run.jpg)