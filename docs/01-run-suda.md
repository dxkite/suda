# 如何运行项目

项目运行环境中，PHP的版本不应该小于 `PHP 7.2`

## 框架安装

### composer命令安装

使用 `composer` 命令在当前目录下创建一个 `project` 项目

```
composer create-project --prefer-dist dxkite/suda project
```


### 下载安装

1. 下载项目
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

2. 运行 composer 命令 安装依赖 [怎么安装composer?](https://pkg.phpcomposer.com/#how-to-install-composer)

```bash
composer install
```


## 运行

### Swoole 运行 

```
php server.php 127.0.0.1:8080
```


### PHP命令运行

运行命令 

```
php -S 127.0.0.1:8080 -t ./public 
```

即可运行项目，访问网页 http://127.0.0.1:8080/ 即可查看网站运行效果

![](/01-run-suda/run.jpg)

### Web服务器运行

将WEB更目录调整到 `public`，如果需要URL重写，`nginx` 配置需包含 `nginx.conf`, `Apache` 无需配置